<?php

/**
 * Newline Technologies Limited — the vendor's own identity, used on invoices,
 * receipts and any other document the school receives from us.
 *
 * Contact details are those published on https://ntl.co.ug. Anything we cannot
 * verify (TIN, bank account) is left empty on purpose and simply does not
 * render — an invoice must never carry an invented registration or account.
 * Fill them in .env when you have them:
 *   NEWLINE_TIN, NEWLINE_BANK_NAME, NEWLINE_BANK_ACCOUNT_NAME,
 *   NEWLINE_BANK_ACCOUNT_NO, NEWLINE_BANK_BRANCH, NEWLINE_BANK_SWIFT,
 *   NEWLINE_MOMO_NAME, NEWLINE_MOMO_NUMBER
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

    // Statutory / settlement details — rendered only when present.
    'tin' => env('NEWLINE_TIN', ''),
    'bank' => [
        'bank_name' => env('NEWLINE_BANK_NAME', ''),
        'account_name' => env('NEWLINE_BANK_ACCOUNT_NAME', ''),
        'account_no' => env('NEWLINE_BANK_ACCOUNT_NO', ''),
        'branch' => env('NEWLINE_BANK_BRANCH', ''),
        'swift' => env('NEWLINE_BANK_SWIFT', ''),
    ],
    'momo' => [
        'name' => env('NEWLINE_MOMO_NAME', ''),
        'number' => env('NEWLINE_MOMO_NUMBER', ''),
    ],

    'invoice' => [
        'default_due_days' => 14,
        'signatory_name' => env('NEWLINE_SIGNATORY_NAME', 'Muhindo Mubaraka'),
        'signatory_title' => env('NEWLINE_SIGNATORY_TITLE', 'Chief Operations Officer'),
        'signature_image' => env('NEWLINE_SIGNATURE_IMAGE', ''),
        'footer_note' => 'Thank you for partnering with Newline Technologies Limited.',
    ],
];
