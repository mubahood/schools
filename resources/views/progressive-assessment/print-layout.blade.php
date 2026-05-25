<?php
use App\Models\StudentProgressiveReportItem;

$numTests    = max(1, (int) $assessment->number_of_tests);
$student     = $report->owner;
$class       = $report->academic_class;
$stream      = $report->stream;
$term        = $report->term;
$items       = $report->items()->with(['subject', 'main_course'])->get()
                    ->sortBy(fn($i) => $i->subject->subject_name ?? '');

// Pre-group items by main course name
$grouped = [];
foreach ($items as $item) {
    $courseName = $item->main_course->name ?? 'General';
    $grouped[$courseName][] = $item;
}

$logoPath   = $ent ? public_path('storage/' . $ent->logo) : '';
$hasLogo    = $logoPath && file_exists($logoPath);
$avatarPath = $student ? public_path($student->getAvatarPath()) : '';
$hasAvatar  = $avatarPath && file_exists($avatarPath);
$color      = $ent->color ?? '#003366';
?>
<article style="font-family: Arial, sans-serif; font-size: 11px; padding: 2mm;">

    {{-- ── HEADER ────────────────────────────────────────────────────────────── --}}
    <table width="100%" style="border-collapse: collapse; margin-bottom: 3px;">
        <tr>
            <td width="8%" style="text-align:center; vertical-align:middle;">
                @if($hasLogo)
                    <img src="{{ $logoPath }}" style="max-height:65px; max-width:65px;">
                @endif
            </td>
            <td style="text-align:center; vertical-align:middle; padding: 0 4px;">
                <div style="font-size:17px; font-weight:bold; text-transform:uppercase; color:{{ $color }};">
                    {{ $ent->name ?? '' }}
                </div>
                <div style="font-size:10px; color:#333;">
                    {{ $ent->address ?? '' }}
                    @if($ent->email) &nbsp;|&nbsp; {{ $ent->email }} @endif
                    @if($ent->phone) &nbsp;|&nbsp; {{ $ent->phone }} @endif
                </div>
                <div style="font-size:13px; font-weight:bold; margin-top:4px; text-decoration:underline; color:{{ $color }};">
                    {{ $assessment->title ?? 'PROGRESSIVE ASSESSMENT REPORT' }}
                </div>
                <div style="font-size:10px;">
                    {{ $term->name_text ?? ($term->name ?? '') }}
                    &nbsp;|&nbsp;
                    Academic Year: {{ $report->academic_year->name ?? '' }}
                </div>
            </td>
            <td width="8%" style="text-align:center; vertical-align:middle;">
                @if($hasAvatar)
                    <img src="{{ $avatarPath }}" style="max-height:65px; max-width:65px; border-radius:2px;">
                @endif
            </td>
        </tr>
    </table>

    <hr style="border: 2px solid {{ $color }}; margin: 2px 0 3px 0;">

    {{-- ── STUDENT INFO ───────────────────────────────────────────────────────── --}}
    <table width="100%" style="font-size:11px; margin-bottom:4px; border-collapse:collapse;">
        <tr>
            <td width="33%">
                <b>Name:</b>
                <span style="text-decoration:underline; padding: 0 40px 0 4px;">
                    {{ strtoupper($student->name ?? 'N/A') }}
                </span>
            </td>
            <td width="22%">
                <b>Class:</b>
                <span style="text-decoration:underline; padding: 0 20px 0 4px;">
                    {{ $class->name_text ?? ($class->name ?? 'N/A') }}
                    @if($stream) / {{ $stream->name }} @endif
                </span>
            </td>
            <td width="20%">
                <b>Admission No:</b>
                <span style="text-decoration:underline; padding: 0 20px 0 4px;">
                    {{ $student->username ?? '—' }}
                </span>
            </td>
            <td width="25%" style="text-align:right;">
                <b>Position:</b>
                <?php
                $pos = (int)($report->position ?? 0);
                $total = (int)($report->total_students ?? 0);
                if ($pos > 0) {
                    $sfx = match(($pos % 100 >= 11 && $pos % 100 <= 13) ? 0 : $pos % 10) {
                        1 => 'st', 2 => 'nd', 3 => 'rd', default => 'th'
                    };
                    echo $pos . $sfx . ($total > 0 ? ' out of ' . $total : '');
                } else {
                    echo '—';
                }
                ?>
                &nbsp;&nbsp;
                <b>Grade:</b> {{ $report->grade ?? '—' }}
            </td>
        </tr>
    </table>

    {{-- ── MARKS TABLE ────────────────────────────────────────────────────────── --}}
    <table width="100%" style="border-collapse: collapse; font-size: 10.5px;" border="0">
        <thead>
            <tr style="background-color: {{ $color }}; color: #fff; text-align:center;">
                <th style="border:1px solid #999; padding:3px 4px; text-align:left; width:22%;">SUBJECT</th>
                @for ($i = 1; $i <= $numTests; $i++)
                    <th style="border:1px solid #999; padding:3px 2px; min-width:28px;">T{{ $i }}</th>
                @endfor
                <th style="border:1px solid #999; padding:3px 2px; min-width:34px;">AVG</th>
                <th style="border:1px solid #999; padding:3px 2px; min-width:30px;">GRD</th>
                <th style="border:1px solid #999; padding:3px 2px; min-width:30px;">AGR</th>
                <th style="border:1px solid #999; padding:3px 4px; text-align:left;">REMARKS</th>
            </tr>
        </thead>
        <tbody>
            @php $rowBg = false; @endphp
            @foreach ($grouped as $courseName => $courseItems)
                {{-- Course group header --}}
                <tr style="background-color: #e8f0fb;">
                    <td colspan="{{ $numTests + 5 }}" style="border:1px solid #ccc; padding:2px 4px; font-weight:bold; font-size:10px; color:{{ $color }};">
                        {{ strtoupper($courseName) }}
                    </td>
                </tr>
                @foreach ($courseItems as $item)
                    @php
                        $scores = is_array($item->test_scores)
                            ? $item->test_scores
                            : (json_decode($item->test_scores, true) ?? []);
                        $bgStyle = $rowBg ? 'background-color:#f9f9f9;' : '';
                        $rowBg   = !$rowBg;
                    @endphp
                    <tr style="{{ $bgStyle }} text-align:center;">
                        <td style="border:1px solid #ccc; padding:2px 4px; text-align:left;">
                            {{ $item->subject->subject_name ?? '—' }}
                        </td>
                        @for ($i = 1; $i <= $numTests; $i++)
                            @php
                                $scoreData  = $scores[$i - 1] ?? null;
                                $scoreVal   = $scoreData['score'] ?? null;
                                $submitted  = $scoreData['submitted'] ?? 'No';
                                $display    = ($scoreVal !== null && $scoreVal > 0) ? $scoreVal : ($submitted === 'Yes' ? '*' : '—');
                            @endphp
                            <td style="border:1px solid #ddd; padding:2px;">
                                {{ $display }}
                            </td>
                        @endfor
                        <td style="border:1px solid #ddd; padding:2px; font-weight:bold;">
                            {{ $item->average_mark > 0 ? $item->average_mark : '—' }}
                        </td>
                        <td style="border:1px solid #ddd; padding:2px;">
                            {{ $item->grade_name ?? '—' }}
                        </td>
                        <td style="border:1px solid #ddd; padding:2px;">
                            {{ $item->aggregates ?? '—' }}
                        </td>
                        <td style="border:1px solid #ddd; padding:2px 4px; text-align:left; font-size:10px;">
                            {{ $item->remarks ?? '' }}
                        </td>
                    </tr>
                @endforeach
            @endforeach

            {{-- Totals row --}}
            <tr style="background-color: #eef3fb; font-weight:bold; text-align:center;">
                <td style="border:1px solid #aaa; padding:3px 4px; text-align:left; color:{{ $color }};">
                    TOTAL / GRADE
                </td>
                @for ($i = 1; $i <= $numTests; $i++)
                    <td style="border:1px solid #aaa; padding:3px;"></td>
                @endfor
                <td style="border:1px solid #aaa; padding:3px; color:{{ $color }};">
                    {{ $report->total_marks ?? 0 }}
                </td>
                <td style="border:1px solid #aaa; padding:3px; color:{{ $color }};">
                    {{ $report->grade ?? '—' }}
                </td>
                <td style="border:1px solid #aaa; padding:3px; color:{{ $color }};">
                    {{ $report->total_aggregates ?? '—' }}
                </td>
                <td style="border:1px solid #aaa; padding:3px;"></td>
            </tr>
        </tbody>
    </table>

    {{-- ── COMMENTS ───────────────────────────────────────────────────────────── --}}
    @if($assessment->display_class_teacher_comments !== 'No' || $assessment->hm_communication)
    <table width="100%" style="border-collapse:collapse; font-size:10.5px; margin-top:5px;">
        <tr>
            @if($assessment->display_class_teacher_comments !== 'No')
            <td width="50%" style="border:1px solid #ccc; padding:3px 5px; vertical-align:top;">
                <b>Class Teacher's Comment:</b><br>
                <span style="font-style:italic;">{{ $report->class_teacher_comment ?? '...........................................................' }}</span>
            </td>
            <td width="50%" style="border:1px solid #ccc; padding:3px 5px; vertical-align:top;">
                <b>Head Teacher's Comment:</b><br>
                @if($assessment->hm_communication)
                    <span style="font-style:italic;">{{ $assessment->hm_communication }}</span>
                @else
                    <span style="font-style:italic;">{{ $report->head_teacher_comment ?? '...........................................................' }}</span>
                @endif
            </td>
            @else
            <td width="100%" style="border:1px solid #ccc; padding:3px 5px; vertical-align:top;">
                <b>Head Teacher's Communication:</b><br>
                <span style="font-style:italic;">{{ $assessment->hm_communication ?? '' }}</span>
            </td>
            @endif
        </tr>
    </table>
    @endif

    {{-- ── SIGNATURES ─────────────────────────────────────────────────────────── --}}
    <table width="100%" style="font-size:10.5px; margin-top:6px; border-collapse:collapse;">
        <tr>
            <td width="33%" style="padding:2px 4px;">
                Class Teacher: ......................................
                &nbsp; Sign: .....................
            </td>
            <td width="34%" style="text-align:center; padding:2px 4px;">
                Head Teacher: ......................................
                &nbsp; Sign: .....................
            </td>
            <td width="33%" style="text-align:right; padding:2px 4px;">
                Parent/Guardian: ................................
                &nbsp; Sign: .....................
            </td>
        </tr>
    </table>

    {{-- ── FOOTER MESSAGE ─────────────────────────────────────────────────────── --}}
    @if($assessment->bottom_message)
    <div style="margin-top:4px; font-size:10px; text-align:center; color:#555; border-top:1px solid #ccc; padding-top:3px;">
        {{ $assessment->bottom_message }}
    </div>
    @endif

</article>
