<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            size: A4 landscape;
            margin: 15mm 15mm 12mm 15mm;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9px;
            line-height: 1.2;
            color: #222;
            padding: 15px 25px 12px 25px;
        }

        /* === HEADER === */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2px;
        }
        .header-table td {
            vertical-align: middle;
            padding: 0;
        }
        .logo-cell {
            width: 40px;
            text-align: center;
        }
        .logo-cell img {
            width: 38px;
            height: auto;
        }
        .info-cell {
            text-align: center;
            padding: 0 4px;
        }
        .school-name {
            font-size: 15px;
            font-weight: 700;
            text-transform: uppercase;
            color: {{ $ent->color ?? '#337ab7' }};
            margin: 0;
            padding: 0;
            line-height: 1.1;
        }
        .motto {
            font-size: 8px;
            font-style: italic;
            margin: 1px 0;
            color: #555;
        }
        .contacts {
            font-size: 7.5px;
            color: #444;
            margin: 0;
        }
        .header-line {
            height: 3px;
            background: {{ $ent->color ?? '#337ab7' }};
            border: none;
            margin: 3px 0 4px 0;
        }
        .doc-title {
            text-align: center;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            margin: 0 0 4px 0;
            padding: 0;
            text-decoration: underline;
            letter-spacing: 0.3px;
        }

        /* === DATA TABLE === */
        .scheme-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5px;
            line-height: 1.2;
        }
        .scheme-table thead th {
            background: {{ $ent->color ?? '#337ab7' }};
            color: #fff;
            font-size: 7.5px;
            font-weight: 700;
            text-transform: uppercase;
            text-align: center;
            padding: 3px 2px;
            border: 1px solid {{ $ent->color ?? '#337ab7' }};
            white-space: nowrap;
            letter-spacing: 0.2px;
        }
        .scheme-table tbody td {
            padding: 2px 3px;
            border: 1px solid #ccc;
            vertical-align: top;
            font-size: 8.5px;
            word-wrap: break-word;
            page-break-inside: avoid;
        }
        .scheme-table tbody tr:nth-child(even) {
            background: #f8f8f8;
        }
        .scheme-table tbody tr:hover {
            background: #eef4fb;
        }

        /* Column widths - optimised for landscape A4 */
        .col-wk    { width: 3%; text-align: center; }
        .col-pd    { width: 3%; text-align: center; }
        .col-topic { width: 14%; }
        .col-comp  { width: 13%; }
        .col-meth  { width: 10%; }
        .col-skill { width: 10%; }
        .col-act   { width: 12%; }
        .col-mat   { width: 10%; }
        .col-cont  { width: 10%; }
        .col-ref   { width: 8%; }
        .col-rem   { width: 7%; }

        .text-c { text-align: center; }
        .text-l { text-align: left; }
        .text-muted { color: #999; }

        /* Footer */
        .footer {
            margin-top: 6px;
            font-size: 7px;
            color: #888;
            text-align: right;
        }
    </style>
</head>
<body>

    {{-- ===== HEADER ===== --}}
    <table class="header-table">
        <tr>
            <td class="logo-cell">
                <img src="{{ public_path('storage/' . $ent->logo) }}">
            </td>
            <td class="info-cell">
                <div class="school-name">{{ $ent->name }}</div>
                @if($ent->motto)<div class="motto">"{{ $ent->motto }}"</div>@endif
                <div class="contacts">Tel: {{ $ent->phone_number }}@if($ent->phone_number_2), {{ $ent->phone_number_2 }}@endif &nbsp;|&nbsp; {{ $ent->email }} &nbsp;|&nbsp; {{ $ent->website }} &nbsp;|&nbsp; {{ $ent->p_o_box }}</div>
            </td>
            <td style="width: 40px;"></td>
        </tr>
    </table>

    <div class="header-line"></div>

    <div class="doc-title">Scheme of Work &mdash; {{ $class->name ?? '' }} &mdash; {{ $sub->subject_name ?? $sub->name ?? '' }} &mdash; {{ $term->name_text ?? '' }}</div>

    {{-- ===== DATA TABLE ===== --}}
    <table class="scheme-table">
        <thead>
            <tr>
                <th class="col-wk">Wk</th>
                <th class="col-pd">Pd</th>
                <th class="col-topic">Topic</th>
                <th class="col-comp">Competence</th>
                <th class="col-meth">Methods</th>
                <th class="col-skill">Skills</th>
                <th class="col-act">Activities</th>
                <th class="col-mat">Materials</th>
                <th class="col-cont">Content</th>
                <th class="col-ref">References</th>
                <th class="col-rem">Remarks</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($items->sortBy('week') as $item)
                <tr>
                    <td class="col-wk text-c">{{ $item->week }}</td>
                    <td class="col-pd text-c">{{ $item->period }}</td>
                    <td class="col-topic text-l">{!! nl2br(e($item->topic ?? '—')) !!}</td>
                    <td class="col-comp text-l">{!! nl2br(e($item->competence ?? '—')) !!}</td>
                    <td class="col-meth text-l">{!! nl2br(e($item->methods ?? '—')) !!}</td>
                    <td class="col-skill text-l">{!! nl2br(e($item->skills ?? '—')) !!}</td>
                    <td class="col-act text-l">{!! nl2br(e($item->suggested_activity ?? '—')) !!}</td>
                    <td class="col-mat text-l">{!! nl2br(e($item->instructional_material ?? '—')) !!}</td>
                    <td class="col-cont text-l">{!! nl2br(e($item->supervisor_comment ?? '—')) !!}</td>
                    <td class="col-ref text-l">{!! nl2br(e($item->references ?? '—')) !!}</td>
                    <td class="col-rem text-l">{!! nl2br(e($item->teacher_comment ?? '—')) !!}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="text-c" style="padding: 8px; color: #999;">No scheme work items found for this subject and term.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Printed on {{ date('d M Y') }} &nbsp;|&nbsp; {{ $ent->name }}
    </div>

</body>
</html>
