<?php

namespace App\Admin\Controllers;

use App\Models\Billing\Invoice;
use App\Models\Billing\Plan;
use App\Models\Billing\Subscription;
use App\Models\Enterprise;
use App\Services\BillingService;
use App\Services\InvoiceDocument;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/** The school's own billing page: choose a package, pay, top up SMS. */
class BillingController extends Controller
{
    private function ent(): Enterprise
    {
        return Enterprise::findOrFail(Admin::user()->enterprise_id);
    }

    public function index(Content $content)
    {
        $ent = $this->ent();
        BillingService::refreshAccess($ent);
        $ent = $ent->fresh();

        // One unpaid invoice outranks everything else on this page: a school
        // with a bill to settle should see the bill, not a package chooser.
        $due = BillingService::dueInvoice($ent);
        if ($due) {
            return $content->title('Payment due')->description($ent->name)
                ->body(view('admin.billing.due', [
                    'ent' => $ent,
                    'inv' => $due,
                    'others' => Invoice::where('enterprise_id', $ent->id)->where('status', Invoice::ISSUED)
                        ->where('id', '<>', $due->id)->orderBy('due_at')->get(),
                    'label' => BillingService::statusLabel($ent),
                    'blocked' => session('billing_blocked') ?: (in_array($ent->access_status, ['suspended', 'cancelled']) ? $ent->access_status : null),
                    'pesapalReady' => app(\App\Services\Gateways\PesapalGateway::class)->isConfigured(),
                    'history' => Invoice::where('enterprise_id', $ent->id)->whereIn('status', [Invoice::PAID, Invoice::VOID])->orderByDesc('id')->limit(10)->get(),
                ]));
        }

        $current = Subscription::where('enterprise_id', $ent->id)
            ->whereIn('status', [Subscription::ACTIVE, Subscription::PENDING])
            ->orderByDesc('id')->with('plan')->first();
        $openInvoices = Invoice::where('enterprise_id', $ent->id)->where('status', Invoice::ISSUED)->orderBy('due_at')->get();
        $history = Invoice::where('enterprise_id', $ent->id)->where('status', '<>', Invoice::DRAFT)->orderByDesc('id')->limit(30)->get();

        return $content->title('Subscription & Billing')
            ->description($ent->name)
            ->body(view('admin.billing.index', [
                'ent' => $ent,
                'label' => BillingService::statusLabel($ent),
                'daysLeft' => BillingService::daysLeft($ent),
                'students' => BillingService::activeStudents($ent),
                'plans' => BillingService::plans(),
                'recommended' => BillingService::recommendPlan($ent),
                'current' => $current,
                'openInvoices' => $openInvoices,
                'history' => $history,
                'blocked' => session('billing_blocked'),
                'pesapalReady' => (new \App\Services\Gateways\PesapalGateway())->isConfigured(),
            ]));
    }

    /** Pick a package and go straight to the first payment. */
    public function choose(Request $r)
    {
        $data = $r->validate([
            'plan_id' => 'required|integer|exists:plans,id',
            'period' => 'required|in:6m,12m',
            'instalments' => 'required|in:1,3',
        ]);
        $ent = $this->ent();
        $plan = Plan::findOrFail($data['plan_id']);
        $students = BillingService::activeStudents($ent);
        if ($plan->max_students !== null && $students > $plan->max_students) {
            admin_error('Package too small', "You have {$students} active students; the {$plan->name} package covers up to {$plan->max_students}.");
            return redirect(admin_url('billing'));
        }
        $invoice = BillingService::subscribe($ent, $plan, $data['period'], (int) $data['instalments']);

        return redirect(admin_url('billing/pay/' . $invoice->id));
    }

    /** Send the payer to Pesapal for one invoice. */
    public function pay($invoiceId)
    {
        $ent = $this->ent();
        $inv = Invoice::where('enterprise_id', $ent->id)->findOrFail($invoiceId);
        try {
            $url = BillingService::initiatePesapal($inv, Admin::user());
        } catch (\Throwable $e) {
            admin_error('Could not start payment', $e->getMessage());
            return redirect(admin_url('billing'));
        }

        return redirect()->away($url);
    }

    /** The document, framed by the page so its own CSS cannot leak into admin. */
    public function invoiceView($invoiceId)
    {
        $inv = Invoice::where('enterprise_id', $this->ent()->id)->where('status', '<>', Invoice::DRAFT)->findOrFail($invoiceId);

        return response(InvoiceDocument::html($inv))->header('Content-Type', 'text/html; charset=UTF-8');
    }

    public function invoicePdf($invoiceId)
    {
        $inv = Invoice::where('enterprise_id', $this->ent()->id)->where('status', '<>', Invoice::DRAFT)->findOrFail($invoiceId);

        return InvoiceDocument::pdf($inv);
    }

    public function topup(Request $r)
    {
        $data = $r->validate(['amount' => 'required|integer|min:5000|max:5000000']);
        $inv = BillingService::topUpSms($this->ent(), (int) $data['amount']);

        return redirect(admin_url('billing/pay/' . $inv->id));
    }

    /** School says "I paid by bank transfer" — recorded as pending for Newline to confirm. */
    public function bankNotice(Request $r, $invoiceId)
    {
        $data = $r->validate(['reference' => 'required|string|max:80']);
        $ent = $this->ent();
        $inv = Invoice::where('enterprise_id', $ent->id)->where('status', Invoice::ISSUED)->findOrFail($invoiceId);
        \App\Models\Billing\Payment::create([
            'invoice_id' => $inv->id, 'enterprise_id' => $ent->id, 'gateway' => 'bank',
            'gateway_ref' => 'bank-claim:' . $inv->id . ':' . time(), 'merchant_ref' => $inv->number,
            'amount' => $inv->amount, 'status' => 'pending', 'method' => 'Bank transfer, ref ' . $data['reference'],
            'recorded_by' => Admin::user()->id,
        ]);
        admin_success('Recorded', 'Thank you. Newline will confirm the transfer and activate your subscription.');

        return redirect(admin_url('billing'));
    }
}
