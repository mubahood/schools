<?php

namespace App\Services;

use App\Models\Billing\Invoice;
use App\Models\Billing\Payment;
use App\Models\Billing\Plan;
use App\Models\Billing\Subscription;
use App\Models\Enterprise;
use App\Models\User;
use App\Models\WalletRecord;
use App\Services\Gateways\PesapalGateway;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * The single place that changes a school's access or credits its wallet.
 *
 * Every state change is derived from dates on the enterprise and the paid
 * invoices beneath it, so a replayed webhook, a double click on "pay", or a
 * re-run of the daily tick can never grant or remove access twice.
 */
class BillingService
{
    public const TRIAL_DAYS = 30;

    public const PENDING_VERIFICATION = 'pending_verification';
    public const TRIALING = 'trialing';
    public const ACTIVE = 'active';
    public const PAST_DUE = 'past_due';
    public const SUSPENDED = 'suspended';
    public const CANCELLED = 'cancelled';

    // ------------------------------------------------------------ plans

    public static function plans()
    {
        return Plan::where('is_public', true)->orderBy('sort')->get();
    }

    public static function activeStudents(Enterprise $ent): int
    {
        return (int) User::where('enterprise_id', $ent->id)
            ->where('user_type', 'student')->where('status', 1)->count();
    }

    /** Smallest public plan that fits the school today; the largest if none does. */
    public static function recommendPlan(Enterprise $ent): ?Plan
    {
        $n = self::activeStudents($ent);
        $plans = self::plans();
        foreach ($plans as $p) {
            if ($p->fits($n)) {
                return $p;
            }
        }

        return $plans->last();
    }

    // ------------------------------------------------------------ trial

    public static function startTrial(Enterprise $ent, int $days = self::TRIAL_DAYS): void
    {
        $ends = Carbon::now()->addDays($days);
        DB::table('enterprises')->where('id', $ent->id)->update([
            'access_status' => self::TRIALING,
            'trial_ends_at' => $ends,
            'access_ends_at' => $ends,
            'billing_exempt' => 0,
            // legacy fields kept coherent for older screens
            'has_valid_lisence' => 'Yes',
            'expiry' => $ends->toDateString(),
        ]);
    }

    // ------------------------------------------------------------ subscribe

    /**
     * Create a subscription and its invoice(s). Nothing is granted until the
     * first invoice is paid. Returns the first invoice to pay.
     */
    public static function subscribe(Enterprise $ent, Plan $plan, string $period, int $instalments, $createdBy = null): Invoice
    {
        $period = $period === Plan::PERIOD_12M ? Plan::PERIOD_12M : Plan::PERIOD_6M;
        $instalments = $instalments === 3 ? 3 : 1;
        $total = $plan->priceFor($period);
        $months = Plan::months($period);

        return DB::transaction(function () use ($ent, $plan, $period, $instalments, $total, $months, $createdBy) {
            // Only one open subscription at a time: void an unpaid pending one.
            $open = Subscription::where('enterprise_id', $ent->id)->where('status', Subscription::PENDING)->get();
            foreach ($open as $o) {
                Invoice::where('subscription_id', $o->id)->where('status', Invoice::ISSUED)->update(['status' => Invoice::VOID]);
                $o->update(['status' => Subscription::CANCELLED, 'cancelled_at' => now()]);
            }

            $sub = Subscription::create([
                'enterprise_id' => $ent->id, 'plan_id' => $plan->id, 'period' => $period,
                'instalments' => $instalments, 'total_amount' => $total,
                'status' => Subscription::PENDING, 'created_by' => $createdBy,
            ]);

            // Split evenly; the last instalment absorbs rounding so the sum is exact.
            $per = intdiv($total, $instalments);
            $first = null;
            for ($k = 1; $k <= $instalments; $k++) {
                $amount = $k === $instalments ? $total - $per * ($instalments - 1) : $per;
                $due = $k === 1 ? now() : now()->addMonths(intdiv($months * ($k - 1), $instalments));
                $inv = self::issueInvoice($ent, Invoice::KIND_SUBSCRIPTION, $amount, sprintf(
                    '%s plan, %d months%s', $plan->name, $months,
                    $instalments > 1 ? " — instalment {$k} of {$instalments}" : ''
                ), $due, $sub->id, $k, $instalments);
                $first = $first ?: $inv;
            }

            return $first;
        });
    }

    public static function topUpSms(Enterprise $ent, int $amount): Invoice
    {
        return self::issueInvoice($ent, Invoice::KIND_SMS_CREDIT, $amount, 'SMS credit top-up UGX ' . number_format($amount), now());
    }

    private static function issueInvoice(Enterprise $ent, string $kind, int $amount, string $desc, $due, $subId = null, int $no = 1, int $of = 1): Invoice
    {
        $inv = Invoice::create([
            'number' => 'TMP', 'enterprise_id' => $ent->id, 'subscription_id' => $subId, 'kind' => $kind,
            'instalment_no' => $no, 'instalment_of' => $of, 'amount' => $amount, 'currency' => 'UGX',
            'status' => Invoice::ISSUED, 'description' => $desc, 'issued_at' => now(), 'due_at' => $due,
        ]);
        $inv->update(['number' => Invoice::nextNumber($inv->id)]);

        return $inv->fresh();
    }

    // ------------------------------------------------------------ pay (Pesapal)

    /** Create the gateway order and return the URL to send the payer to. */
    public static function initiatePesapal(Invoice $inv, User $payer): string
    {
        if ($inv->status !== Invoice::ISSUED) {
            throw new \RuntimeException('Invoice ' . $inv->number . ' is ' . $inv->status . ' and cannot be paid.');
        }
        $gw = app(PesapalGateway::class);
        if (!$gw->isConfigured()) {
            throw new \RuntimeException('Online payment is not configured. Please use bank transfer.');
        }
        $attempt = Payment::where('invoice_id', $inv->id)->count() + 1;
        $merchantRef = $inv->number . '-' . $attempt;

        $payment = Payment::create([
            'invoice_id' => $inv->id, 'enterprise_id' => $inv->enterprise_id, 'gateway' => Payment::GATEWAY_PESAPAL,
            'merchant_ref' => $merchantRef, 'amount' => $inv->amount, 'status' => Payment::INITIATED,
        ]);

        $parts = explode(' ', trim($payer->name ?: 'School Admin'), 2);
        $order = $gw->submitOrder($inv, $merchantRef, [
            'email' => filter_var($payer->email, FILTER_VALIDATE_EMAIL) ? $payer->email : '',
            'phone' => $payer->phone_number_1 ?: '',
            'first_name' => $parts[0], 'last_name' => $parts[1] ?? $parts[0],
        ]);
        $payment->update(['gateway_ref' => $order['order_tracking_id'], 'status' => Payment::PENDING]);

        return $order['redirect_url'];
    }

    /**
     * Ask Pesapal what happened to an order and settle accordingly.
     * Safe to call any number of times from the callback, the IPN, or by hand.
     */
    public static function reconcilePesapal(string $orderTrackingId): ?Payment
    {
        $payment = Payment::where('gateway_ref', $orderTrackingId)->first();
        if (!$payment) {
            Log::warning('Pesapal notification for unknown order', ['order' => $orderTrackingId]);
            return null;
        }
        $st = app(PesapalGateway::class)->status($orderTrackingId);

        if ($payment->status === Payment::SUCCEEDED) {
            // Already settled. The only thing that can change now is a reversal.
            if ($st['status'] === 'reversed') {
                self::unsettle($payment, 'Pesapal reported the payment reversed');
            }
            return $payment->fresh();
        }

        $payment->raw_payload = json_encode($st['raw']);
        $payment->method = $st['method'] ?: $payment->method;
        $payment->confirmation_code = $st['confirmation_code'] ?: $payment->confirmation_code;

        if ($st['ok']) {
            $inv = $payment->invoice;
            if ((float) $st['amount'] + 0.5 < (float) $inv->amount) {
                // Underpayment: never grant; leave for a human.
                $payment->status = Payment::FAILED;
                $payment->save();
                Log::error('Pesapal amount mismatch', ['payment' => $payment->id, 'paid' => $st['amount'], 'due' => $inv->amount]);
                return $payment;
            }
            self::settle($payment, Carbon::now());
        } elseif (in_array($st['status'], ['failed', 'invalid', 'reversed'], true)) {
            $payment->status = $st['status'] === 'reversed' ? Payment::REVERSED : Payment::FAILED;
            $payment->save();
        } else {
            $payment->save(); // still pending
        }

        return $payment->fresh();
    }

    /**
     * Undo a settlement after a reversal/chargeback: invoice back to issued,
     * access recomputed from what is still genuinely paid. Never throws.
     */
    public static function unsettle(Payment $payment, string $reason): void
    {
        DB::transaction(function () use ($payment, $reason) {
            $inv = Invoice::where('id', $payment->invoice_id)->lockForUpdate()->first();
            $payment->status = Payment::REVERSED;
            $payment->method = trim(($payment->method ?? '') . ' | ' . $reason);
            $payment->save();
            if (!$inv || $inv->status !== Invoice::PAID) {
                return;
            }
            $inv->status = Invoice::ISSUED;
            $inv->paid_at = null;
            $inv->save();

            $ent = Enterprise::find($inv->enterprise_id);
            if ($inv->kind === Invoice::KIND_SMS_CREDIT) {
                $w = new WalletRecord();
                $w->enterprise_id = $ent->id;
                $w->amount = -1 * (int) $inv->amount;
                $w->details = 'Reversal of ' . $inv->number . ' (' . $reason . ')';
                $w->save();
                return;
            }
            $sub = Subscription::where('id', $inv->subscription_id)->lockForUpdate()->first();
            if (!$sub || !$sub->starts_at) {
                return;
            }
            $paid = Invoice::where('subscription_id', $sub->id)->where('status', Invoice::PAID)->count();
            $accessEnds = $paid > 0
                ? Carbon::parse($sub->starts_at)->addMonths(intdiv($sub->months() * $paid, $sub->instalments))
                : Carbon::now()->subDay();
            if ($paid === 0) {
                $sub->status = Subscription::PENDING;
                $sub->save();
            }
            DB::table('enterprises')->where('id', $ent->id)->update([
                'access_ends_at' => $accessEnds, 'expiry' => $accessEnds->toDateString(),
            ]);
            Log::warning('Payment reversed; access recomputed', ['enterprise' => $ent->id, 'invoice' => $inv->number, 'reason' => $reason]);
            self::refreshAccess($ent);
        });
    }

    /** Offline payment confirmed by Newline (bank slip, cash). */
    public static function recordManualPayment(Invoice $inv, string $gateway, ?string $ref, int $recordedBy, ?string $note = null): Payment
    {
        $p = Payment::create([
            'invoice_id' => $inv->id, 'enterprise_id' => $inv->enterprise_id, 'gateway' => $gateway,
            'gateway_ref' => $ref ? ($gateway . ':' . $ref) : ($gateway . ':inv' . $inv->id . ':' . time()),
            'merchant_ref' => $inv->number, 'amount' => $inv->amount, 'status' => Payment::INITIATED,
            'method' => $note, 'recorded_by' => $recordedBy,
        ]);
        self::settle($p, Carbon::now());

        return $p->fresh();
    }

    /**
     * THE settlement. Marks the payment and invoice paid and applies the effect
     * exactly once, under a row lock on the invoice.
     */
    public static function settle(Payment $payment, Carbon $when): void
    {
        DB::transaction(function () use ($payment, $when) {
            $inv = Invoice::where('id', $payment->invoice_id)->lockForUpdate()->first();
            if (!$inv) {
                throw new \RuntimeException('Invoice missing for payment ' . $payment->id);
            }

            $payment->status = Payment::SUCCEEDED;
            $payment->received_at = $when;
            $payment->save();

            if ($inv->status === Invoice::PAID) {
                return; // effect already applied by an earlier notification
            }
            $inv->status = Invoice::PAID;
            $inv->paid_at = $when;
            $inv->save();

            $ent = Enterprise::find($inv->enterprise_id);
            if ($inv->kind === Invoice::KIND_SMS_CREDIT) {
                $w = new WalletRecord();
                $w->enterprise_id = $ent->id;
                $w->amount = (int) $inv->amount;
                $w->details = 'Purchased SMS credit UGX ' . number_format($inv->amount) . ' via ' . $payment->gateway . ', invoice ' . $inv->number;
                $w->save();
                if ($ent->can_send_messages !== 'Yes') {
                    DB::table('enterprises')->where('id', $ent->id)->update(['can_send_messages' => 'Yes']);
                }
                return;
            }

            if ($inv->kind === Invoice::KIND_LICENCE) {
                self::applyLicence($inv, $ent, $when);
                return;
            }

            // Subscription instalment: access runs to the share of the period paid for.
            $sub = Subscription::where('id', $inv->subscription_id)->lockForUpdate()->first();
            if (!$sub) {
                return;
            }
            if (!$sub->starts_at) {
                // First payment starts the clock — from today, or from the end of a
                // still-running paid period so early renewals never lose days.
                $base = ($ent->access_status === self::ACTIVE && $ent->access_ends_at && Carbon::parse($ent->access_ends_at)->isFuture())
                    ? Carbon::parse($ent->access_ends_at) : $when->copy();
                $sub->starts_at = $base;
                $sub->ends_at = $base->copy()->addMonths($sub->months());
            }
            $paid = Invoice::where('subscription_id', $sub->id)->where('status', Invoice::PAID)->count();
            $coveredMonths = intdiv($sub->months() * $paid, $sub->instalments);
            $accessEnds = Carbon::parse($sub->starts_at)->addMonths($coveredMonths);
            $sub->status = $paid >= $sub->instalments ? Subscription::ACTIVE : Subscription::ACTIVE;
            $sub->save();

            // Any other open subscription is superseded.
            Subscription::where('enterprise_id', $ent->id)->where('id', '<>', $sub->id)
                ->whereIn('status', [Subscription::PENDING])->update(['status' => Subscription::CANCELLED, 'cancelled_at' => $when]);

            DB::table('enterprises')->where('id', $ent->id)->update([
                'access_status' => self::ACTIVE,
                'access_ends_at' => $accessEnds,
                'has_valid_lisence' => 'Yes',
                'expiry' => $accessEnds->toDateString(),
            ]);
        });
    }

    /**
     * A paid licence invoice: the term it was raised for becomes the active
     * term, and access runs to that term's end plus the grace window. A school
     * that was locked out is unlocked by this, which is the whole point of
     * leaving the invoice reachable while suspended.
     */
    private static function applyLicence(Invoice $inv, Enterprise $ent, Carbon $when): void
    {
        $term = $inv->term_id ? DB::table('terms')->where('id', $inv->term_id)->where('enterprise_id', $ent->id)->first() : null;

        if ($term) {
            // Exactly one active term per school, and its year active with it.
            DB::table('terms')->where('enterprise_id', $ent->id)->where('id', '<>', $term->id)->update(['is_active' => 0]);
            DB::table('terms')->where('id', $term->id)->update(['is_active' => 1]);
            if ($term->academic_year_id) {
                DB::table('academic_years')->where('enterprise_id', $ent->id)->where('id', '<>', $term->academic_year_id)->update(['is_active' => 0]);
                DB::table('academic_years')->where('id', $term->academic_year_id)->update(['is_active' => 1]);
            }
        }

        $ends = $term && $term->ends ? Carbon::parse($term->ends)->endOfDay() : $when->copy()->addMonths(4);
        if ($ends->isPast()) {
            // Paying late must still buy a usable window, never a dead one.
            $ends = $when->copy()->addDays(30);
        }
        $ends = $ends->addDays((int) ($ent->grace_days ?: 7));

        // Never shorten a window the school has already paid for.
        if ($ent->access_ends_at && Carbon::parse($ent->access_ends_at)->gt($ends)) {
            $ends = Carbon::parse($ent->access_ends_at);
        }

        DB::table('enterprises')->where('id', $ent->id)->update([
            'access_status' => self::ACTIVE,
            'access_ends_at' => $ends,
            'has_valid_lisence' => 'Yes',
            'expiry' => $ends->toDateString(),
        ]);

        Log::info('Licence invoice settled', ['invoice' => $inv->number, 'enterprise' => $ent->id,
            'term' => $term->id ?? null, 'access_ends_at' => $ends->toDateString()]);
    }

    /**
     * Raise a licence invoice for a school. Starts life as a draft so Newline
     * can read the document before the school ever sees it.
     *
     * $data: title, description, notes, inclusions[], due_at, term_id, items[]
     *        where each item is [label, description, quantity, unit, unit_amount].
     */
    public static function createLicenceInvoice(Enterprise $ent, array $data, ?int $byUserId = null): Invoice
    {
        return DB::transaction(function () use ($ent, $data, $byUserId) {
            $items = array_values(array_filter($data['items'] ?? [], fn ($i) => trim((string) ($i['label'] ?? '')) !== ''));
            if (!$items) {
                throw new \InvalidArgumentException('An invoice needs at least one line item.');
            }

            $term = !empty($data['term_id']) ? DB::table('terms')->where('id', $data['term_id'])->where('enterprise_id', $ent->id)->first() : null;
            $total = 0;
            foreach ($items as $i) {
                $total += (int) max(0, (int) ($i['quantity'] ?? 1)) * (int) max(0, (int) ($i['unit_amount'] ?? 0));
            }
            if ($total <= 0) {
                throw new \InvalidArgumentException('An invoice must come to more than zero.');
            }

            $inv = new Invoice();
            $inv->number = 'TMP-' . uniqid();
            $inv->enterprise_id = $ent->id;
            $inv->kind = Invoice::KIND_LICENCE;
            $inv->title = $data['title'] ?? 'System Licence & Support';
            $inv->term_id = $term->id ?? null;
            $inv->academic_year_id = $term->academic_year_id ?? null;
            $inv->amount = $total;
            $inv->currency = 'UGX';
            $inv->status = Invoice::DRAFT;
            $inv->description = $data['description'] ?? trim(($inv->title . ' — ' . ($term ? self::termLabel($term) : '')), ' —');
            $inv->notes = $data['notes'] ?? null;
            $inv->inclusions = !empty($data['inclusions']) ? json_encode(array_values($data['inclusions'])) : null;
            $inv->due_at = !empty($data['due_at']) ? Carbon::parse($data['due_at'])->endOfDay() : Carbon::now()->addDays((int) config('newline.invoice.default_due_days', 14))->endOfDay();
            $inv->created_by = $byUserId;
            $inv->save();
            $inv->number = Invoice::nextNumber($inv->id);
            $inv->public_token = \Illuminate\Support\Str::random(48);
            $inv->save();

            $sort = 0;
            foreach ($items as $i) {
                $qty = (int) max(1, (int) ($i['quantity'] ?? 1));
                $unitAmount = (int) max(0, (int) ($i['unit_amount'] ?? 0));
                \App\Models\Billing\InvoiceItem::create([
                    'invoice_id' => $inv->id,
                    'label' => mb_substr(trim($i['label']), 0, 190),
                    'description' => $i['description'] ?? null,
                    'quantity' => $qty,
                    'unit' => $i['unit'] ?? null,
                    'unit_amount' => $unitAmount,
                    'amount' => $qty * $unitAmount,
                    'sort' => $sort++,
                ]);
            }

            return $inv->fresh();
        });
    }

    /** Make a draft visible and payable to the school. */
    public static function publishInvoice(Invoice $inv): Invoice
    {
        if ($inv->status !== Invoice::DRAFT) {
            return $inv;
        }
        $inv->status = Invoice::ISSUED;
        $inv->issued_at = Carbon::now();
        $inv->save();
        Log::info('Invoice issued to school', ['invoice' => $inv->number, 'enterprise' => $inv->enterprise_id]);

        return $inv->fresh();
    }

    public static function voidInvoice(Invoice $inv, ?string $reason = null): Invoice
    {
        if ($inv->status === Invoice::PAID) {
            throw new \RuntimeException('A paid invoice cannot be voided; reverse the payment instead.');
        }
        $inv->status = Invoice::VOID;
        $inv->notes = trim(($inv->notes ?? '') . "\nVoided: " . ($reason ?: 'no reason given'));
        $inv->save();
        Payment::where('invoice_id', $inv->id)->whereIn('status', [Payment::INITIATED, Payment::PENDING])
            ->update(['status' => Payment::FAILED]);

        return $inv->fresh();
    }

    /**
     * The one invoice the school must deal with right now: the oldest unpaid
     * one, overdue first. This is what takes over the billing page and the
     * dashboard.
     */
    public static function dueInvoice(Enterprise $ent): ?Invoice
    {
        return Invoice::where('enterprise_id', $ent->id)
            ->where('status', Invoice::ISSUED)
            ->orderByRaw('CASE WHEN due_at IS NULL THEN 1 ELSE 0 END')
            ->orderBy('due_at')
            ->orderBy('id')
            ->first();
    }

    public static function termLabel($term): string
    {
        if (!$term) {
            return '';
        }
        $name = trim((string) ($term->name ?? ''));
        $name = is_numeric($name) ? 'Term ' . $name : ($name ?: 'Term');
        $year = DB::table('academic_years')->where('id', $term->academic_year_id)->value('name');

        return trim($name . ($year ? ', ' . trim($year) : ''));
    }

    // ------------------------------------------------------------ lifecycle

    /** Recompute access_status from the dates. Idempotent. */
    public static function refreshAccess(Enterprise $ent): string
    {
        $ent = Enterprise::find($ent->id);
        if ($ent->billing_exempt || $ent->id == 1) {
            return self::ACTIVE;
        }
        $status = $ent->access_status;
        if (in_array($status, [self::CANCELLED, self::PENDING_VERIFICATION], true)) {
            return $status;
        }

        $ends = $ent->access_ends_at ? Carbon::parse($ent->access_ends_at) : ($ent->trial_ends_at ? Carbon::parse($ent->trial_ends_at) : null);
        // "Paid" means a live package OR a settled licence invoice — a school on a
        // negotiated licence is a paying customer, not one still on trial.
        $hasPaid = Subscription::where('enterprise_id', $ent->id)->where('status', Subscription::ACTIVE)->exists()
            || Invoice::where('enterprise_id', $ent->id)->where('kind', Invoice::KIND_LICENCE)
                ->where('status', Invoice::PAID)->exists();
        $now = Carbon::now();

        // An issued invoice whose deadline has passed caps the window: the date
        // printed on the invoice is the date the system enforces, whatever a
        // previously paid period says. Clearing the invoice lifts the cap.
        $missed = Invoice::where('enterprise_id', $ent->id)->where('status', Invoice::ISSUED)
            ->whereNotNull('due_at')->where('due_at', '<', $now)->min('due_at');
        if ($missed) {
            $missed = Carbon::parse($missed);
            $ends = ($ends === null || $missed->lt($ends)) ? $missed : $ends;
        }

        if ($ends === null) {
            // No dates to reason from: an operator hold (suspended) must stay
            // held, and anything else keeps its current label. Only a genuinely
            // unset row is promoted to trialing.
            $new = in_array($status, [self::SUSPENDED, self::ACTIVE, self::PAST_DUE, self::TRIALING], true)
                ? $status : ($hasPaid ? self::ACTIVE : self::TRIALING);
        } elseif ($now->lte($ends)) {
            $new = $hasPaid ? self::ACTIVE : self::TRIALING;
        } elseif ($now->lte($ends->copy()->addDays((int) $ent->grace_days))) {
            $new = self::PAST_DUE;
        } else {
            $new = self::SUSPENDED;
        }

        if ($new !== $status) {
            DB::table('enterprises')->where('id', $ent->id)->update([
                'access_status' => $new,
                'has_valid_lisence' => $new === self::SUSPENDED ? 'No' : 'Yes',
            ]);
        }

        return $new;
    }

    /** Days of access left (negative = days overdue). */
    public static function daysLeft(Enterprise $ent): ?int
    {
        $ends = $ent->access_ends_at ?: $ent->trial_ends_at;

        return $ends ? (int) Carbon::now()->startOfDay()->diffInDays(Carbon::parse($ends)->startOfDay(), false) : null;
    }

    /** Short label for the header pill. */
    public static function statusLabel(Enterprise $ent): array
    {
        if ($ent->billing_exempt || $ent->id == 1) {
            return ['text' => 'Licensed', 'class' => 'success'];
        }
        $d = self::daysLeft($ent);
        switch ($ent->access_status) {
            case self::TRIALING:  return ['text' => 'Trial: ' . max(0, $d) . ' day' . ($d == 1 ? '' : 's') . ' left', 'class' => $d <= 7 ? 'warning' : 'info'];
            case self::ACTIVE:    return ['text' => 'Active · ' . max(0, $d) . ' days left', 'class' => $d <= 14 ? 'warning' : 'success'];
            case self::PAST_DUE:  return ['text' => 'Payment overdue', 'class' => 'danger'];
            case self::SUSPENDED: return ['text' => 'Suspended — read only', 'class' => 'danger'];
            case self::CANCELLED: return ['text' => 'Cancelled', 'class' => 'default'];
            default:              return ['text' => ucfirst(str_replace('_', ' ', (string) $ent->access_status)), 'class' => 'default'];
        }
    }

    /** Daily job: transitions + owner notices. Returns counts for the log. */
    public static function tick(): array
    {
        $out = ['checked' => 0, 'changed' => 0, 'notified' => 0];
        foreach (Enterprise::where('billing_exempt', 0)->where('id', '<>', 1)->get() as $ent) {
            $out['checked']++;
            $before = $ent->access_status;
            $after = self::refreshAccess($ent);
            if ($after !== $before) {
                $out['changed']++;
            }
            $days = self::daysLeft($ent);
            $event = null;
            if (in_array($after, [self::TRIALING, self::ACTIVE], true) && $days !== null && $days <= 7 && $days >= 0) {
                $event = 'expiring';
            } elseif ($after === self::PAST_DUE) {
                $event = 'past_due';
            } elseif ($after === self::SUSPENDED && $before !== self::SUSPENDED) {
                $event = 'suspended';
            }
            if ($event && self::notifyOwner($ent->fresh(), $event, $days)) {
                $out['notified']++;
            }
        }

        return $out;
    }

    /** One email per event per week; never throws. */
    private static function notifyOwner(Enterprise $ent, string $event, ?int $days): bool
    {
        $owner = User::find($ent->administrator_id);
        if (!$owner || !filter_var($owner->email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        $bucket = $event === 'expiring' ? Carbon::now()->format('o-W') : Carbon::now()->format('Y-m-d');
        if (!Cache::add("billing.notice.{$ent->id}.{$event}.{$bucket}", 1, 60 * 60 * 24 * 8)) {
            return false;
        }
        $url = admin_url('billing');
        $msgs = [
            'expiring'  => ["Your School Dynamics access ends in {$days} day(s)", "Hello,\n\nAccess for {$ent->name} ends in {$days} day(s). Choose a package and pay online (Mobile Money or card) to keep your school running without interruption:\n{$url}\n\nSchool Dynamics"],
            'past_due'  => ["Payment overdue for {$ent->name}", "Hello,\n\nYour subscription for {$ent->name} has expired. You have a short grace period before the system becomes read-only. Pay now:\n{$url}\n\nSchool Dynamics"],
            'suspended' => ["{$ent->name} is now read-only", "Hello,\n\nAccess for {$ent->name} has been suspended for non-payment. You can still log in to view data and pay:\n{$url}\n\nSchool Dynamics"],
        ];
        [$subject, $body] = $msgs[$event];
        try {
            Mail::raw($body, function ($m) use ($owner, $subject) {
                $m->to($owner->email)->subject($subject);
            });
            Log::info('Billing notice sent', ['enterprise' => $ent->id, 'event' => $event]);
            return true;
        } catch (\Throwable $e) {
            Log::error('Billing notice failed', ['enterprise' => $ent->id, 'event' => $event, 'error' => $e->getMessage()]);
            return false;
        }
    }
}
