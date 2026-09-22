<?php

/**
 * Newline Technologies Limited — the vendor's own identity, used on invoices,
 * receipts and any other document the school receives from us.
 *
 * Contact details are those published on https://ntl.co.ug.
 *
 * Settlement is Pesapal only, by deliberate policy: no TIN, no bank account and
 * no mobile-money number appear on an invoice. One payment link, confirmed by
 * the gateway, is the whole story — so there is no off-system transfer for
 * anyone to claim, mistype or chase a receipt for.
 */
return [
    'legal_name' => 'Newline Technologies Limited',
    'short_name' => 'Newline Technologies',
    'tagline' => 'Building Bridges of Technology',
    'address_lines' => [
        'E-TOWER, Kampala Road',
        '5th Floor, Suite No. E04',
        'Kampala, Uganda',
    ],
    'phones' => ['+256 414 581 765', '+256 772 851 937'],
    'email' => 'info@ntl.co.ug',
    'website' => 'www.ntl.co.ug',
    'website_url' => 'https://ntl.co.ug',

    // Brand
    'brand' => '#009EE1',
    'brand_dark' => '#0B1B2B',
    'ink' => '#14202E',
    'muted' => '#6B7A8C',
    'logo' => 'assets/images/newline-logo-print.png',
    'logo_large' => 'assets/images/newline-logo.png',
    'logo_small' => 'assets/images/newline-logo-sm.png',

    'invoice' => [
        'default_due_days' => 14,
        'signatory_name' => env('NEWLINE_SIGNATORY_NAME', 'Muhindo Mubaraka'),
        'signatory_title' => env('NEWLINE_SIGNATORY_TITLE', 'Chief Operations Officer'),
        // Signed automatically on every invoice. Override per-environment if needed.
        'signature_image' => env('NEWLINE_SIGNATURE_IMAGE', 'assets/images/newline-signature.png'),
        'footer_note' => 'Thank you for partnering with Newline Technologies Limited.',
    ],
];
