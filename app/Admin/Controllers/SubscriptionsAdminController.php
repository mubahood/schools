<?php

namespace App\Admin\Controllers;

use App\Models\Billing\Invoice;
use App\Models\Billing\Payment;
use App\Models\Billing\Subscription;
use App\Models\Enterprise;
use App\Services\BillingService;
use App\Services\InvoiceDocument;
use App\Services\SmsService;
use App\Models\Utils;
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
        $search = trim((string) $r->get('q', ''));
        $q = Enterprise::where('id', '<>', 1)->orderBy('name');
        if ($search !== '') {
            $q->where(function ($w) use ($search) {
                $w->where('name', 'like', "%{$search}%")->orWhere('subdomain', 'like', "%{$search}%")
                  ->orWhere('subdomain_slug', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }
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
                'due' => BillingService::dueInvoice($e),
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
                'rows' => $rows, 'claims' => $claims, 'filter' => $filter, 'mrr' => $mrr, 'search' => $search,
                'drafts' => Invoice::where('status', Invoice::DRAFT)->orderByDesc('id')->limit(20)->get(),
                'overdue' => Invoice::where('status', Invoice::ISSUED)->whereNotNull('due_at')
                    ->where('due_at', '<', Carbon::now())->orderBy('due_at')->get(),
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

    // ---------------------------------------------------------- one school

    /** Everything Newline needs about one school, with every lever on the page. */
    public function show(Content $content, $enterpriseId)
    {
        $e = Enterprise::findOrFail($enterpriseId);
        BillingService::refreshAccess($e);
        $e = $e->fresh();

        $owner = $e->administrator_id ? \App\Models\User::find($e->administrator_id) : null;
        $terms = DB::table('terms')->where('enterprise_id', $e->id)
            ->leftJoin('academic_years', 'academic_years.id', '=', 'terms.academic_year_id')
            ->select('terms.*', 'academic_years.name as year_name')
            ->orderByDesc('terms.id')->limit(12)->get();

        return $content->title($e->name)->description('School controls')
            ->body(view('admin.billing.school', [
                'e' => $e,
                'owner' => $owner,
                'label' => BillingService::statusLabel($e),
                'days' => BillingService::daysLeft($e),
                'students' => BillingService::activeStudents($e),
                'staff' => DB::table('admin_users')->where('enterprise_id', $e->id)->whereNotIn('user_type', ['student', 'parent'])->count(),
                'parents' => DB::table('admin_users')->where('enterprise_id', $e->id)->where('user_type', 'parent')->count(),
                'invoices' => Invoice::where('enterprise_id', $e->id)->orderByDesc('id')->limit(40)->get(),
                'payments' => Payment::where('enterprise_id', $e->id)->orderByDesc('id')->limit(25)->get(),
                'subs' => Subscription::where('enterprise_id', $e->id)->with('plan')->orderByDesc('id')->limit(10)->get(),
                'terms' => $terms,
                'activeTerm' => $terms->firstWhere('is_active', 1),
            ]));
    }

    // ---------------------------------------------------------- invoicing

    /** The invoice builder, pre-filled with what we already know. */
    public function newInvoice(Content $content, $enterpriseId)
    {
        $e = Enterprise::findOrFail($enterpriseId);
        $terms = DB::table('terms')->where('enterprise_id', $e->id)
            ->leftJoin('academic_years', 'academic_years.id', '=', 'terms.academic_year_id')
            ->select('terms.*', 'academic_years.name as year_name')
            ->orderByDesc('terms.id')->limit(12)->get();

        return $content->title('Raise an invoice')->description($e->name)
            ->body(view('admin.billing.invoice-form', [
                'e' => $e,
                'students' => BillingService::activeStudents($e),
                'terms' => $terms,
                'suggestedTerm' => $terms->firstWhere('is_active', 1) ?: $terms->first(),
                'defaultDue' => Carbon::now()->addDays((int) config('newline.invoice.default_due_days', 14))->toDateString(),
            ]));
    }

    public function storeInvoice(Request $r, $enterpriseId)
    {
        $e = Enterprise::findOrFail($enterpriseId);
        $d = $r->validate([
            'title' => 'required|string|max:190',
            'description' => 'nullable|string|max:255',
            'due_at' => 'required|date',
            'term_id' => 'nullable|integer',
            'notes' => 'nullable|string|max:4000',
            'inclusions' => 'nullable|string|max:4000',
            'items' => 'required|array|min:1',
            'items.*.label' => 'nullable|string|max:190',
            'items.*.description' => 'nullable|string|max:500',
            'items.*.quantity' => 'nullable|integer|min:1|max:1000000',
            'items.*.unit' => 'nullable|string|max:30',
            'items.*.unit_amount' => 'nullable|integer|min:0|max:100000000',
            'issue_now' => 'nullable',
        ]);

        try {
            $inv = BillingService::createLicenceInvoice($e, [
                'title' => $d['title'],
                'description' => $d['description'] ?? null,
                'due_at' => $d['due_at'],
                'term_id' => $d['term_id'] ?? null,
                'notes' => $d['notes'] ?? null,
                'inclusions' => array_values(array_filter(array_map('trim', preg_split('/\r?\n/', (string) ($d['inclusions'] ?? ''))))),
                'items' => $d['items'],
            ], Admin::user()->id);
        } catch (\Throwable $ex) {
            admin_error('Could not create the invoice', $ex->getMessage());
            return back()->withInput();
        }

        if ($r->filled('issue_now')) {
            BillingService::publishInvoice($inv);
        }
        $this->audit('create-invoice', $e->id, ['invoice' => $inv->number, 'amount' => $inv->amount, 'issued' => (bool) $r->filled('issue_now')]);
        admin_success('Invoice ' . $inv->number . ' created', $r->filled('issue_now') ? 'It is now visible to the school.' : 'Saved as a draft — review it, then issue it to the school.');

        return redirect(admin_url('subscriptions-admin/invoices/' . $inv->id));
    }

    /** Read the document, then decide: issue, void, mark paid, remind. */
    public function showInvoice(Content $content, $invoiceId)
    {
        $inv = Invoice::findOrFail($invoiceId);

        return $content->title('Invoice ' . $inv->number)->description(optional(Enterprise::find($inv->enterprise_id))->name)
            ->body(view('admin.billing.invoice-show', [
                'inv' => $inv,
                'e' => Enterprise::find($inv->enterprise_id),
                'payments' => Payment::where('invoice_id', $inv->id)->orderByDesc('id')->get(),
            ]));
    }

    public function invoiceView($invoiceId)
    {
        return response(InvoiceDocument::html(Invoice::findOrFail($invoiceId)))
            ->header('Content-Type', 'text/html; charset=UTF-8');
    }

    public function invoicePdf($invoiceId)
    {
        return InvoiceDocument::pdf(Invoice::findOrFail($invoiceId), !request()->boolean('download'));
    }

    public function publish($invoiceId)
    {
        $inv = BillingService::publishInvoice(Invoice::findOrFail($invoiceId));
        $this->audit('issue-invoice', $inv->enterprise_id, ['invoice' => $inv->number]);
        admin_success('Issued', $inv->number . ' is now on the school\'s billing page and dashboard.');

        return back();
    }

    public function voidInvoice(Request $r, $invoiceId)
    {
        $inv = Invoice::findOrFail($invoiceId);
        try {
            BillingService::voidInvoice($inv, $r->get('reason'));
        } catch (\Throwable $e) {
            admin_error('Could not void', $e->getMessage());
            return back();
        }
        $this->audit('void-invoice', $inv->enterprise_id, ['invoice' => $inv->number, 'reason' => $r->get('reason')]);
        admin_warning('Voided', $inv->number . ' no longer appears to the school.');

        return back();
    }

    /** Nudge the school: SMS and email carrying the no-login payment link. */
    public function remind($invoiceId)
    {
        $inv = Invoice::findOrFail($invoiceId);
        if (!$inv->isPayable()) {
            admin_error('Nothing to remind about', 'This invoice is ' . $inv->status . '.');
            return back();
        }
        $e = Enterprise::find($inv->enterprise_id);
        $owner = $e->administrator_id ? \App\Models\User::find($e->administrator_id) : null;
        $link = $inv->publicUrl();
        $due = $inv->due_at ? $inv->due_at->format('d M Y') : 'on receipt';
        $sent = [];

        $phone = $owner->phone_number_1 ?? $e->phone_number;
        if ($phone && SmsService::send($phone, config('newline.short_name') . ': Invoice ' . $inv->number . ' for ' . $e->name
            . ' — UGX ' . number_format($inv->amount) . ', due ' . $due . '. Pay or view: ' . $link)) {
            $sent[] = 'SMS to ' . $phone;
        }
        $email = $owner->email ?? $e->email;
        if ($email) {
            try {
                Utils::mail_sender([
                    'email' => $email,
                    'name' => $owner->name ?? $e->name,
                    'subject' => 'Invoice ' . $inv->number . ' — ' . $inv->title . ' (due ' . $due . ')',
                    'body' => '<p>Dear ' . e($owner->name ?? $e->name) . ',</p>'
                        . '<p>Please find invoice <b>' . $inv->number . '</b> for <b>' . e($e->name) . '</b>, '
                        . 'amounting to <b>UGX ' . number_format($inv->amount) . '</b>, due <b>' . $due . '</b>.</p>'
                        . '<p><a href="' . $link . '" style="background:' . config('newline.brand') . ';color:#fff;padding:10px 18px;'
                        . 'text-decoration:none;border-radius:6px;font-weight:bold">View and pay the invoice</a></p>'
                        . '<p>Or open: <br>' . $link . '</p>'
                        . '<p>Payment by Mobile Money, Visa or Mastercard is confirmed instantly and activates your system access automatically.</p>'
                        . '<p>' . config('newline.legal_name') . '<br>' . config('newline.email') . '</p>',
                    'data' => 'Invoice ' . $inv->number . ': UGX ' . number_format($inv->amount) . ' due ' . $due . '. ' . $link,
                ]);
                $sent[] = 'email to ' . $email;
            } catch (\Throwable $ex) {
                Log::error('Invoice reminder email failed', ['invoice' => $inv->id, 'error' => $ex->getMessage()]);
            }
        }

        $inv->update(['sent_at' => Carbon::now()]);
        $this->audit('remind', $e->id, ['invoice' => $inv->number, 'sent' => $sent]);
        $sent ? admin_success('Reminder sent', implode(' and ', $sent) . '.')
              : admin_error('Nothing could be sent', 'No usable phone or email on file for this school.');

        return back();
    }

    private function audit(string $action, int $enterpriseId, array $extra = []): void
    {
        Log::info('Subscriptions console: ' . $action, $extra + ['enterprise' => $enterpriseId, 'by' => Admin::user()->id]);
    }
}
