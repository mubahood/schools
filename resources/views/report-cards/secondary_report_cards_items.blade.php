<?php
use App\Models\SecondaryCompetence;
use App\Models\GenericSkill;
$NUMBER_OF_UNITS = 0;
$termly_report = $r->termly_secondary_report_card;
if ($termly_report == null) {
    throw new Exception('Termly report card not found for #' . $r->id);
}

if ($termly_report->has_u1 == 'Yes') {
    $NUMBER_OF_UNITS++;
}
if ($termly_report->has_u2 == 'Yes') {
    $NUMBER_OF_UNITS++;
}

if ($termly_report->has_u3 == 'Yes') {
    $NUMBER_OF_UNITS++;
}

if ($termly_report->has_u4 == 'Yes') {
    $NUMBER_OF_UNITS++;
}

if ($termly_report->has_u5 == 'Yes') {
    $NUMBER_OF_UNITS++;
}

/* 
    "id" => 1
    "created_at" => "2024-10-11 22:41:03"
    "updated_at" => "2024-10-12 14:46:46"
    "enterprise_id" => 19
    "academic_year_id" => 17
    "term_id" => 51
    "report_title" => "Term 3 - Termly Report Card"
    "has_u1" => "Yes"
    "has_u2" => "Yes"
    "has_u3" => "Yes"
    "has_u4" => "No"
    "has_u5" => "No"
    "do_update" => "No"
    "generate_marks" => "No"
    "generate_marks_for_classes" => "["165","164","163"]"
    "delete_marks_for_non_active" => "Yes"
    "submit_u1" => "Yes"
    "submit_u2" => "Yes"
    "submit_u3" => "Yes"
    "submit_u4" => "No"
    "submit_u5" => "No"
    "submit_project" => "No"
    "submit_exam" => "Yes"
    "reports_generate" => "Yes"
    "reports_include_u1" => "Yes"
    "reports_include_u2" => "Yes"
    "reports_include_u3" => "Yes"
    "reports_include_u4" => "Yes"
    "reports_include_u5" => "Yes"
    "reports_include_exam" => "Yes"
    "reports_include_project" => "Yes"
    "reports_template" => "1"
    "reports_who_fees_balance" => "Yes"
    "reports_display_report_to_parents" => "Yes"
    "hm_communication" => "<p><br></p>"
    "generate_class_teacher_comment" => "Yes"
    "generate_head_teacher_comment" => "No"
    "generate_positions" => "Yes"
    "display_positions" => "No"
    "bottom_message" => "<p><br></p>"
    "max_score_1" => "3.0"
    "max_score_2" => "3.0"
    "max_score_3" => "3.0"
    "max_score_4" => "3.0"
    "max_score_5" => "3.0"
    "max_project_score" => "10.0"
    "max_exam_score" => "80.0"
*/

$a4_width = 210;
?>
<table style="width: 100%!important;">
    <tr>
        <td class="text-center " style="width: {{ $a4_width / 6 }}mm!important ">
            <img style="width: 100%;" src="{{ public_path('storage/' . $r->ent->logo) }}">
        </td>
        <td style="width: 100%!important;" class="px-3">
            <p class="text-center text-uppercase" style="font-size: 18px"><b>{{ $r->ent->name }}</b></p>
            <p class="text-center mt-1" style="font-size: 13px">{{ $r->ent->p_o_box }}</p>
            <p class="text-center" style="font-size: 13px"><b>E-MAIL:</b> {{ $r->ent->email }}</p>
            <p class="text-center" style="font-size: 13px"><b>TELEPHONE:</b> {{ $r->ent->phone_number }},
                {{ $r->ent->phone_number_2 }}</p>
            <h2 class="text-center mt-2" style="font-weight: 800; font-size: 20px"><u>
                    {{ $r->secondary_termly_report_card->report_title }}
                </u></h2>
            {{-- END OF TERM III 2022 REPORT
                    CARD --}}
            <p class="mt-2 text-center text-sm small"><i>"{{ $r->ent->motto }}"</i></p>
        </td>
        <td class="text-center " style="width: {{ $a4_width / 6 }}mm!important ">
            <img style="width: 100%;" src="{{ public_path($r->owner->getAvatarPath()) }}">
        </td>
    </tr>
</table>
<div class="row px-3 mt-1">
    <hr style="border: solid {{ $r->ent->color }} 1px; " class="m-0 mt-1  mb-1">
</div>
<div class="row mb-1 mt-2 d-flex justify-content-between summary px-3"style="font-size: 14px">
    <span><b style="font-weight: 400;">NAME:</b> <span class="value">{{ $r->owner->name }}</span></span>
    {{--     <span><b>SEX:</b> <span class="value">{{ $r->owner->sex }}</span></span> --}}
    {{--     <span><b>AGE:</b> <span class="value">{{ '--' }}</span></span> --}}
    <span><b style="font-weight: 400;">LIN:</b> <span class="value">{{ $r->owner->id }}</span></span>
    <span><b style="font-weight: 400;">CLASS:</b> <span
            class="value">{{ $r->owner->current_class->name }}</span></span>
    <span><b style="font-weight: 400;">LESSONS PRESENT:</b> <span class="value">32</span></span>
    <span><b style="font-weight: 400;">LESSONS ABSENT:</b> <span class="value">10</span></span>
</div>
<table class="table table-bordered data mt-2">
    <thead class="text-center">
        <tr>
            <th style="width: 20%;">SUBJECT</th>
            <th style=" vertical-align: top;">TOTAL<br>(Out of 20)</th>
            <th style=" vertical-align: top;">EXAM<br>(Out of 80)</th>
            <th style=" vertical-align: top;">Total Score<br>(Out of 100)</th>
            <th style=" vertical-align: top;">Aggregates</th>
            <th style=" vertical-align: top;">TEACHER<br>INITIALS</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($r->get_report_card_items() as $item)
            <?php
            
            if ($item->subject == null) {
                //dd($item);
                //dd($item->secondary_subject_id);
                //echo 'Subject not found ' . $item->secondary_subject_id;
                continue;
            }
            
            ?>

            <tr>
                <th style="line-height: 14px;">{{ $item->subject->subject_name }}</th>
                <td style="text-align: center;">{{ $item->project_score ?? '-' }}</td>
                <td style="text-align: center;">{{ $item->exam_score ?? '-' }}</td>
                <td style="text-align: center;"><b>{{ $item->overall_score ?? '-' }}</b></td>
                <td style="text-align: center;"><b>{{ $item->grade_name ?? '-' }}</b></td>
                <td style="text-align: center;"><b>{{ $item->teacher ?? '-' }}</b></td>
            </tr>
        @endforeach
    </tbody>
</table>



<table class="table table-bordered data">
    <tr>
        <td style="width: 50%; vertical-align: top;">
            <table class="table table-bordered data mt-1" style="width: 100%; margin: 0;">
                <tr>
                    <th colspan="2"
                        style="font-size: 14px; text-align: center; background-color: #f8f9fa; padding: 8px;">
                        GRADE AND DESCRIPTORS
                    </th>
                </tr>
                <tr style="background-color: #f1f3f4;">
                    <td style="font-weight: bold; text-align: center; padding: 6px; width: 30%;">SCORE RANGE</td>
                    <td style="font-weight: bold; text-align: center; padding: 6px;">DESCRIPTOR (Meaning)</td>
                </tr>

                @foreach (GenericSkill::where([
        'enterprise_id' => $r->enterprise_id,
    ])->orderBy('min_score', 'asc')->get() as $x)
                    <tr>
                        <td style="text-align: center; padding: 4px; font-size: 12px;">
                            {{ $x->min_score . ' - ' . $x->max_score }}</td>
                        <td style="padding: 4px; font-size: 12px;">{{ $x->comments }}</td>
                    </tr>
                @endforeach

            </table>
        </td>
        <td style="width: 50%; vertical-align: top;">
            <table class="table table-bordered data mt-1" style="width: 100%; margin: 0;">
                <tr>
                    <th colspan="3"
                        style="font-size: 14px; text-align: center; background-color: #f8f9fa; padding: 8px;">
                        OVERALL AVERAGE LEVEL OF ACHIEVEMENT
                    </th>
                </tr>
                <tr style="background-color: #f1f3f4;">
                    <td style="font-weight: bold; text-align: center; padding: 6px;">POSITION</td>
                    <td style="font-weight: bold; text-align: center; padding: 6px;">AVERAGE SCORE</td>
                    <td style="font-weight: bold; text-align: center; padding: 6px;">DESCRIPTOR</td>
                </tr>
                <tr>
                    <td style="text-align: center; padding: 8px; font-size: 14px; font-weight: bold;">
                        {{ $r->position ?? '-' }}</td>
                    <td style="text-align: center; padding: 8px; font-size: 14px; font-weight: bold;">
                        {{ $r->average_score ?? '-' }}</td>
                    <td style="text-align: center; padding: 8px; font-size: 14px; font-weight: bold;">
                        {{ $r->grade ?? '-' }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>



<div class="p-0 mt-2 mb-2 class-teacher">
    <p style="font-size: 14px;"><b>CLASS TEACHER'S COMMENT:</b>
        <span class="comment">{{ $r->class_teacher_comment }}</span>
    </p>

    <p style="font-size: 14px;"><b>HEAD TEACHER'S COMMENT:</b>
        <span class="comment">{{ $r->head_teacher_comment }}</span>
    </p>
    <p style="font-size: 14px;"><b>GENERAL COMMUNICATION:</b>
        <span class="comment">{{ $r->secondary_termly_report_card->general_commnunication }}</span>
    </p>
</div>

<table class="table table-bordered data mt-2 mb-0">
    <tr>
        <td colspan="2" style="padding: 8px; font-size: 11px; line-height: 1.4;">
            <b>Descriptor</b> - Gives details on the extend to which the learner has attained the intended
            learning outcomes. <br>
            <b>Competency</b> - The overall expected capability of the learner at the end of topic after
            being exposed to a body of knowledge, Skills and Values.<br>
            <b>Indentifier</b> - A label of grade that distinguishes learners according to the achievement
            of the set of outcomes.<br>
            <b>U1: </b> Unit 1 Assessment, <b>U2: </b> Unit 2 Assessment, etc.
        </td>
    </tr>
</table>

<div class="row px-3 mt-0">
    <hr style="border: solid black .5px; " class="m-0 mt-0  mb-2">
    <div
        style="
        border: 1px solid #333;
        padding: 8px;
        font-size: 10!important;
        margin: 5px 0;
    ">
        <b style="font-weight: 600; font-size: 10px!important;">{!! $r->secondary_termly_report_card->bottom_message !!}</b>
    </div>
</div>
