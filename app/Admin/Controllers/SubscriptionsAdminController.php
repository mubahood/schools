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
        $perPage = min(100, max(10, (int) $r->get('per_page', 25)));

        $q = Enterprise::where('id', '<>', 1);
        if ($search !== '') {
            $q->where(function ($w) use ($search) {
                foreach (['name', 'subdomain', 'subdomain_slug', 'email', 'phone_number'] as $c) {
                    $w->orWhere($c, 'like', "%{$search}%");
                }
            });
        }
        switch ($filter) {
            case 'past_due':  $q->where('access_status', 'past_due'); break;
            case 'suspended': $q->where('access_status', 'suspended'); break;
            case 'trialing':  $q->where('access_status', 'trialing'); break;
            case 'exempt':    $q->where('billing_exempt', 1); break;
            case 'paying':    $q->where('billing_exempt', 0)->where('access_status', 'active'); break;
            case 'owing':     $q->whereIn('id', Invoice::where('status', Invoice::ISSUED)->select('enterprise_id')); break;
            case 'overdue':   $q->whereIn('id', Invoice::where('status', Invoice::ISSUED)
                                ->whereNotNull('due_at')->where('due_at', '<', Carbon::now())->select('enterprise_id')); break;
        }

        $page = $q->orderBy('name')->paginate($perPage, ['*'], 'page', max(1, (int) $r->get('page', 1)))->appends($r->query());
        $ids = collect($page->items())->pluck('id');

        // Everything the table needs, in a handful of queries rather than one
        // set per row. The page used to fire 374 queries for 38 schools.
        $students = DB::table('admin_users')->whereIn('enterprise_id', $ids)
            ->where('user_type', 'student')->where('status', 1)
            ->selectRaw('enterprise_id, COUNT(*) n')->groupBy('enterprise_id')->pluck('n', 'enterprise_id');

        $open = Invoice::whereIn('enterprise_id', $ids)->where('status', Invoice::ISSUED)
            ->selectRaw('enterprise_id, COUNT(*) n, SUM(amount) total, MIN(due_at) soonest')
            ->groupBy('enterprise_id')->get()->keyBy('enterprise_id');

        $dueInvoice = Invoice::whereIn('enterprise_id', $ids)->where('status', Invoice::ISSUED)
            ->orderByRaw('CASE WHEN due_at IS NULL THEN 1 ELSE 0 END')->orderBy('due_at')->orderBy('id')
            ->get()->groupBy('enterprise_id')->map->first();

        $lastPaid = Payment::whereIn('enterprise_id', $ids)->where('status', Payment::SUCCEEDED)
            ->orderByDesc('received_at')->get()->groupBy('enterprise_id')->map->first();

        $subs = Subscription::whereIn('enterprise_id', $ids)->where('status', Subscription::ACTIVE)
            ->with('plan')->orderByDesc('id')->get()->groupBy('enterprise_id')->map->first();

        $rows = collect($page->items())->map(function (Enterprise $e) use ($students, $open, $dueInvoice, $lastPaid, $subs) {
            $o = $open[$e->id] ?? null;
            $sub = $subs[$e->id] ?? null;
            $pay = $lastPaid[$e->id] ?? null;
            $due = $dueInvoice[$e->id] ?? null;

            return (object) [
                'ent' => $e,
                'label' => BillingService::statusLabel($e),
                'days' => BillingService::daysLeft($e),
                'students' => (int) ($students[$e->id] ?? 0),
                'plan' => $sub ? $sub->plan->name . ' ' . $sub->months() . 'm' : null,
                'last_paid' => $pay,
                'open_count' => (int) ($o->n ?? 0),
                'open_total' => (int) ($o->total ?? 0),
                'due' => $due,
                'overdue' => $due && $due->due_at && $due->due_at->isPast(),
                'locked' => ($due && $due->due_at && $due->due_at->isPast())
                    || (!$e->billing_exempt && in_array($e->access_status, ['suspended', 'cancelled'], true)),
            ];
        });

        return $content->title('Subscriptions')->description('Newline console')
            ->body(view('admin.billing.console', [
                'rows' => $rows,
                'page' => $page,
                'filter' => $filter,
                'search' => $search,
                'perPage' => $perPage,
                'claims' => Payment::where('status', Payment::PENDING)->whereIn('gateway', ['bank', 'cash'])
                    ->with('invoice')->orderBy('created_at')->get(),
                'drafts' => Invoice::where('status', Invoice::DRAFT)->orderByDesc('id')->limit(20)->get(),
                'overdue' => Invoice::where('status', Invoice::ISSUED)->whereNotNull('due_at')
                    ->where('due_at', '<', Carbon::now())->orderBy('due_at')->get(),
                'names' => Enterprise::pluck('name', 'id'),
                'stats' => $this->stats(),
            ]));
    }

    /** Headline numbers, each a single aggregate query. */
    private function stats(): array
    {
        $mrr = (int) Subscription::where('status', Subscription::ACTIVE)->get()
            ->sum(fn ($s) => (int) round($s->total_amount / max(1, $s->months())));

        return [
            'mrr' => $mrr,
            'schools' => Enterprise::where('id', '<>', 1)->count(),
            'paying' => Enterprise::where('id', '<>', 1)->where('billing_exempt', 0)->where('access_status', 'active')->count(),
            'trialing' => Enterprise::where('access_status', 'trialing')->count(),
            'exempt' => Enterprise::where('id', '<>', 1)->where('billing_exempt', 1)->count(),
            'suspended' => Enterprise::where('access_status', 'suspended')->count(),
            'owed' => (int) Invoice::where('status', Invoice::ISSUED)->sum('amount'),
            'overdue_total' => (int) Invoice::where('status', Invoice::ISSUED)->whereNotNull('due_at')
                ->where('due_at', '<', Carbon::now())->sum('amount'),
            'collected_30d' => (int) Payment::where('status', Payment::SUCCEEDED)
                ->where('received_at', '>=', Carbon::now()->subDays(30))->sum('amount'),
        ];
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

    /**
     * Give a school N more days.
     *
     * "More days" is counted from the date the school would actually lose
     * access, which is usually an unpaid invoice rather than the access
     * window. The old version only moved the window, so a school locked by an
     * overdue invoice stayed locked however many days were added, and a school
     * with an invoice due tonight was shown months it did not have.
     *
     * Any unpaid invoice falling due before the new date moves to it, the
     * access window is never shortened, and the change is audited.
     */
    public function extend(Request $r, $enterpriseId)
    {
        $d = $r->validate(['days' => 'required|integer|min:1|max:365', 'reason' => 'nullable|string|max:200']);
        $e = Enterprise::findOrFail($enterpriseId);
        $days = (int) $d['days'];

        $current = BillingService::effectiveDeadline($e);
        $base = $current && $current->isFuture() ? $current->copy() : Carbon::now();
        $newEnd = $base->addDays($days)->endOfDay();

        $window = $e->access_ends_at ? Carbon::parse($e->access_ends_at) : null;
        $windowEnd = ($window && $window->gt($newEnd)) ? $window : $newEnd;

        DB::transaction(function () use ($e, $newEnd, $windowEnd, &$moved) {
            DB::table('enterprises')->where('id', $e->id)->update([
                'access_ends_at' => $windowEnd,
                'access_status' => $e->access_status === 'trialing' ? 'trialing' : 'active',
                'has_valid_lisence' => 'Yes',
                'expiry' => $windowEnd->toDateString(),
            ]);
            $moved = Invoice::where('enterprise_id', $e->id)->where('status', Invoice::ISSUED)
                ->where(function ($q) use ($newEnd) {
                    $q->whereNull('due_at')->orWhere('due_at', '<', $newEnd);
                })
                ->update(['due_at' => $newEnd]);
        });

        $this->audit('extend', $e->id, ['days' => $days, 'reason' => $d['reason'] ?? '',
            'until' => $newEnd->toDateString(), 'invoices_moved' => $moved]);
        admin_success('Extended by ' . $days . ' days',
            $e->name . ' has access until ' . $newEnd->format('d M Y')
            . ($moved ? ', and ' . $moved . ' unpaid invoice' . ($moved > 1 ? 's are' : ' is') . ' now due that day.' : '.'));

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
        $terms = DB::table('terms')->where('terms.enterprise_id', $e->id)
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
        $terms = DB::table('terms')->where('terms.enterprise_id', $e->id)
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

    /** Lift a suspension without having to invent a date or an exemption. */
    public function restore(Request $r, $enterpriseId)
    {
        $d = $r->validate(['days' => 'nullable|integer|min:1|max:365']);
        $e = Enterprise::findOrFail($enterpriseId);
        $days = (int) ($d['days'] ?? 14);
        $ends = Carbon::now()->addDays($days);
        DB::table('enterprises')->where('id', $e->id)->update([
            'access_status' => 'active',
            'access_ends_at' => $ends,
            'has_valid_lisence' => 'Yes',
            'expiry' => $ends->toDateString(),
        ]);
        // An overdue invoice would re-lock them on the next request.
        $moved = Invoice::where('enterprise_id', $e->id)->where('status', Invoice::ISSUED)
            ->whereNotNull('due_at')->where('due_at', '<', Carbon::now())
            ->update(['due_at' => $ends]);

        $this->audit('restore', $e->id, ['days' => $days, 'invoices_extended' => $moved]);
        admin_success('Access restored', $e->name . ' is active until ' . $ends->format('d M Y')
            . ($moved ? ', and ' . $moved . ' overdue invoice(s) moved to that date.' : '.'));

        return back();
    }

    private function audit(string $action, int $enterpriseId, array $extra = []): void
    {
        Log::info('Subscriptions console: ' . $action, $extra + ['enterprise' => $enterpriseId, 'by' => Admin::user()->id]);
    }
}
