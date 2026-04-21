<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm 8mm 8mm 8mm;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9px;
            line-height: 1.18;
            color: #000;
            padding: 8px;
        }

        /* === HEADER === */
        .meta-line {
            width: 100%;
            margin-bottom: 3px;
            font-size: 11px;
            font-weight: 700;
            font-style: italic;
        }
        .meta-line td {
            padding: 0;
        }
        .meta-line .meta-school {
            color: #0a8f3f;
            text-transform: uppercase;
        }
        .meta-line .meta-teacher {
            color: #0a8f3f;
            text-transform: uppercase;
            text-align: right;
        }
        .term-line {
            text-align: center;
            color: #d10000;
            font-size: 11px;
            font-style: italic;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 2px;
        }
        .subject-line {
            text-align: center;
            color: #0068bf;
            font-size: 10.5px;
            font-style: italic;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        /* === DATA TABLE === */
        .scheme-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 7.8px;
            line-height: 1.12;
        }
        .scheme-table thead th {
            background: #d9d9d9;
            color: #000;
            font-size: 6.8px;
            font-weight: 700;
            text-transform: uppercase;
            text-align: center;
            padding: 2px 1px;
            border: 1px solid #111;
            white-space: normal;
        }
        .scheme-table tbody td {
            padding: 1.5px 2px;
            border: 1px solid #111;
            vertical-align: top;
            font-size: 7.6px;
            word-wrap: break-word;
            overflow-wrap: break-word;
            page-break-inside: avoid;
        }

        /* Column widths matching sample layout */
        .col-wk { width: 2.4%; text-align: center; }
        .col-pd { width: 2.3%; text-align: center; }
        .col-theme { width: 4.5%; }
        .col-topic { width: 6%; }
        .col-subtopic { width: 6.3%; }
        .col-content { width: 17.4%; }
        .col-comp-sub { width: 7%; }
        .col-comp-lang { width: 7%; }
        .col-methods { width: 8.2%; }
        .col-life-skills { width: 9.2%; }
        .col-activities { width: 10%; }
        .col-materials { width: 8.7%; }
        .col-references { width: 8.1%; }
        .col-rem { width: 3%; }

        .vtext {
            writing-mode: vertical-rl;
            text-orientation: mixed;
            direction: rtl;
            transform: none;
            text-align: center;
            font-weight: 700;
            letter-spacing: 0.25px;
        }

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

    <table class="meta-line">
        <tr>
            <td>
                SCHOOL: <span class="meta-school">{{ $ent->name ?? '' }}</span>
            </td>
            <td class="meta-teacher">
                TEACHER: {{ strtoupper(optional($sub->teacher)->name ?? 'N/A') }}
            </td>
        </tr>
    </table>
    <div class="term-line">{{ $class->name ?? '' }} SCHEME OF WORK, {{ $term->name_text ?? '' }} {{ date('Y') }}</div>
    <div class="subject-line">{{ strtoupper($sub->subject_name ?? $sub->name ?? '') }}</div>

    {{-- ===== DATA TABLE ===== --}}
    <table class="scheme-table">
        <thead>
            <tr>
                <th class="col-wk" rowspan="2">WK</th>
                <th class="col-pd" rowspan="2">PD</th>
                <th class="col-theme" rowspan="2">THEME</th>
                <th class="col-topic" rowspan="2">TOPIC</th>
                <th class="col-subtopic" rowspan="2">SUBTOPIC</th>
                <th class="col-content" rowspan="2">CONTENT</th>
                <th colspan="2">COMPETENCES</th>
                <th class="col-methods" rowspan="2">METHODS &amp;<br>TECHNIQUES</th>
                <th class="col-life-skills" rowspan="2">LIFE SKILLS &amp;<br>VALUES</th>
                <th class="col-activities" rowspan="2">SUGGESTED<br>ACTIVITIES</th>
                <th class="col-materials" rowspan="2">INSTRUCTIONAL<br>MATERIALS</th>
                <th class="col-references" rowspan="2">REFERENCES</th>
                <th class="col-rem" rowspan="2">REM</th>
            </tr>
            <tr>
                <th class="col-comp-sub">SUBJECT</th>
                <th class="col-comp-lang">LANGUAGE</th>
            </tr>
        </thead>
        <tbody>
            @php
                $asPrintable = function ($text) {
                    if ($text === null) {
                        return '—';
                    }

                    $raw = trim((string) $text);
                    if ($raw === '') {
                        return '—';
                    }

                    $normalized = preg_replace('/<\s*br\s*\/?\s*>/i', "\n", $raw);
                    $normalized = preg_replace('/<\s*\/\s*p\s*>/i', "\n", $normalized);
                    $normalized = preg_replace('/<\s*p[^>]*>/i', '', $normalized);
                    $normalized = preg_replace('/<\s*\/\s*div\s*>/i', "\n", $normalized);
                    $normalized = preg_replace('/<\s*div[^>]*>/i', '', $normalized);
                    $normalized = preg_replace('/<\s*li[^>]*>/i', "\n- ", $normalized);
                    $normalized = preg_replace('/<\s*\/\s*li\s*>/i', '', $normalized);
                    $normalized = strip_tags($normalized);
                    $normalized = preg_replace("/\n{2,}/", "\n", $normalized);
                    $normalized = trim($normalized);

                    if ($normalized === '') {
                        return '—';
                    }

                    return nl2br(e($normalized));
                };

                $sortedItems = $items->sortBy(function ($i) {
                    return sprintf(
                        '%03d-%03d-%s-%s',
                        (int) ($i->week ?? 0),
                        (int) ($i->period ?? 0),
                        strtolower(trim((string) ($i->theme ?? ''))),
                        strtolower(trim((string) ($i->topic ?? '')))
                    );
                })->values();

                $weekCounts = [];
                $themeCounts = [];
                $topicCounts = [];

                foreach ($sortedItems as $row) {
                    $wkKey = (string) ($row->week ?? '0');
                    $themeKey = $wkKey . '|' . trim((string) ($row->theme ?? ''));
                    $topicKey = $themeKey . '|' . trim((string) ($row->topic ?? ''));

                    $weekCounts[$wkKey] = ($weekCounts[$wkKey] ?? 0) + 1;
                    $themeCounts[$themeKey] = ($themeCounts[$themeKey] ?? 0) + 1;
                    $topicCounts[$topicKey] = ($topicCounts[$topicKey] ?? 0) + 1;
                }

                $weekRendered = [];
                $themeRendered = [];
                $topicRendered = [];
            @endphp

            @forelse ($sortedItems as $item)
                <tr>
                    @php
                        $wkKey = (string) ($item->week ?? '0');
                        $themeKey = $wkKey . '|' . trim((string) ($item->theme ?? ''));
                        $topicKey = $themeKey . '|' . trim((string) ($item->topic ?? ''));
                    @endphp

                    @if (!isset($weekRendered[$wkKey]))
                        <td class="col-wk text-c" rowspan="{{ $weekCounts[$wkKey] }}"><strong>{{ $item->week }}</strong></td>
                        @php $weekRendered[$wkKey] = true; @endphp
                    @endif

                    <td class="col-pd text-c">{{ $item->period }}</td>

                    @if (!isset($themeRendered[$themeKey]))
                        <td class="col-theme text-l" rowspan="{{ $themeCounts[$themeKey] }}"><div class="vtext">{{ strtoupper($item->theme ?? '—') }}</div></td>
                        @php $themeRendered[$themeKey] = true; @endphp
                    @endif

                    @if (!isset($topicRendered[$topicKey]))
                        <td class="col-topic text-l" rowspan="{{ $topicCounts[$topicKey] }}"><div class="vtext">{{ strtoupper($item->topic ?? '—') }}</div></td>
                        @php $topicRendered[$topicKey] = true; @endphp
                    @endif

                    <td class="col-subtopic text-l">{!! $asPrintable($item->sub_topic ?? '—') !!}</td>
                    <td class="col-content text-l">{!! $asPrintable($item->content ?? $item->supervisor_comment ?? '—') !!}</td>
                    <td class="col-comp-sub text-l">{!! $asPrintable($item->competence_subject ?? $item->competence ?? '—') !!}</td>
                    <td class="col-comp-lang text-l">{!! $asPrintable($item->competence_language ?? '—') !!}</td>
                    <td class="col-methods text-l">{!! $asPrintable($item->methods ?? '—') !!}</td>
                    <td class="col-life-skills text-l">{!! $asPrintable($item->life_skills_values ?? $item->skills ?? '—') !!}</td>
                    <td class="col-activities text-l">{!! $asPrintable($item->suggested_activity ?? '—') !!}</td>
                    <td class="col-materials text-l">{!! $asPrintable($item->instructional_material ?? '—') !!}</td>
                    <td class="col-references text-l">{!! $asPrintable($item->references ?? '—') !!}</td>
                    <td class="col-rem text-l">{!! $asPrintable($item->teacher_comment ?? '—') !!}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="14" class="text-c" style="padding: 8px; color: #999;">No scheme work items found for this subject and term.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Printed on {{ date('d M Y') }} &nbsp;|&nbsp; {{ $ent->name }}
    </div>

</body>
</html>
