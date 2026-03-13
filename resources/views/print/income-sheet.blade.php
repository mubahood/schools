<?php
/**
 * Income Sheet PDF template.
 * Expects: $incomeSheet, $ent, $transactions
 */
use App\Models\Utils;

$logo_link = '';
if ($ent->logo && strlen($ent->logo) > 3) {
    $logo_link = public_path('storage/' . $ent->logo);
    if (!file_exists($logo_link)) {
        $logo_link = '';
    }
}

$term = $incomeSheet->term;
$term_label = $term ? 'Term ' . $term->name_text : '';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page {
            margin: 12mm 10mm;
            size: A4;
        }
        body {
            font-family: Georgia, 'Times New Roman', Times, serif;
            font-size: 12px;
            color: #222;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }
        .header-table td {
            vertical-align: middle;
        }
        .school-name {
            font-size: 18px;
            font-weight: bold;
            font-family: Arial, Helvetica, sans-serif;
            margin: 0;
            text-transform: uppercase;
        }
        .school-detail {
            font-size: 11px;
            margin: 2px 0 0 0;
            color: #444;
        }
        .motto {
            font-size: 11px;
            font-style: italic;
            margin: 2px 0 0 0;
        }
        .doc-title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            text-decoration: underline;
            margin: 15px 0 5px 0;
            text-transform: uppercase;
        }
        .sub-title {
            text-align: center;
            font-size: 13px;
            font-weight: bold;
            margin: 0 0 10px 0;
        }
        .meta-info {
            margin: 5px 0 12px 0;
            font-size: 12px;
        }
        .meta-info span {
            margin-right: 25px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }
        .data-table th {
            background-color: #e8e8e8;
            border: 1px solid #333;
            padding: 6px 8px;
            font-size: 11px;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
        }
        .data-table td {
            border: 1px solid #555;
            padding: 4px 8px;
            font-size: 11px;
        }
        .data-table td.num {
            text-align: right;
        }
        .data-table td.center {
            text-align: center;
        }
        .data-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .total-row td {
            font-weight: bold;
            background-color: #e0e0e0 !important;
            border-top: 2px solid #333;
        }
        .footer {
            margin-top: 20px;
            font-size: 10px;
            color: #666;
            text-align: center;
        }
        .summary-box {
            margin-top: 15px;
            border: 1px solid #333;
            padding: 10px 15px;
            font-size: 12px;
        }
        .summary-box p {
            margin: 4px 0;
        }
    </style>
</head>
<body>

    {{-- Header --}}
    <table class="header-table">
        <tr>
            @if($logo_link)
            <td style="width: 60px; padding-right: 10px;">
                <img src="{{ $logo_link }}" style="width: 55px; height: auto;">
            </td>
            @endif
            <td style="text-align: center;">
                <p class="school-name">{{ $ent->name }}</p>
                @if(!empty($ent->motto))
                <p class="motto">"{{ $ent->motto }}"</p>
                @endif
                <p class="school-detail">
                    {{ $ent->p_o_box ? $ent->p_o_box . ',' : '' }} {{ $ent->address }}
                    | Tel: {{ $ent->phone_number }}@if(!empty($ent->phone_number_2)), {{ $ent->phone_number_2 }}@endif
                </p>
                @if(!empty($ent->email))
                <p class="school-detail">Email: {{ $ent->email }}</p>
                @endif
            </td>
            @if($logo_link)
            <td style="width: 60px;"></td>
            @endif
        </tr>
    </table>

    <hr style="border: 1px solid #333; margin: 5px 0;">

    {{-- Title --}}
    <p class="doc-title">INCOME SHEET</p>
    @if($incomeSheet->title)
    <p class="sub-title">{{ $incomeSheet->title }}</p>
    @endif

    {{-- Meta info --}}
    <div class="meta-info">
        <span><b>Term:</b> {{ $term_label }}</span>
        <span><b>Period:</b> {{ date('d/m/Y', strtotime($incomeSheet->date_from)) }} - {{ date('d/m/Y', strtotime($incomeSheet->date_to)) }}</span>
        <span><b>Type:</b> {{ str_replace('_', ' ', $incomeSheet->type) }}</span>
    </div>

    {{-- Data Table --}}
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 15%;">Date</th>
                <th style="width: 15%;">Receipt No.</th>
                <th style="width: 25%;">Student Name</th>
                <th style="width: 12%;">Source</th>
                <th style="width: 14%;">Amount (UGX)</th>
                <th style="width: 14%;">Cumulative (UGX)</th>
            </tr>
        </thead>
        <tbody>
            @php
                $cumulative = 0;
                $counter = 0;
            @endphp
            @forelse($transactions as $tr)
            @php
                $counter++;
                $cumulative += abs($tr->amount);
                $student_name = '';
                if ($tr->account && $tr->account->owner) {
                    $student_name = $tr->account->owner->name;
                }
            @endphp
            <tr>
                <td class="center">{{ $counter }}</td>
                <td class="center">{{ date('d/m/Y', strtotime($tr->payment_date)) }}</td>
                <td class="center">{{ $tr->id }}</td>
                <td>{{ $student_name }}</td>
                <td class="center">{{ str_replace('_', ' ', $tr->source ?? '') }}</td>
                <td class="num">{{ number_format(abs($tr->amount)) }}</td>
                <td class="num">{{ number_format($cumulative) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align: center; padding: 20px; color: #888;">No transactions found for the selected period.</td>
            </tr>
            @endforelse

            @if($counter > 0)
            <tr class="total-row">
                <td colspan="5" style="text-align: right; padding-right: 10px;">TOTAL</td>
                <td class="num">{{ number_format($cumulative) }}</td>
                <td class="num">{{ number_format($cumulative) }}</td>
            </tr>
            @endif
        </tbody>
    </table>

    {{-- Summary --}}
    @if($counter > 0)
    <div class="summary-box">
        <p><b>Total Income:</b> UGX {{ number_format($cumulative) }}</p>
        <p><b>Total Transactions:</b> {{ $counter }}</p>
        <p><b>Amount in Words:</b> {{ ucwords(Utils::convert_number_to_words($cumulative)) }} Shillings Only</p>
    </div>
    @endif

    <p class="footer">Generated on {{ date('d/m/Y H:i') }}</p>

</body>
</html>
