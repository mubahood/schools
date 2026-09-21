<?php

namespace App\Services;

use App\Models\Billing\Invoice;
use App\Models\Enterprise;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

/**
 * Turns an invoice into the document the school actually receives — one set of
 * view data used identically by the on-screen preview and the PDF, so what a
 * bursar sees on the page is exactly what downloads.
 */
class InvoiceDocument
{
    public static function data(Invoice $inv): array
    {
        $ent = Enterprise::find($inv->enterprise_id);
        $term = $inv->term_id ? DB::table('terms')->where('id', $inv->term_id)->first() : null;
        $owner = $ent && $ent->administrator_id ? User::find($ent->administrator_id) : null;

        return [
            'inv' => $inv,
            'items' => $inv->items()->get(),
            'ent' => $ent,
            'owner' => $owner,
            'termLabel' => $term ? BillingService::termLabel($term) : null,
            'termWindow' => $term && $term->starts && $term->ends
                ? \Carbon\Carbon::parse($term->starts)->format('d M Y') . ' to ' . \Carbon\Carbon::parse($term->ends)->format('d M Y')
                : null,
            'co' => config('newline'),
            'logo' => self::embed(config('newline.logo')),
            'inclusions' => $inv->inclusionList(),
            'payUrl' => $inv->publicUrl(),
        ];
    }

    /** Base64 so the same markup renders in the browser and inside dompdf. */
    public static function embed(?string $relativePath): ?string
    {
        if (!$relativePath) {
            return null;
        }
        $path = public_path($relativePath);
        if (!is_file($path)) {
            return null;
        }
        $mime = str_ends_with(strtolower($path), '.png') ? 'image/png' : 'image/jpeg';

        return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
    }

    public static function html(Invoice $inv): string
    {
        return View::make('billing.invoice', self::data($inv))->render();
    }

    /** Opens in the browser's viewer by default; ?download=1 saves it. */
    public static function pdf(Invoice $inv, bool $inline = true)
    {
        $pdf = \App::make('dompdf.wrapper');
        $pdf->getDomPDF()->getOptions()->set('isHtml5ParserEnabled', true);
        $pdf->loadHTML(self::html($inv))->setPaper('a4', 'portrait');
        $name = $inv->number . ' - ' . preg_replace('/[^A-Za-z0-9 \-]/', '', (string) optional(Enterprise::find($inv->enterprise_id))->name) . '.pdf';

        return $inline ? $pdf->stream($name, ['Attachment' => false]) : $pdf->download($name);
    }
}
