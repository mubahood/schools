<?php

namespace App\Http\Controllers;

use App\Models\Billing\Invoice;
use App\Models\Enterprise;
use App\Models\User;
use App\Services\BillingService;
use App\Services\InvoiceDocument;
use Illuminate\Http\Request;

/**
 * An invoice a school can open, read, download and pay without logging in.
 *
 * This is deliberately outside the admin guard: when a licence lapses the
 * system locks, and the one thing that must never be locked is the bill that
 * unlocks it. The token is the only credential, so nothing here exposes
 * anything beyond the invoice itself.
 */
class PublicInvoiceController extends Controller
{
    private function find(string $token): Invoice
    {
        return Invoice::where('public_token', $token)
            ->where('status', '<>', Invoice::DRAFT)
            ->firstOrFail();
    }

    public function show(string $token)
    {
        $inv = $this->find($token);

        return view('billing.public-invoice', [
            'inv' => $inv,
            'ent' => Enterprise::find($inv->enterprise_id),
            'co' => config('newline'),
            'flash' => session('billing_flash'),
        ]);
    }

    /** The document itself, framed by the page above so its CSS stays isolated. */
    public function raw(string $token)
    {
        return response(InvoiceDocument::html($this->find($token)))
            ->header('Content-Type', 'text/html; charset=UTF-8')
            ->header('X-Frame-Options', 'SAMEORIGIN');
    }

    public function pdf(string $token, ?Request $r = null)
    {
        return InvoiceDocument::pdf($this->find($token), !($r ?: request())->boolean('download'));
    }

    public function pay(string $token)
    {
        $inv = $this->find($token);
        if ($inv->isPaid()) {
            return redirect('invoice/' . $token);
        }
        $ent = Enterprise::find($inv->enterprise_id);
        $payer = $ent && $ent->administrator_id ? User::find($ent->administrator_id) : null;
        if (!$payer) {
            return redirect('invoice/' . $token)->with('billing_flash', ['type' => 'error', 'text' => 'This school has no billing contact on record. Please call ' . config('newline.phones')[0] . '.']);
        }
        try {
            $url = BillingService::initiatePesapal($inv, $payer);
        } catch (\Throwable $e) {
            return redirect('invoice/' . $token)->with('billing_flash', ['type' => 'error', 'text' => $e->getMessage()]);
        }

        return redirect()->away($url);
    }
}
