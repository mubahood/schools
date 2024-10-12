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

?>
<table>
    <tr>
        <td class="text-center ">
            <img style="width: 100%;" src="{{ public_path('storage/' . $r->ent->logo) }}">
        </td>
        <td style="width: 72%;" class="px-3">
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
        <td class="text-center">
            <br>
            {{-- <img style="width: 100%;" src="{{ public_path('assets/mubahood.png') }}"> --}}
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
            <th style="width: 20%;" rowspan="2">SUBJECT</th>
            <th colspan="{{ $NUMBER_OF_UNITS }}">Scores from ADIs</th>
            <th rowspan="2">TOTAL<br>PTs</th>
            <th rowspan="2" style=" vertical-align: top;">AVG<br>Score</th>
            <th rowspan="2" style=" vertical-align: top;">TOTAL<br>Out of 10</th>
            <th rowspan="2" style=" vertical-align: top;">DESCRIPTOR</th>
            <th rowspan="2" style=" vertical-align: top;">PROJECT<br>Out of 10</th>
            <th rowspan="2" style=" vertical-align: top;">TOTAL<br>Out of 20</th>
            <th rowspan="2" style=" vertical-align: top;">EXAM<br>Out of 80</th>
            <th rowspan="2" style=" vertical-align: top;">SCORE<br>Out of 100%</th>
            <th rowspan="2" style=" vertical-align: top;">SCORE<br>GRADE</th>
            <th rowspan="2" style=" vertical-align: top;">TEAHCER<br>INITIALS</th>
        </tr>
        <tr>

            @if ($termly_report->has_u1 == 'Yes')
                <th>U1</th>
            @endif
            @if ($termly_report->has_u2 == 'Yes')
                <th>U2</th>
            @endif
            @if ($termly_report->has_u3 == 'Yes')
                <th>U3</th>
            @endif
            @if ($termly_report->has_u4 == 'Yes')
                <th>U4</th>
            @endif
            @if ($termly_report->has_u5 == 'Yes')
                <th>U5</th>
            @endif
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
            /*
    "id" => 6339
    "created_at" => "2024-10-12 14:47:36"
    "updated_at" => "2024-10-12 14:47:36"
    "enterprise_id" => 19
    "academic_year_id" => 17
    "secondary_subject_id" => 204
    "secondary_report_card_id" => 1
    "average_score" => 0.0
    "generic_skills" => null
    "remarks" => null
    "teacher" => "13302"
    "administrator_id" => 14258
    "academic_class_id" => 165
    "term_id" => 51
    "academic_class_sctream_id" => 149
    "score_1" => null
    "score_2" => null
    "score_3" => null
    "score_4" => null
    "score_5" => null
    "tot_units_score" => "0.0"
    "out_of_10" => null
    "descriptor" => null
    "project_score" => null
    "out_of_20" => null
    "exam_score" => null
    "overall_score" => null
    "grade_value" => null
    "grade_name" => null
    "score_1_submitted" => "No"
    "score_2_submitted" => "No"
    "score_3_submitted" => "No"
    "score_4_submitted" => "No"
    "score_5_submitted" => "No"
    "project_score_submitted" => "No"
    "exam_score_submitted" => "No"
    "termly_examination_id" => 1
*/
            ?>

            <tr>
                <th style="line-height: 14px;">{{ $item->subject->subject_name }}</th>
                @if ($termly_report->has_u1 == 'Yes')
                    <td style="text-align: center;">{{ $item->score_1 ?? '-' }}</td>
                @endif
                @if ($termly_report->has_u2 == 'Yes')
                    <td style="text-align: center;">{{ $item->score_2 ?? '-' }}</td>
                @endif
                @if ($termly_report->has_u3 == 'Yes')
                    <td style="text-align: center;">{{ $item->score_3 ?? '-' }}</td>
                @endif
                @if ($termly_report->has_u4 == 'Yes')
                    <td style="text-align: center;">{{ $item->score_4 ?? '-' }}</td>
                @endif
                @if ($termly_report->has_u5 == 'Yes')
                    <td style="text-align: center;">{{ $item->score_5 ?? '-' }}</td>
                @endif
                @if ($termly_report->has_u5 == 'Yes')
                    <td style="text-align: center;">{{ $item->score_5 ?? '-' }}</td>
                @endif
                <td style="text-align: center;">{{ $item->tot_units_score ?? '-' }}</td>
                <td style="text-align: center;">{{ $item->average_score ?? '-' }}</td>
                <td style="text-align: center;"><b>{{ $item->out_of_10 ?? '-' }}</b></td>
                <td style="text-align: center;">{{ $item->descriptor ?? '-' }}</td>
                <td style="text-align: center;">{{ $item->project_score ?? '-' }}</td>
                <td style="text-align: center;"><b>{{ $item->out_of_20 ?? '-' }}</b></td>
                <td style="text-align: center;">{{ $item->exam_score ?? '-' }}</td>
                <td style="text-align: center;"><b>{{ $item->overall_score ?? '-' }}</b></td>
                <td style="text-align: center;"><b>{{ $item->grade_name ?? '-' }}</b></td>
                <td style="text-align: center;"><b>{{ $item->teacher ?? '-' }}</b></td>
                {{-- 
                
id
created_at
updated_at
enterprise_id
academic_year_id
secondary_subject_id
secondary_report_card_id
average_score
generic_skills
remarks

administrator_id
academic_class_id
term_id
academic_class_sctream_id
score_1
score_2
score_3
score_4
score_5
tot_units_score
out_of_10
descriptor
project_score
out_of_20
exam_score
overall_score
grade_value
grade_name
score_1_submitted
score_2_submitted
score_3_submitted
score_4_submitted
score_5_submitted
project_score_submitted
exam_score_submitted
termly_examination_id

Edit Edit

                --}}
            </tr>
        @endforeach
    </tbody>
</table>

<table class="table table-bordered data mt-1">
    <tr>
        <th rowspan="2">
            OVERALL AVERAGE LEVEL OF ACHIEVEMENT
        </th>
        <td class="text-center">AVERAGE SCORE</td>
        <td class="text-center">DESCRIPTOR</td>
    </tr>
    <tr>
        <td class="text-center">{{ 13 }}</td>
        <td class="text-center">{{ 13 }}</td>
    </tr>
</table>

<table class="table table-bordered data mt-1">
    <tr>
        <th colspan="2" style="font-size: 14px;">
            KEY TERMS USED
        </th>
    </tr>
    <tr>
        <td>SCORE RANGE</td>
        <td>DESCRIPTOR (Meaning)</td>
    </tr>

    @foreach (GenericSkill::where([
        'enterprise_id' => $r->enterprise_id,
    ])->orderBy('min_score', 'asc')->get() as $x)
        <tr>
            <td style="width: 15%;">{{ $x->min_score . ' - ' . $x->max_score }}</td>
            <td>{{ $x->comments }}</td>
        </tr>
    @endforeach
    <tr>
        <td colspan="2">
            <b>Descriptor</b> - Gives details on the extend to which the learner has attained the intended learning
            outcomes. <br>
            <b>Competency</b> - The overall expected capability of the learner at the end of topic after being
            exposed to
            a body of knowledge, Skills and Values.<br>
            <b>Indentifier</b> - A label of grade that distinguishes learners according to the achievement of the set
            of
            outcomes.
            <br>
            <b>U1: </b> Unit 1 Assesment, <b>U2: </b> Unit 2 Assesment, etc.
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
<div class="row px-3 mt-1">
    <hr style="border: solid black .5px; " class="m-0 mt-1  mb-2">
    <b>{!! $r->secondary_termly_report_card->bottom_message !!}</b>
</div>
