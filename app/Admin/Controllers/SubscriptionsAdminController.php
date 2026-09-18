<?php

namespace App\Admin\Controllers;

use App\Models\Billing\Invoice;
use App\Models\Billing\Payment;
use App\Models\Billing\Subscription;
use App\Models\Enterprise;
use App\Services\BillingService;
use Carbon\Carbon;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Newline's console: every school's subscription state, pending bank claims,
 * and the overrides an operator needs. Every action writes a payments /
 * subscriptions row or a dated status — never a raw column edit.
 */
class SubscriptionsAdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $u = Admin::user();
            if (!$u || !$u->isRole('super-admin')) {
                abort(403, 'Newline staff only.');
            }
            return $next($request);
        });
    }

    public function index(Content $content, Request $r)
    {
        $filter = $r->get('f', 'all');
        $q = Enterprise::where('id', '<>', 1)->orderBy('name');
        $rows = $q->get()->map(function (Enterprise $e) {
            BillingService::refreshAccess($e);
            $e = $e->fresh();
            $sub = Subscription::where('enterprise_id', $e->id)->where('status', Subscription::ACTIVE)->orderByDesc('id')->with('plan')->first();
            $last = Payment::where('enterprise_id', $e->id)->where('status', Payment::SUCCEEDED)->orderByDesc('received_at')->first();
            return (object) [
                'ent' => $e, 'label' => BillingService::statusLabel($e), 'days' => BillingService::daysLeft($e),
                'students' => BillingService::activeStudents($e), 'plan' => $sub ? $sub->plan->name . ' ' . $sub->months() . 'm' : '—',
                'last_paid' => $last ? $last->received_at->format('d M Y') . ' · ' . number_format($last->amount) : '—',
                'open' => Invoice::where('enterprise_id', $e->id)->where('status', Invoice::ISSUED)->sum('amount'),
            ];
        });
        $rows = $rows->filter(function ($x) use ($filter) {
            switch ($filter) {
                case 'expiring':  return in_array($x->ent->access_status, ['trialing', 'active']) && !$x->ent->billing_exempt && $x->days !== null && $x->days <= 14;
                case 'past_due':  return $x->ent->access_status === 'past_due';
                case 'suspended': return $x->ent->access_status === 'suspended';
                case 'trialing':  return $x->ent->access_status === 'trialing';
                case 'exempt':    return (bool) $x->ent->billing_exempt;
                case 'paying':    return !$x->ent->billing_exempt && $x->ent->access_status === 'active';
                default:          return true;
            }
        })->values();

        $claims = Payment::where('status', Payment::PENDING)->whereIn('gateway', ['bank', 'cash'])
            ->with('invoice')->orderBy('created_at')->get();

        $mrr = Subscription::where('status', Subscription::ACTIVE)->get()
            ->sum(fn ($s) => (int) round($s->total_amount / $s->months()));

        return $content->title('Subscriptions')->description('Newline console')
            ->body(view('admin.billing.console', [
                'rows' => $rows, 'claims' => $claims, 'filter' => $filter, 'mrr' => $mrr,
                'counts' => [
                    'paying' => Enterprise::where('access_status', 'active')->where('billing_exempt', 0)->where('id', '<>', 1)->count(),
                    'trialing' => Enterprise::where('access_status', 'trialing')->count(),
                    'past_due' => Enterprise::where('access_status', 'past_due')->count(),
                    'suspended' => Enterprise::where('access_status', 'suspended')->count(),
                    'exempt' => Enterprise::where('billing_exempt', 1)->where('id', '<>', 1)->count(),
                ],
            ]));
    }

    /** Confirm a bank/cash claim the school submitted: settles the invoice. */
    public function confirmPayment($paymentId)
    {
        $p = Payment::where('status', Payment::PENDING)->findOrFail($paymentId);
        BillingService::settle($p, Carbon::now());
        $this->audit('confirm-payment', $p->enterprise_id, ['payment' => $p->id, 'amount' => $p->amount]);
        admin_success('Payment confirmed', 'Invoice ' . $p->invoice->number . ' settled; access updated.');

        return back();
    }

    public function rejectPayment($paymentId)
    {
        $p = Payment::where('status', Payment::PENDING)->findOrFail($paymentId);
        $p->update(['status' => Payment::FAILED, 'method' => trim(($p->method ?? '') . ' — rejected by ' . Admin::user()->name)]);
        $this->audit('reject-payment', $p->enterprise_id, ['payment' => $p->id]);
        admin_warning('Claim rejected', 'The school will see the invoice as still unpaid.');

        return back();
    }

    /** Record an offline payment Newline received for any open invoice. */
    public function manualPay(Request $r, $invoiceId)
    {
        $d = $r->validate(['reference' => 'nullable|string|max:80', 'gateway' => 'required|in:bank,cash,manual']);
        $inv = Invoice::where('status', Invoice::ISSUED)->findOrFail($invoiceId);
        $p = BillingService::recordManualPayment($inv, $d['gateway'], $d['reference'] ?? null, Admin::user()->id, 'Recorded by ' . Admin::user()->name);
        $this->audit('manual-pay', $inv->enterprise_id, ['invoice' => $inv->id, 'payment' => $p->id]);
        admin_success('Recorded', 'Invoice ' . $inv->number . ' paid.');

        return back();
    }

    /** Give a school N more days (goodwill, agreed delay). */
    public function extend(Request $r, $enterpriseId)
    {
        $d = $r->validate(['days' => 'required|integer|min:1|max:365', 'reason' => 'nullable|string|max:200']);
        $e = Enterprise::findOrFail($enterpriseId);
        $base = $e->access_ends_at && Carbon::parse($e->access_ends_at)->isFuture() ? Carbon::parse($e->access_ends_at) : Carbon::now();
        $ends = $base->addDays((int) $d['days']);
        DB::table('enterprises')->where('id', $e->id)->update([
            'access_ends_at' => $ends, 'access_status' => $e->access_status === 'trialing' ? 'trialing' : 'active',
            'has_valid_lisence' => 'Yes', 'expiry' => $ends->toDateString(),
        ]);
        $this->audit('extend', $e->id, ['days' => $d['days'], 'reason' => $d['reason'] ?? '', 'until' => $ends->toDateString()]);
        admin_success('Extended', $e->name . ' now has access until ' . $ends->format('d M Y') . '.');

        return back();
    }

    public function toggleExempt($enterpriseId)
    {
        $e = Enterprise::findOrFail($enterpriseId);
        $new = $e->billing_exempt ? 0 : 1;
        $upd = ['billing_exempt' => $new];
        if ($new) {
            $upd += ['access_status' => 'active', 'has_valid_lisence' => 'Yes'];
        } elseif (!$e->access_ends_at) {
            // switching billing ON for a school with no dates: give it a 30-day runway
            $upd += ['access_status' => 'trialing', 'trial_ends_at' => now()->addDays(30), 'access_ends_at' => now()->addDays(30)];
        }
        DB::table('enterprises')->where('id', $e->id)->update($upd);
        $this->audit('toggle-exempt', $e->id, ['billing_exempt' => $new]);
        admin_success($new ? 'Billing exempt' : 'Billing enabled', $e->name);

        return back();
    }

    public function suspend($enterpriseId)
    {
        $e = Enterprise::findOrFail($enterpriseId);
        // End access beyond the grace window, otherwise refreshAccess() would
        // read "expired yesterday" as past_due and quietly undo the hold.
        DB::table('enterprises')->where('id', $e->id)->update(['access_status' => 'suspended', 'billing_exempt' => 0,
            'access_ends_at' => now()->subDays((int) $e->grace_days + 1), 'has_valid_lisence' => 'No']);
        $this->audit('suspend', $e->id);
        admin_warning('Suspended', $e->name . ' is now read-only.');

        return back();
    }

    private function audit(string $action, int $enterpriseId, array $extra = []): void
    {
        Log::info('Subscriptions console: ' . $action, $extra + ['enterprise' => $enterpriseId, 'by' => Admin::user()->id]);
    }
}
