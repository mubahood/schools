<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm 8mm 8mm 8mm;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8px;
            line-height: 1.2;
            color: #000;
        }

        /* ── TERM SECTION ── */
        .term-section { width: 100%; }
        .term-section + .term-section { page-break-before: always; }

        /* ── HEADER ── */
        .meta-table { width: 100%; margin-bottom: 3px; }
        .meta-table td {
            font-size: 10px; font-weight: bold;
            font-style: italic; padding: 0;
        }
        .meta-school  { color: #0a8f3f; text-transform: uppercase; }
        .meta-teacher { color: #0a8f3f; text-transform: uppercase; text-align: right; }
        .term-line {
            text-align: center; color: #d10000;
            font-size: 11px; font-style: italic;
            font-weight: bold; text-transform: uppercase; margin-bottom: 1px;
        }
        .subject-line {
            text-align: center; color: #0068bf;
            font-size: 10px; font-style: italic;
            font-weight: bold; text-transform: uppercase; margin-bottom: 4px;
        }

        /* ── TABLE ── */
        .scheme-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 7px;
            line-height: 1.15;
            border: 1.5px solid #222;
        }
        thead { display: table-header-group; }
        tbody { display: table-row-group; }

        .scheme-table th {
            background-color: #c8d8ea;
            color: #000;
            font-size: 6.2px;
            font-weight: bold;
            text-transform: uppercase;
            text-align: center;
            vertical-align: middle;
            padding: 2px 1px;
            border: 1px solid #333;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        .scheme-table th.th-group { background-color: #a8bedc; }

        .scheme-table td {
            padding: 1.5px 2px;
            border: 1px solid #555;
            vertical-align: top;
            font-size: 7px;
            word-wrap: break-word;
            overflow-wrap: break-word;
            overflow: hidden;
        }

        .italic-prefix { color: #555; font-style: italic; font-size: 6.5px; }
        .empty-dash    { color: #bbb; }

        /* ── FOOTER ── */
        .footer {
            margin-top: 4px; font-size: 6.5px;
            color: #888; text-align: right;
        }
    </style>
</head>
<body>

@php
    $tmpl = $sub->scheme_template ?? 'auto';
    if (in_array($tmpl, ['lower', 'generic'])) {
        $isLowerPrimary = true;
    } elseif (in_array($tmpl, ['upper', 'science', 'mathematics', 'language'])) {
        $isLowerPrimary = false;
    } else {
        // 'auto' — infer from class name (P1/P.1/Primary 1 … → upper)
        $_cn = trim((string) ($class->name ?? $class->name_text ?? ''));
        $isLowerPrimary = !preg_match('/\bp\.?\s*[1-9]\b|\bprimary\s*[1-9]\b/i', $_cn);
    }
    // 'language' template: upper layout (has TOPIC/SUB TOPIC) but single Competence column
    $splitCompetence = !$isLowerPrimary && $tmpl !== 'language';
    $totalColumns    = $isLowerPrimary ? 12 : ($splitCompetence ? 14 : 13);

    $currentYear = date('Y');

    $asPrintable = function ($text) {
        if ($text === null) return '<span class="empty-dash">&mdash;</span>';
        $raw = trim((string) $text);
        if ($raw === '') return '<span class="empty-dash">&mdash;</span>';
        $n = preg_replace('/<\s*br\s*\/?\s*>/i', "\n", $raw);
        $n = preg_replace('/<\s*\/?\s*p[^>]*>/i', "\n", $n);
        $n = preg_replace('/<\s*\/?\s*div[^>]*>/i', "\n", $n);
        $n = preg_replace('/<\s*li[^>]*>/i', "\n\xE2\x80\xa2 ", $n);
        $n = preg_replace('/<\s*\/\s*li\s*>/i', '', $n);
        $n = strip_tags($n);
        $n = preg_replace("/\n{3,}/", "\n\n", $n);
        $n = trim($n);
        return $n === '' ? '<span class="empty-dash">&mdash;</span>' : nl2br(e($n));
    };
@endphp

@foreach ($termGroups as $termGroup)
@php
    $term  = $termGroup['term'];
    $items = $termGroup['items'];

    $termNameText = $term->name_text ?? '';
    $titleTerm = (strpos((string) $termNameText, (string) $currentYear) !== false)
                 ? $termNameText
                 : trim($termNameText . ' ' . $currentYear);
    // Prefix "TERM " if the name doesn't already contain the word "term"
    if (!preg_match('/\bterm\b/i', (string) $titleTerm)) {
        $titleTerm = 'TERM ' . $titleTerm;
    }
@endphp
<div class="term-section">

{{-- ── HEADER ── --}}
<table class="meta-table">
    <tr>
        <td>SCHOOL: <span class="meta-school">{{ $ent->name ?? '' }}</span></td>
        <td class="meta-teacher">TEACHER: {{ strtoupper(optional($sub->teacher)->name ?? 'N/A') }}</td>
    </tr>
</table>
<div class="term-line">{{ strtoupper($class->name ?? '') }} SCHEME OF WORK, {{ strtoupper($titleTerm) }}</div>
<div class="subject-line">{{ strtoupper($sub->subject_name ?? $sub->name ?? '') }}</div>

{{-- ── DATA TABLE ── --}}
<table class="scheme-table" width="100%">
    {{--
        colgroup widths (HTML attribute + inline style — belt & suspenders for DomPDF).
        Lower            (12 cols): 3.0+2.5+6.5+8.5+15.0+15.0+9.0+10.0+9.0+8.5+8.0+5.0     = 100%
        Language upper   (13 cols): 2.8+2.5+5.0+6.0+6.5+14.5+15.2+9.0+9.0+9.0+8.5+8.0+4.0  = 100%
        Standard upper   (14 cols): 2.8+2.5+5.0+6.0+6.5+14.5+7.6+7.6+9.0+9.0+9.0+8.5+8.0+4.0 = 100%
    --}}
    <colgroup>
        @if($isLowerPrimary)
            {{-- 12 cols --}}
            <col width="3.0%"  style="width:3.0%">
            <col width="2.5%"  style="width:2.5%">
            <col width="6.5%"  style="width:6.5%">
            <col width="8.5%"  style="width:8.5%">
            <col width="15.0%" style="width:15.0%">
            <col width="15.0%" style="width:15.0%">
            <col width="9.0%"  style="width:9.0%">
            <col width="10.0%" style="width:10.0%">
            <col width="9.0%"  style="width:9.0%">
            <col width="8.5%"  style="width:8.5%">
            <col width="8.0%"  style="width:8.0%">
            <col width="5.0%"  style="width:5.0%">
        @elseif(!$splitCompetence)
            {{-- 13 cols: language upper — single Competence column --}}
            <col width="2.8%"  style="width:2.8%">
            <col width="2.5%"  style="width:2.5%">
            <col width="5.0%"  style="width:5.0%">
            <col width="6.0%"  style="width:6.0%">
            <col width="6.5%"  style="width:6.5%">
            <col width="14.5%" style="width:14.5%">
            <col width="15.2%" style="width:15.2%">
            <col width="9.0%"  style="width:9.0%">
            <col width="9.0%"  style="width:9.0%">
            <col width="9.0%"  style="width:9.0%">
            <col width="8.5%"  style="width:8.5%">
            <col width="8.0%"  style="width:8.0%">
            <col width="4.0%"  style="width:4.0%">
        @else
            {{-- 14 cols: standard upper — Competences split Subject + Language --}}
            <col width="2.8%"  style="width:2.8%">
            <col width="2.5%"  style="width:2.5%">
            <col width="5.0%"  style="width:5.0%">
            <col width="6.0%"  style="width:6.0%">
            <col width="6.5%"  style="width:6.5%">
            <col width="14.5%" style="width:14.5%">
            <col width="7.6%"  style="width:7.6%">
            <col width="7.6%"  style="width:7.6%">
            <col width="9.0%"  style="width:9.0%">
            <col width="9.0%"  style="width:9.0%">
            <col width="9.0%"  style="width:9.0%">
            <col width="8.5%"  style="width:8.5%">
            <col width="8.0%"  style="width:8.0%">
            <col width="4.0%"  style="width:4.0%">
        @endif
    </colgroup>

    {{--
        THEAD — CRITICAL: For lower primary (single header row) do NOT use rowspan="2".
        DomPDF steals the first tbody row to fill the phantom "second header row" when
        rowspan="2" appears in a thead with only one <tr>, pushing Week-1 off to the right.
        Upper primary genuinely has 2 header rows so rowspan="2" is correct there.
    --}}
    <thead>
        @if($isLowerPrimary)
        {{-- Single-row header (12 cols): no TOPIC column --}}
        <tr>
            <th style="width:3.0%">WK</th>
            <th style="width:2.5%">PD</th>
            <th style="width:6.5%">THEME</th>
            <th style="width:8.5%">SUB<br>THEME</th>
            <th style="width:15.0%">CONTENT</th>
            <th style="width:15.0%">COMPETENCE</th>
            <th style="width:9.0%">METHODS&nbsp;&amp;<br>TECHNIQUES</th>
            <th style="width:10.0%">INDICATORS OF<br>LIFE SKILLS<br>&amp;&nbsp;VALUES</th>
            <th style="width:9.0%">SUGGESTED<br>ACTIVITIES</th>
            <th style="width:8.5%">INSTRUCTIONAL<br>MATERIALS</th>
            <th style="width:8.0%">REFERENCES</th>
            <th style="width:5.0%">REM</th>
        </tr>
        @elseif(!$splitCompetence)
        {{-- Single-row header (13 cols): language upper — TOPIC present, single COMPETENCE --}}
        <tr>
            <th style="width:2.8%">WK</th>
            <th style="width:2.5%">PD</th>
            <th style="width:5.0%">THEME</th>
            <th style="width:6.0%">TOPIC</th>
            <th style="width:6.5%">SUB<br>TOPIC</th>
            <th style="width:14.5%">CONTENT</th>
            <th style="width:15.2%">COMPETENCE</th>
            <th style="width:9.0%">METHODS&nbsp;&amp;<br>TECHNIQUES</th>
            <th style="width:9.0%">INDICATORS OF<br>LIFE SKILLS<br>&amp;&nbsp;VALUES</th>
            <th style="width:9.0%">SUGGESTED<br>ACTIVITIES</th>
            <th style="width:8.5%">INSTRUCTIONAL<br>MATERIALS</th>
            <th style="width:8.0%">REFERENCES</th>
            <th style="width:4.0%">REM</th>
        </tr>
        @else
        {{-- Two-row header (14 cols): standard upper — COMPETENCES split into Subject + Language --}}
        <tr>
            <th rowspan="2" style="width:2.8%">WK</th>
            <th rowspan="2" style="width:2.5%">PD</th>
            <th rowspan="2" style="width:5.0%">THEME</th>
            <th rowspan="2" style="width:6.0%">TOPIC</th>
            <th rowspan="2" style="width:6.5%">SUB<br>TOPIC</th>
            <th rowspan="2" style="width:14.5%">CONTENT</th>
            <th class="th-group" colspan="2">COMPETENCES</th>
            <th rowspan="2" style="width:9.0%">METHODS&nbsp;&amp;<br>TECHNIQUES</th>
            <th rowspan="2" style="width:9.0%">INDICATORS OF<br>LIFE SKILLS<br>&amp;&nbsp;VALUES</th>
            <th rowspan="2" style="width:9.0%">SUGGESTED<br>ACTIVITIES</th>
            <th rowspan="2" style="width:8.5%">INSTRUCTIONAL<br>MATERIALS</th>
            <th rowspan="2" style="width:8.0%">REFERENCES</th>
            <th rowspan="2" style="width:4.0%">REM</th>
        </tr>
        <tr>
            <th style="width:7.6%">SUBJECT</th>
            <th style="width:7.6%">LANGUAGE</th>
        </tr>
        @endif
    </thead>

    <tbody>
        @php
            $sortedItems = $items->sortBy(function ($i) {
                return sprintf('%03d-%03d-%s-%s',
                    (int) ($i->week   ?? 0),
                    (int) ($i->period ?? 0),
                    strtolower(trim((string) ($i->theme ?? ''))),
                    strtolower(trim((string) ($i->topic ?? '')))
                );
            })->values();
        @endphp

        @forelse ($sortedItems as $item)
            @php
                $idx  = $loop->index;
                $prev = $idx > 0 ? $sortedItems[$idx - 1] : null;

                $curWk    = (string) ($item->week  ?? '0');
                $curTheme = trim((string) ($item->theme ?? ''));
                $curTopic = trim((string) ($item->topic ?? ''));

                $prevWk    = $prev ? (string) ($prev->week  ?? '0') : null;
                $prevTheme = $prev ? trim((string) ($prev->theme ?? '')) : null;
                $prevTopic = $prev ? trim((string) ($prev->topic ?? '')) : null;

                $contWk    = $prev && $prevWk    === $curWk;
                $contTheme = $contWk  && $prevTheme === $curTheme;
                $contTopic = $contTheme && $prevTopic === $curTopic;

                $rawContent    = trim((string) ($item->content ?? $item->supervisor_comment ?? ''));
                $rawLifeSkills = trim((string) ($item->life_skills_values ?? $item->skills ?? ''));
                $compSub       = trim((string) ($item->competence_subject  ?? ''));
                $compLang      = trim((string) ($item->competence_language ?? ''));
                $sep           = ($compSub !== '' && $compLang !== '') ? "\n" : '';
                $cellCompAll   = trim($compSub . $sep . $compLang) ?: null;

                /* Inline styles for WK/THEME/TOPIC visual merge (no-class approach = safer in DomPDF) */
                $wkBg    = 'background-color:#dce8f5;text-align:center;vertical-align:middle;font-weight:bold;font-size:8px;';
                $themeBg = 'background-color:#eaf1f9;text-align:center;vertical-align:middle;font-weight:bold;font-size:6.5px;';
                $topicBg = 'background-color:#f2f7fc;text-align:center;vertical-align:middle;font-weight:bold;font-size:6.5px;';

                $wkBorder    = $contWk    ? 'border-top:none;'  : '';
                $themeBorder = $contTheme ? 'border-top:none;'  : '';
                $topicBorder = $contTopic ? 'border-top:none;'  : '';
            @endphp

            <tr>
                <td style="{{ $wkBg }}{{ $wkBorder }}">{{ $contWk ? '' : $item->week }}</td>
                <td style="text-align:center;vertical-align:middle;">{{ $item->period }}</td>
                <td style="{{ $themeBg }}{{ $themeBorder }}">{{ $contTheme ? '' : strtoupper($curTheme ?: '—') }}</td>
                @if(!$isLowerPrimary)
                <td style="{{ $topicBg }}{{ $topicBorder }}">{{ $contTopic ? '' : strtoupper($curTopic ?: '—') }}</td>
                @endif

                <td>{!! $asPrintable($item->sub_topic ?? null) !!}</td>

                <td>@if($isLowerPrimary && $rawContent !== '')<span class="italic-prefix">The learner;</span><br>@endif{!! $asPrintable($rawContent ?: null) !!}</td>

                @if($splitCompetence)
                    <td>{!! $asPrintable($item->competence_subject ?? $item->competence ?? null) !!}</td>
                    <td>{!! $asPrintable($item->competence_language ?? null) !!}</td>
                @else
                    <td>{!! $asPrintable($cellCompAll) !!}</td>
                @endif

                <td>{!! $asPrintable($item->methods ?? null) !!}</td>

                <td>{!! $asPrintable($rawLifeSkills ?: null) !!}</td>

                <td>{!! $asPrintable($item->suggested_activity ?? null) !!}</td>
                <td>{!! $asPrintable($item->instructional_material ?? null) !!}</td>
                <td>{!! $asPrintable($item->references ?? null) !!}</td>
                <td>{!! $asPrintable($item->teacher_comment ?? null) !!}</td>
            </tr>
        @empty
            <tr>
                <td colspan="{{ $totalColumns }}" style="padding:10px;text-align:center;color:#999;font-style:italic;">
                    No scheme work items found for this subject and term.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

</div>{{-- .term-section --}}
@endforeach

<div class="footer">Printed on {{ date('d M Y') }} &nbsp;|&nbsp; {{ $ent->name ?? '' }}</div>

</body>
</html>
