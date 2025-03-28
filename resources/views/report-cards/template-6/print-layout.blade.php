<?php
use App\Models\Utils;
use App\Models\StudentHasClass;
use App\Models\StudentHasTheologyClass;

$max_bot = 30;
$max_mot = 40;
$max_eot = 60;
$tr = isset($tr) ? $tr : null;
$report_type = isset($report_type) ? $report_type : null;
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
    if ($tr->termly_report_card == null) {
        $tr = null;
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
$hasTheologyClass = null;
$hasClass = StudentHasClass::where(['administrator_id' => $r->owner->id, 'academic_class_id' => $r->academic_class_id])->first();
if ($hasClass != null) {
    if ($hasClass->stream != null) {
        $stream_class = ' - ' . $hasClass->stream->name;
    }
}

if ($tr != null) {
    $hasTheologyClass = StudentHasTheologyClass::where(['administrator_id' => $tr->owner->id, 'theology_class_id' => $tr->theology_class_id])->first();
    if ($hasTheologyClass != null) {
        if ($hasTheologyClass->stream != null) {
            $theo_stream_class = ' - ' . $hasTheologyClass->stream->name;
        }
    }
}
if ($tr == null) {
    $tr = $r->get_theology_report();
}
if ($tr != null) {
    $theology_termly_report_card = $tr->termly_report_card;
}
if ($hasTheologyClass == null) {
    $tr = null;
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
$exam_num = 0;
$show_avg = false;
foreach ($r->termly_report_card->term->exams as $exam) {
    if ($exam->type == 'B.O.T') {
        $max_bot = $exam->max_mark;
        $exam_num++;
    }
    if ($exam->type == 'M.O.T') {
        $max_mot = $exam->max_mark;
        $exam_num++;
    }
    if ($exam->type == 'E.O.T') {
        $max_eot = $exam->max_mark;
        $exam_num++;
    }
}
if ($exam_num < 2) {
    $show_avg = false;
} else {
    $show_avg = true;
}
/* $theology_termly_report_card->generate_positions = 'Yes';
$theology_termly_report_card->report_title .= '.';
$theology_termly_report_card->save();
die("done");
dd($theology_termly_report_card); */
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
                    <p class="fs-16 lh-6 mt-2">TEL: {{ $ent->phone_number }},&nbsp;{{ $ent->phone_number_2 }}</p>
                    <p class="fs-16 lh-6 mt-1">EMAIL: {{ $ent->email }}, WEBSITE: {{ $ent->website }}</p>
                    <p class="fs-16 mt-1">{{ $ent->p_o_box }}, &nbsp; {{ $ent->address }}</p>
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

    <hr class="mt-4 mb-3" style="background-color:  {{ $r->ent->color }}; height: 2px; padding: 0px; margin: 0px; ">

    <p class="fs-26 mb-3 text-center"><u>{{ $termly_report_card->report_title }}</u></p>

    <div class="text-left  fs-16 text-uppercase mb-1">
        NAME: <b>{{ $r->owner->name }}</b> &nbsp;

        @if ($r->owner->sex != null && strlen($r->owner->sex) > 1)
            GENDER: <b>{{ $r->owner->sex }}</b> &nbsp;
        @endif
        @if ($owner->lin != null && strlen($owner->lin) > 4)
            LIN: <b>{{ $owner->lin }}</b> &nbsp;
        @endif

        @if ($r->termly_report_card->reports_who_fees_balance == 'Yes')
            SCHOOL FEES BALANCE: <b>{{ $bal_text }}</b> &nbsp;
        @endif
        SCHOOLPAY CODE: <b>{{ $r->owner->school_pay_payment_code }}</b> &nbsp;
    </div>

    @if ($report_type == 'Secular')


        <p class="text-center my-2 mt-2">
            <span
                style="
                    padding: 8px;
                    border-radius: 10px;
                    border: 3px <?= $ent->color ?> solid; "
                class="text-center text-uppercase fs-20 fw-800">secular report</span>
        </p>

        <div class="text-uppercase fs-16">
            CLASS: <b>{{ $r->academic_class->short_name }} {{ $stream_class }}&nbsp;</b>
            {{-- STREAM: <b> {{ $stream_class }}&nbsp;</b> --}}
            TERM: <b>{{ $r->termly_report_card->term->name }}</b> &nbsp;
            YEAR: <b>{{ $r->termly_report_card->academic_year->name }}</b> &nbsp;
            Aggregate: <b class="text-danger">{{ (int) $r->average_aggregates }}</b> &nbsp;
            DIVISION: <b class="text-danger">{{ $r->grade }}</b> &nbsp;



            @if ($r->termly_report_card->display_positions == 'Yes')
                position: <b
                    class="text-danger">{{ (int) $r->position }}<sup>{{ Utils::getSuperscriptSuffix($r->position) }}</sup>
                </b> &nbsp;
                OUT OF: <b class="text-danger">{{ (int) $r->total_students }}</b> &nbsp;
            @elseif ($r->termly_report_card->display_positions == 'Manual')
                position: <b class="text-danger">......</b>
                &nbsp;
                OUT OF: <b class="text-danger">......</b> &nbsp;
            @endif


        </div>

        <table class="table table-bordered marks-table p-0 m-0 w-100 mt-2">
            <thead class="p-0 m-0 text-center" style="line-height: 12px;">
                <th class="text-left p-1"><b>SUBJECTS</b></th>
                @if ($termly_report_card->reports_include_bot == 'Yes')
                    <th class="p-1 m-0" colspan="2">
                        <b>{{ $termly_report_card->bot_name }}</b>
                        {{-- <small class="d-block">({{ $termly_report_card->bot_max }})</small> --}}
                    </th>
                @endif
                @if ($termly_report_card->reports_include_mot == 'Yes')
                    <th class="p-1 m-0" colspan="2">
                        <b>{{ $termly_report_card->mot_name }}</b>
                        {{-- <small class="d-block">({{ $termly_report_card->mot_max }})</small> --}}
                    </th>
                @endif
                @if ($termly_report_card->reports_include_eot == 'Yes')
                    <th class="p-1 m-0" colspan="2">
                        <b>{{ $termly_report_card->eot_name }}</b>
                        {{-- <small class="d-block">({{ $termly_report_card->eot_max }})</small> --}}
                    </th>
                @endif

                @if ($show_avg)
                    <th class="p-1"><b>AVERAGE MARKS</b>
                        <small class="d-block"> ({{ '100' }}%)</small>
                    </th>
                    <th class="p-1">AGGR</th>
                @endif

                <th class="remarks p-1 text-center"><b class="text-uppercase">Remarks</b></th>
                <th class="remarks text-center p-1" colspan="2"><b class="text-uppercase">Initials</b></th>
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
                        if ($hasClass == null) {
                            continue;
                        }

                        if ($hasClass->academic_class_id != $v->subject->academic_class_id) {
                            continue;
                        }

                        if ($v->subject->show_in_report != 'Yes') {
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
                        <td>{{ $v->subject->grade_subject == 'Yes' ? $v->bot_grade : '-' }}
                    @endif
                    @if ($termly_report_card->reports_include_mot == 'Yes')
                        <td>{{ (int) $v->mot_score }}</td>
                        <td>{{ $v->subject->grade_subject == 'Yes' ? $v->mot_grade : '-' }}
                        </td>
                    @endif
                    @if ($termly_report_card->reports_include_eot == 'Yes')
                        <td>{{ (int) $v->eot_score }}</td>
                        <td>{{ $v->subject->grade_subject == 'Yes' ? $v->eot_grade : '-' }}
                        </td>
                    @endif
                    @if ($show_avg)
                        <td>{{ $v->subject->grade_subject == 'Yes' ? $v->total_score_display : '' }}</td>
                        <td>{{ $v->aggr_name }}</td>
                    @endif

                    <td class="remarks text-center">{{ $v->remarks }}</td>
                    <td class="remarks text-center" colspan="2">
                        {{ $v->initials }}</td>
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
                    <th>{{ (int) $r->average_aggregates }}</th>
                @endif

                <td class="text-center"><b></b></td>
                @if ($show_avg)
                    <td><b>{{ $r->total_aggregates }}</b></td>
                @endif

                @if ($show_avg)
                    <td colspan="1"></td>
                @else
                    <td colspan="2"></td>
                @endif
            </tr>
        </table>



        @if ($r->termly_report_card->display_class_teacher_comments == 'Yes')
            <p class="mt-3 fw-16" style="font-size: 14!important;"><span class="text-uppercase">Class Teacher's
                    Comment:</span> <b class="comment" style="font-size: 16px">{!! $termly_report_card->display_class_teacher_comments == 'Yes'
                        ? $r->class_teacher_comment
                        : Utils::get_empty_spaces(150) . '<br>' . Utils::get_empty_spaces(150) !!}</b></p>
        @else
            <p class="mt-3 fw-16" style="font-size: 14!important;"><span class="text-uppercase">Class Teacher's
                    Comment:</span> <b class="" style="font-size: 14px">
                    ........................................................................................................<br><br>........................................................................................
                    ........................................................................................
                    {{-- {{ Utils::capitalizeSentences($r->class_teacher_comment) }} --}}</b></p>
        @endif



        <p class="mt-2 " style="font-size: 16!important;"><span class="text-uppercase">Class Teacher:</span>
            {{-- <b style="font-size: 14px" class="text-uppercase">{{ $class_teacher_name }}</b>,&nbsp; --}}
            <b style="font-size: 14px" class="text-uppercase">.......................................</b>&nbsp;
            <span class="text-uppercase fs-14 ">Signature:<b>.....................</b></span>
        </p>

    @endif

    @if ($tr != null)
        @php
            $isOneExam = false;
            $no_of_exams = 0;
            if ($theology_termly_report_card->reports_include_bot == 'Yes') {
                $no_of_exams += 1;
            }
            if ($theology_termly_report_card->reports_include_mot == 'Yes') {
                $no_of_exams += 1;
            }
            if ($theology_termly_report_card->reports_include_eot == 'Yes') {
                $no_of_exams += 1;
            }

            if ($no_of_exams == 1) {
                $isOneExam = true;
            } else {
                $isOneExam = false;
            }

        @endphp

        @if ($report_type == 'Theology')
            <p class="text-center my-4 mt-4">
                <span
                    style="
                    padding: 8px;
                    border-radius: 10px;
                    border: 3px <?= $ent->color ?> solid; "
                    class="text-center text-uppercase fs-24 fw-800">theology report</span>
            </p>
            <div class="text-uppercase" style="font-size: 18px">
                CLASS: <b>{{ $tr->theology_class->short_name . $theo_stream_class }}&nbsp;</b>
                {{-- STREAM: <b> {{ $theo_stream_class }}&nbsp;</b> --}}
                Aggregate: <b class="text-danger">{{ (int) $tr->average_aggregates }}</b> &nbsp;
                DIVISION: <b class="text-danger">{{ $tr->grade }}</b> &nbsp;
                @if ($theology_termly_report_card->display_positions == 'Yes')
                    position: <b
                        class="text-danger">{{ (int) $tr->position }}{{ Utils::getSuperscriptSuffix($tr->position) }}</b>
                    &nbsp;
                    OUT OF: <b class="text-danger">{{ (int) $tr->total_students }}</b> &nbsp;
                @elseif ($r->termly_report_card->display_positions == 'Manual')
                    position: <b class="text-danger">......</b>
                    &nbsp;
                    OUT OF: <b class="text-danger">......</b> &nbsp;
                @endif
            </div>
            <table class="table table-bordered marks-table p-0 m-0 w-100 mt-2">
                <thead class="p-0 m-0 text-center" style="line-height: 12px;">
                    <th class="text-left p-1"><b>SUBJECTS</b></th>
                    @if ($theology_termly_report_card->reports_include_bot == 'Yes')
                        <th colspan="2" class="p-1 m-0">
                            <b>{{ $theology_termly_report_card->bot_name }}</b>
                            <small class="d-block">({{ $termly_report_card->bot_max }})</small>
                        </th>
                    @endif
                    @if ($theology_termly_report_card->reports_include_mot == 'Yes')
                        <th colspan="2" class="p-1 m-0">
                            <b>{{ $theology_termly_report_card->mot_name }}</b>
                            <small class="d-block">({{ $termly_report_card->mot_max }})</small>
                        </th>
                    @endif
                    @if ($theology_termly_report_card->reports_include_eot == 'Yes')
                        <th colspan="2" class="p-1 m-0">
                            <b>{{ $theology_termly_report_card->eot_name }}</b>
                            <small class="d-block">({{ $termly_report_card->eot_max }})</small>
                        </th>
                    @endif

                    @if (!$isOneExam)
                        <th class="p-1"><b>AVERAGE MARKS</b>
                            <small class="d-block">({{ '100' }}%)</small>
                        </th>
                        <th class="p-1">AGGR</th>
                    @endif

                    <th class="remarks p-1 text-center"><b class="text-uppercase">Remarks</b></th>
                    <th class="remarks text-center p-1"><b class="text-uppercase">Initials</b></th>
                </thead>
                @php
                    $span = 0;
                    $done_ids = [];
                    $no_of_exams = 0;
                    if ($theology_termly_report_card->reports_include_bot == 'Yes') {
                        $span++;
                        $no_of_exams += 1;
                    }
                    if ($theology_termly_report_card->reports_include_mot == 'Yes') {
                        $span++;
                        $no_of_exams += 1;
                    }
                    if ($theology_termly_report_card->reports_include_eot == 'Yes') {
                        $span++;
                        $no_of_exams += 1;
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

                            if (in_array($v->subject->id, $done_ids)) {
                                continue;
                            }
                            $done_ids[] = $v->subject->id;

                            if ($hasTheologyClass == null) {
                                continue;
                            }

                            if ($hasTheologyClass->theology_class_id != $v->subject->theology_class_id) {
                                continue;
                            }

                            $span = 0;
                            $bot_tot += $v->bot_score;
                            $mot_tot += $v->mot_score;
                            $eot_tot += $v->eot_score;

                        @endphp
                        <th>{{ $v->subject->name }}</th>
                        @if ($theology_termly_report_card->reports_include_bot == 'Yes')
                            <td>{{ $v->bot_score }}</td>
                            <td>{{ $v->bot_grade }}</td>
                        @endif
                        @if ($theology_termly_report_card->reports_include_mot == 'Yes')
                            <td>{{ (int) $v->mot_score }}</td>
                            <td>{{ $v->mot_grade }}</td>
                        @endif
                        @if ($theology_termly_report_card->reports_include_eot == 'Yes')
                            <td>{{ (int) $v->eot_score }}</td>
                            <td>{{ $v->eot_grade }}</td>
                        @endif
                        <td class="remarks text-center">{{ $v->remarks }}</td>
                        <td class="remarks text-center">{{ $v->initials }}</td>
                    </tr>
                @endforeach
                <tr class="marks">
                    <th><b>TOTAL</b></th>
                    @if ($theology_termly_report_card->reports_include_bot == 'Yes')
                        <th class="text-center">{{ $bot_tot }}</th>
                        <th>{{ (int) $tr->average_aggregates }}</th>
                    @endif
                    @if ($theology_termly_report_card->reports_include_mot == 'Yes')
                        <th class="text-center">{{ $mot_tot }}</th>
                        <th>{{ (int) $tr->average_aggregates }}</th>
                    @endif
                    @if ($theology_termly_report_card->reports_include_eot == 'Yes')
                        <th class="text-center">{{-- {{ $eot_tot }} --}}</th>
                    @endif
                    {{-- <td>{{ (int) $tr->average_aggregates }}</td> --}}
                    {{--                     <td><b>{{ $tr->total_aggregates }}</b></td> --}}
                    <td colspan="2"></td>
                </tr>

            </table>
            <p class="mt-3 fw-16" style="font-size: 18px"><span class="text-uppercase">Class Teacher's
                    comment:</span> <b class="comment" style="font-size: 18px" style="font-size: 18px">
                    {!! $termly_report_card->display_class_teacher_comments == 'Yes'
                        ? $tr->class_teacher_comment
                        : Utils::get_empty_spaces(150) . '<br>' . Utils::get_empty_spaces(150) !!}
                </b></p>
            <p class="mt-2 fw-16"><span class="text-uppercase" style="font-size: 18px">Class Teacher's Name:</span>
                {{-- <b style="font-size: 14px" class="text-uppercase">{{ $class_teacher_name_1 }}</b>,&nbsp; --}}
                <b style="font-size: 18px"
                    class="text-uppercase">.............................................</b>,&nbsp;
                <span class="text-uppercase fs-16 ">Signature:<b>............................</b></span>
            </p>
        @endif
    @endif

    <hr style="background-color:  {{ $r->ent->color }}; 
            font-size: 18px;
            height: 2px; 
            padding: 0px; margin-bottom: 6px;   "
        class="my-3">

    {{-- <p class="mt-2 fw-16"><span class="text-uppercase">Mentor's comment:</span> <b class="comment"
            style="font-size: 14px">{{ $r->mentor_comment }}</b></p>
    <p class="mt-2 fw-16"><span class="text-uppercase">Co-Curricular Activities comment:</span> <b class="comment"
            style="font-size: 14px">{{ $r->sports_comment }}</b></p>
    <p class="mt-2 fw-16"><span class="text-uppercase">Nurse's comment:</span> <b class="comment"
            style="font-size: 14px">{{ $r->nurse_comment }}</b></p> --}}



    <p class="mt-2 fw-14"><span class="text-uppercase" style="font-size: 14px">HEAD TEACHER'S COMMUNICATION:</span>
        <b class="comment"
            style="font-size: 14px">{{ Utils::capitalizeSentences($r->termly_report_card->hm_communication) }}</b>
    </p>
    <p class="mt-2 fw-14"><span class="text-uppercase">HEAD Teacher:</span> <b style="font-size: 14px"
            class="text-uppercase">{{ $hm_name }}</b>,&nbsp;
        <span class="text-uppercase fs-14 ">Signature:
            @php
                $signature_path = null;
                $last_seg = null;
                $segs = [];

                if ($ent->hm_signature != null && strlen($ent->hm_signature) > 3) {
                    $segs = explode('/', $ent->hm_signature);
                }
                //if not empty $segs
                if (!empty($segs)) {
                    $last_seg = end($segs);
                }
                if ($last_seg != null && strlen($last_seg) > 3) {
                    $signature_path = public_path('storage/images/' . $last_seg);
                }
            @endphp
            @if ($signature_path != null && file_exists($signature_path))
                <img style="width: 70px; " src="{{ $signature_path }}">
            @else
                
            @endif
        </span>
    </p>
    <br>

    {{--     <hr
        style="background-color:  {{ $r->ent->color }}; height: 2px; 
            padding: 0px; margin-bottom: 2px; margin-top: 5px; "> --}}

    <table class="w-100 mt-0">
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
                                <th class=" fs-12 lh-1 pt-2">13 - 24</th>
                                <th class=" fs-12 lh-1 pt-2">24 - 29</th>
                                <th class=" fs-12 lh-1 pt-2">30 - 35</th>
                                <th class=" fs-12 lh-1 pt-2">36 > </th>
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





    <div class=" mt-0 d-flex justify-content-between p-0 pt-1 " style="font-size: 14px;">
        {!! $r->termly_report_card->bottom_message !!}
    </div>
    <p class="text-right"><small>Printed on: <b>{{ Utils::my_date_3(now()) }}</b></small></p>


</article>
