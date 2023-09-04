<?php
use App\Models\Utils;
use App\Models\StudentHasClass;
use App\Models\StudentHasTheologyClass;

$max_bot = 30;
$max_mot = 40;
$max_eot = 60;
$tr = isset($tr) ? $tr : null;
$ent = $r->ent;
$owner = $r->owner;

$tr = $r->get_theology_report();
$termly_report_card = $r->termly_report_card;
$theology_termly_report_card = null;

$grading_scale = $termly_report_card->grading_scale;
$class_teacher_name = '..........................................';
$class_teacher_name_1 = '..........................................';
$hm_name = '..........................................';
if ($ent->hm_name != null && strlen($ent->hm_name) > 1) {
    $hm_name = $ent->hm_name;
}
if ($r->academic_class != null) {
    if ($r->academic_class->class_teacher != null) {
        $class_teacher_name = $r->academic_class->class_teacher->name;
    }
}

if ($tr != null) {
    $theology_termly_report_card = $tr->termly_report_card;

    if ($tr->theology_class != null) {
        $_teacher = $tr->theology_class->get_class_teacher();
        if ($_teacher != null) {
            $class_teacher_name_1 = $_teacher->name;
        }
    }
}
$stream_class = '';
$theo_stream_class = '.......';
$hasClass = StudentHasClass::where(['administrator_id' => $r->owner->id, 'academic_class_id' => $r->academic_class_id])->first();
if ($hasClass != null) {
    if ($hasClass->stream != null) {
        $stream_class = ' - ' . $hasClass->stream->name;
    }
}

if ($tr != null) {
    $hasClass = StudentHasTheologyClass::where(['administrator_id' => $tr->owner->id, 'theology_class_id' => $tr->theology_class_id])->first();
    if ($hasClass != null) {
        if ($hasClass->stream != null) {
            $theo_stream_class = ' - ' . $hasClass->stream->name;
        }
    }
}
if ($tr == null) {
    $tr = $r->get_theology_report();
}
if ($tr != null) {
    $theology_termly_report_card = $tr->termly_report_card;
}

$bal = ((int) $r->owner->account->balance);
$bal_text = '........';
if ($bal == 0) {
    $bal_text = 'NIL BALANCE';
} else {
    if ($bal < 0) {
        $bal = -1 * $bal;
    }
    $bal_text = 'UGX ' . number_format($bal);
}

if (!$r->owner->account->status) {
    $bal_text = '...................';
}

$numFormat = new NumberFormatter('en_US', NumberFormatter::ORDINAL);
foreach ($r->termly_report_card->term->exams as $exam) {
    if ($exam->type == 'B.O.T') {
        $max_bot = $exam->max_mark;
    }
    if ($exam->type == 'M.O.T') {
        $max_mot = $exam->max_mark;
    }
    if ($exam->type == 'E.O.T') {
        $max_eot = $exam->max_mark;
    }
}
?>
<article>

    <table class="w-100">
        <tr>
            <td style="width: 16%">
                <img style="width: 100%; " src="{{ public_path('storage/' . $ent->logo) }}">
            </td>
            <td>
                <div class="text-center">
                    <p>
                        <img style="width: 40%; " src="{{ public_path('assets/bismillah.png') }}">
                    </p>
                    <p class="fs-28 text-center fw-200 mt-2 text-uppercase text-primary">{{ $ent->name }}</p>
                    <p><i>"{{ $ent->motto }}"</i></p>
                    <p class="fs-14 lh-6 mt-2">TEL: {{ $ent->phone_number }},&nbsp;{{ $ent->phone_number_2 }}</p>
                    <p class="fs-14 lh-6 mt-1">EMAIL: {{ $ent->email }}, WEBSITE: {{ $ent->website }}</p>
                    <p class="fs-14 mt-1">{{ $ent->p_o_box }}, &nbsp; {{ $ent->address }}</p>
                </div>
            </td>
            <td style="width: 16%">
                @php
                    $avatar = $r->owner->getAvatarPath();
                @endphp
                @if (!str_contains($avatar, 'user.jpeg'))
                    <img style="width: 100%;" src="{{ public_path($avatar) }}">
                @endif
            </td>
        </tr>
    </table>

    <hr class="my-2" style="background-color:  {{ $r->ent->color }}; height: 2px; padding: 0px; margin: 0px; ">

    <p class="fs-20 text-center"><u>{{ $termly_report_card->report_title }}</u></p>

    <div class="text-left mt-2 fs-14 text-uppercase">
        NAME: <b>{{ $r->owner->name }}</b> &nbsp;

        @if ($r->owner->sex != null && strlen($r->owner->sex) > 1)
            GENDER: <b>{{ $r->owner->sex }}</b> &nbsp;
        @endif

        @if ($r->termly_report_card->reports_who_fees_balance == 'Yes')
            SCHOOL FEES BALANCE: <b>{{ $bal_text }}</b> &nbsp;
        @endif
        SCHOOL PAY CODE: <b>{{ $r->owner->school_pay_payment_code }}</b> &nbsp;
    </div>

    <p class="text-center my-3 mt-4">
        <span
            style="
                    padding: 8px;
                    border-radius: 10px;
                    border: 3px <?= $ent->color ?> solid; "
            class="text-center text-uppercase fs-14 fw-200">secular report</span>
    </p>

    <div class="text-uppercase">
        CLASS: <b>{{ $r->academic_class->short_name }} {{ $stream_class }}&nbsp;</b>
        {{-- STREAM: <b> {{ $stream_class }}&nbsp;</b> --}}
        TERM: <b>{{ $r->termly_report_card->term->name }}</b> &nbsp;
        YEAR: <b>{{ $r->termly_report_card->academic_year->name }}</b> &nbsp;
        Aggregates: <b class="text-danger">{{ (int) $r->average_aggregates }}</b> &nbsp;
        DIVISION: <b class="text-danger">{{ (int) $r->grade }}</b> &nbsp;

        @if ($r->display_positions == 'Yes')
            position: <b class="text-danger">{{ (int) $r->position }}</b> &nbsp;
            OUT OF: <b class="text-danger">{{ (int) $r->total_students }}</b> &nbsp;
        @endif


    </div>

    <table class="table table-bordered marks-table p-0 m-0 w-100 mt-2">
        <thead class="p-0 m-0 text-center" style="line-height: 12px;">
            <th class="text-left p-1"><b>SUBJECTS</b></th>
            @if ($termly_report_card->reports_include_bot == 'Yes')
                <th class="p-1 m-0" colspan="2">
                    <b>B.O.T</b>
                    <small class="d-block">({{ $termly_report_card->bot_max }})</small>
                </th>
            @endif
            @if ($termly_report_card->reports_include_mot == 'Yes')
                <th class="p-1 m-0" colspan="2">
                    <b>M.O.T</b>
                    <small class="d-block">({{ $termly_report_card->mot_max }})</small>
                </th>
            @endif
            @if ($termly_report_card->reports_include_eot == 'Yes')
                <th class="p-1 m-0" colspan="2">
                    <b>E.O.T</b>
                    <small class="d-block">({{ $termly_report_card->eot_max }})</small>
                </th>
            @endif
            @if ($termly_report_card->positioning_method != 'Specific')
                <th class="p-1"><b>MARKS</b>
                    <small class="d-block"> ({{ $max_mot }}%)</small>
                </th>
                <th class="p-1">AGGR</th>
            @endif
            <th class="remarks p-1"><b class="text-uppercase">Remarks</b></th>
            <th class="remarks text-center p-1"><b class="text-uppercase">Initials</b></th>
        </thead>
        @php
            $span = 0;
            $bot_tot = 0;
            $mot_tot = 0;
            $eot_tot = 0;
            if ($termly_report_card->reports_include_bot == 'Yes') {
                $span++;
            }
            if ($termly_report_card->reports_include_mot == 'Yes') {
                $span++;
            }
            if ($termly_report_card->reports_include_eot == 'Yes') {
                $span++;
            }
        @endphp
        @foreach ($termly_report_card->get_student_marks($owner->id) as $v)
            <tr class="marks">
                @php
                    if ($v->subject == null) {
                        $v->delete();
                        continue;
                    }
                @endphp

                @php
                    $bot_tot += $v->bot_score;
                    $mot_tot += $v->mot_score;
                    $eot_tot += $v->eot_score;
                @endphp
                <th>{{ $v->subject->subject_name }}</th>
                @if ($termly_report_card->reports_include_bot == 'Yes')
                    <td>{{ (int) $v->bot_score }}</td>
                    <td>{{ Utils::generateAggregates($grading_scale, $v->bot_score)->name }}</td>
                @endif
                @if ($termly_report_card->reports_include_mot == 'Yes')
                    <td>{{ (int) $v->mot_score }}</td>
                    <td>{{ $v->get_grade($grading_scale, $v->mot_score) }}</td>
                @endif
                @if ($termly_report_card->reports_include_eot == 'Yes')
                    <td>{{ (int) $v->eot_score }}</td>
                    <td>{{ $v->get_grade($grading_scale, $v->eot_score) }}</td>
                @endif
                @if ($termly_report_card->positioning_method != 'Specific')
                    <td>{{ (int) $v->total_score_display }}</td>
                    <td>{{ $v->aggr_name }}</td>
                @endif
                <td class="remarks">{{ $v->remarks }}</td>
                <td class="remarks text-center">{{ $v->initials }}</td>
            </tr>
        @endforeach
        <tr class="marks">
            <th><b>TOTAL</b></th>
            @if ($termly_report_card->reports_include_bot == 'Yes')
                <th class="text-center">{{ $bot_tot }}</th>
                <th></th>
            @endif
            @if ($termly_report_card->reports_include_mot == 'Yes')
                <th class="text-center">{{ $mot_tot }}</th>
                <th></th>
            @endif
            @if ($termly_report_card->reports_include_eot == 'Yes')
                <th class="text-center">{{ $eot_tot }}</th>
                <th></th>
            @endif
            @if ($termly_report_card->positioning_method != 'Specific')
                <td class="text-center"><b>{{ $r->total_marks }}</b></td>
                <td><b>{{ $r->total_aggregates }}</b></td>
            @endif
            <td colspan="2"></td>
        </tr>
    </table>
    <p class="mt-2 fw-16"><span class="text-uppercase">Class Teacher's Comment:</span> <b class="comment"
            style="font-size: 14px">{{ $r->class_teacher_comment }}</b></p>
    <p class="mt-2 fw-16"><span class="text-uppercase">Class Teacher's Name:</span> <b style="font-size: 14px"
            class="text-uppercase">{{ $class_teacher_name }}</b>,&nbsp;
        <span class="text-uppercase fs-16 ">Signature:<b>...............................</b></span>
    </p>

    @if ($tr != null)
        <p class="text-center my-3 mt-4">
            <span
                style="
                    padding: 8px;
                    border-radius: 10px;
                    border: 3px <?= $ent->color ?> solid; "
                class="text-center text-uppercase fs-14 fw-200">theology report</span>
        </p>
        <div class="text-uppercase">
            CLASS: <b>{{ $tr->theology_class->short_name . $theo_stream_class }}&nbsp;</b>
            {{-- STREAM: <b> {{ $theo_stream_class }}&nbsp;</b> --}}
            Aggregates: <b class="text-danger">{{ (int) $tr->average_aggregates }}</b> &nbsp;
            DIVISION: <b class="text-danger">{{ (int) $tr->grade }}</b> &nbsp;
            @if ($tr->display_positions == 'Yes')
                position: <b class="text-danger">{{ (int) $tr->position }}</b> &nbsp;
                OUT OF: <b class="text-danger">{{ (int) $tr->total_students }}</b> &nbsp;
            @endif
        </div>
        <table class="table table-bordered marks-table p-0 m-0 w-100 mt-2">
            <thead class="p-0 m-0 text-center" style="line-height: 12px;">
                <th class="text-left p-1"><b>SUBJECTS</b></th>
                @if ($theology_termly_report_card->reports_include_bot == 'Yes')
                    <th colspan="2" class="p-1 m-0">
                        <b>B.O.T</b>
                        <small class="d-block">({{ $termly_report_card->bot_max }})</small>
                    </th>
                @endif
                @if ($theology_termly_report_card->reports_include_mot == 'Yes')
                    <th colspan="2" class="p-1 m-0">
                        <b>M.O.T</b>
                        <small class="d-block">({{ $termly_report_card->mot_max }})</small>
                    </th>
                @endif
                @if ($theology_termly_report_card->reports_include_eot == 'Yes')
                    <th colspan="2" class="p-1 m-0">
                        <b>E.O.T</b>
                        <small class="d-block">({{ $termly_report_card->eot_max }})</small>
                    </th>
                @endif
                @if ($termly_report_card->positioning_method != 'Specific')
                    <th class="p-1"><b>MARKS</b>
                        <small class="d-block">average - ({{ '100' }}%)</small>
                    </th>
                    <th class="p-1">AGGR</th>
                @endif
                <th class="remarks p-1"><b class="text-uppercase">Remarks</b></th>
                <th class="remarks text-center p-1"><b class="text-uppercase">Initials</b>
                </th>
            </thead>
            @php
                $span = 0;
                if ($theology_termly_report_card->reports_include_bot == 'Yes') {
                    $span++;
                }
                if ($theology_termly_report_card->reports_include_mot == 'Yes') {
                    $span++;
                }
                if ($theology_termly_report_card->reports_include_eot == 'Yes') {
                    $span++;
                }
            @endphp

            @php
                
                $span = 0;
                $bot_tot = 0;
                $mot_tot = 0;
                $eot_tot = 0;
                
            @endphp
            @foreach ($theology_termly_report_card->get_student_marks($owner->id) as $v)
                <tr class="marks">
                    @php
                        if ($v->subject == null) {
                            $v->delete();
                            continue;
                        }
                        if ($v->total_score_display == 0) {
                            continue;
                        }
                        $span = 0;
                        $bot_tot += $v->bot_score;
                        $mot_tot += $v->mot_score;
                        $eot_tot += $v->eot_score;
                        
                    @endphp
                    <th>{{ $v->subject->name }}</th>
                    @if ($termly_report_card->reports_include_bot == 'Yes')
                        <td>{{ (int) $v->bot_score }}</td>
                        <td>{{ $v->get_grade($grading_scale, $v->eot_score) }}</td>
                    @endif
                    @if ($termly_report_card->reports_include_mot == 'Yes')
                        <td>{{ (int) $v->mot_score }}</td>
                        <td>{{ $v->get_grade($grading_scale, $v->mot_score) }}</td>
                    @endif
                    @if ($termly_report_card->reports_include_eot == 'Yes')
                        <td>{{ (int) $v->eot_score }}</td>
                        <td>{{ $v->get_grade($grading_scale, $v->eot_score) }}</td>
                    @endif
                    @if ($termly_report_card->positioning_method != 'Specific')
                        <td>{{ (int) $v->total_score_display }}</td>
                        <td>{{ $v->aggr_name }}</td>
                    @endif
                    <td class="remarks">{{ $v->remarks }}</td>
                    <td class="remarks text-center">{{ $v->initials }}</td>
                </tr>
            @endforeach
            <tr class="marks">
                <th><b>TOTAL</b></th>
                @if ($termly_report_card->reports_include_bot == 'Yes')
                    <th class="text-center">{{ $bot_tot }}</th>
                    <th></th>
                @endif
                @if ($termly_report_card->reports_include_mot == 'Yes')
                    <th class="text-center">{{ $mot_tot }}</th>
                    <th></th>
                @endif
                @if ($termly_report_card->reports_include_eot == 'Yes')
                    <th class="text-center">{{ $eot_tot }}</th>
                    <th></th>
                @endif
                @if ($termly_report_card->positioning_method != 'Specific')
                    <td class="text-center"><b>{{ $tr->total_marks }}</b></td>
                    <td><b>{{ $tr->total_aggregates }}</b></td>
                @endif
                <td colspan="2"></td>
            </tr>

        </table>
        <p class="mt-2 fw-16"><span class="text-uppercase">Class Teacher's comment:</span> <b class="comment"
                style="font-size: 14px">{{ $tr->class_teacher_comment }}</b></p>
        <p class="mt-2 fw-16"><span class="text-uppercase">Class Teacher's Name:</span> <b style="font-size: 14px"
                class="text-uppercase">{{ $class_teacher_name_1 }}</b>,&nbsp;
            <span class="text-uppercase fs-16 ">Signature:<b>...............................</b></span>
        </p>
    @endif

    <hr style="background-color:  {{ $r->ent->color }}; height: 2px; 
            padding: 0px; margin-bottom: 6px;   "
        class="my-3">

    <p class="mt-2 fw-16"><span class="text-uppercase">Mentor's comment:</span> <b class="comment"
            style="font-size: 14px">{{ $r->mentor_comment }}</b></p>
    <p class="mt-2 fw-16"><span class="text-uppercase">Co-Curricular Activities comment:</span> <b class="comment"
            style="font-size: 14px">{{ $r->sports_comment }}</b></p>
    <p class="mt-2 fw-16"><span class="text-uppercase">Nurse's comment:</span> <b class="comment"
            style="font-size: 14px">{{ $r->nurse_comment }}</b></p>



    <p class="mt-2 fw-16"><span class="text-uppercase">HEAD TEACHER'S COMMENT:</span> <b class="comment"
            style="font-size: 14px">{{ $r->head_teacher_comment }}</b></p>
    <p class="mt-2 fw-16"><span class="text-uppercase">HEAD Teacher's Name:</span> <b style="font-size: 14px"
            class="text-uppercase">{{ $hm_name }}</b>,&nbsp;
        <span class="text-uppercase fs-16 ">Signature:<b>...............................</b></span>
    </p>
    <br>

    <hr
        style="background-color:  {{ $r->ent->color }}; height: 2px; 
            padding: 0px; margin-bottom: 6px; margin-top: 10px; ">

    {{--  <table class="w-100 mt-0">
        <tbody>
            <tr>
                <td>
                    <h2 class="p-0 text-center m-0 bg-black text-uppercase" style="font-size: 14px;">Aggregates Scale
                    </h2>
                    <table class="table table-bordered grade-table w-100">
                        <tbody>
                            <tr class="text-center">
                                <th class="text-left fs-12 lh-1 pt-2">Mark</th>
                                <th class="fs-10 p-0 lh-1 pt-2">00 - 39</th>
                                <th class="fs-10 p-0 lh-1 pt-2">40 - 44</th>
                                <th class="fs-10 p-0 lh-1 pt-2">45 - 49</th>
                                <th class="fs-10 p-0 lh-1 pt-2">50 - 54</th>
                                <th class="fs-10 p-0 lh-1 pt-2">55 - 59</th>
                                <th class="fs-10 p-0 lh-1 pt-2">60 - 69</th>
                                <th class="fs-10 p-0 lh-1 pt-2">70 - 79</th>
                                <th class="fs-10 p-0 lh-1 pt-2">80 - 89</th>
                                <th class="fs-10 p-0 lh-1 pt-2">90 - 100</th>
                            </tr>
                            <tr>
                                <th class="text-left fs-12 lh-1 pt-2">Aggregates</th>
                                <td class="bordered-table text-center value fs-12 lh-1 pt-2">F9</td>
                                <td class="bordered-table text-center value fs-12 lh-1 pt-2">P8</td>
                                <td class="bordered-table text-center value fs-12 lh-1 pt-2">P7</td>
                                <td class="bordered-table text-center value fs-12 lh-1 pt-2">C6</td>
                                <td class="bordered-table text-center value fs-12 lh-1 pt-2">C5</td>
                                <td class="bordered-table text-center value fs-12 lh-1 pt-2">C4</td>
                                <td class="bordered-table text-center value fs-12 lh-1 pt-2">C3</td>
                                <td class="bordered-table text-center value fs-12 lh-1 pt-2">D2</td>
                                <td class="bordered-table text-center value fs-12 lh-1 pt-2">D1</td>
                            </tr>
                        </tbody>
                    </table>
                </td>
                <td>

                    <h2 class="p-0 text-center m-0  bg-black text-uppercase m-0" style="font-size: 14px;">Grading
                        Scale
                    </h2>
                    <table class="table table-bordered grade-table">
                        <tbody>
                            <tr class="text-center">
                                <th class="text-left  fs-12 lh-1 pt-2">Aggregates</th>
                                <th class=" fs-12 lh-1 pt-2">4 - 12</th>
                                <th class=" fs-12 lh-1 pt-2">13 - 23</th>
                                <th class=" fs-12 lh-1 pt-2">24 - 29</th>
                                <th class=" fs-12 lh-1 pt-2">30 - 33</th>
                                <th class=" fs-12 lh-1 pt-2">34 - 36</th>
                            </tr>
                            <tr>
                                <th class="text-left fs-12 lh-1 pt-2">DIVISION</th>
                                <td class="bordered-table text-center value fs-12 lh-1 pt-2 ">1</td>
                                <td class="bordered-table text-center value fs-12 lh-1 pt-2 ">2</td>
                                <td class="bordered-table text-center value  fs-12 lh-1 pt-2">3</td>
                                <td class="bordered-table text-center value  fs-12 lh-1 pt-2">4</td>
                                <td class="bordered-table text-center value  fs-12 lh-1 pt-2">U</td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
 --}}




    <div class=" mt-0 d-flex justify-content-between p-0 pt-1 " style="font-size: 14px;">
        {!! $r->termly_report_card->bottom_message !!}
    </div>
    <p class="text-right"><small>Printed on: <b>{{ Utils::my_date_3(now()) }}</b></small></p>


</article>
