<?php
use App\Models\SecondaryCompetence;
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
            <img style="width: 100%;" src="{{ public_path('assets/mubahood.png') }}">
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
                <?php
                $competences = $item->items();
                if (count($competences) == 0) {
                    $c1 = new SecondaryCompetence();
                    $c1->topic = 'Topic';
                    $c1->description = 'Description';
                    $c1->competance = new \stdClass();
                    $c1->competance->score = 0;
                    $competences[] = $c1;
                    //c2
                    $c2 = new SecondaryCompetence();
                    $c2->topic = 'Topic';
                    $c2->description = 'Description';
                    $c2->competance = new \stdClass();
                    $c2->competance->score = 0;
                    $competences[] = $c2;
                    #c3
                    $c3 = new SecondaryCompetence();
                    $c3->topic = 'Topic';
                    $c3->description = 'Description';
                    $c3->competance = new \stdClass();
                    $c3->competance->score = 0;
                    $competences[] = $c3;
                }
                $first_competence = $competences[0];
                $last_competences = array_slice($competences, 1, count($competences));
                
                $seed = str_split('abcdefghijklmnopqrstuvwxyz' . 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'); // and any other characters
                shuffle($seed); // probably optional since array_is randomized; this may be redundant
                $rand = '';
                $isFirst = true; //
                foreach (array_rand($seed, 2) as $k) {
                    if (!$isFirst) {
                        $rand .= '.';
                    }
                    $isFirst = false;
                    $rand .= $seed[$k];
                }
                $rand = strtoupper($rand);
                ?>
                <th rowspan="{{ count($competences) }}">{{ $item->subject->subject_name }}</th>
                <td>
                    <b>{{ $first_competence->topic }}:</b> {{ $first_competence->description }}
                </td>
                <td class="text-center"><b>{{ $first_competence->competance->score }}</b></td>
                <td class="text-center" rowspan="{{ count($competences) }}">
                    <b><br>{{ $item->average_score }}</b>
                </td>
                <td rowspan="{{ count($competences) }}">{{ $item->generic_skills }}</td>
                <td rowspan="{{ count($competences) }}">{{ $item->remarks }}</td>
                <td rowspan="{{ count($competences) }}">{{ $rand }}</td>
            </tr>
            @foreach ($last_competences as $competence)
                <tr>
                    <td>
                        <b>{{ $competence->topic }}:</b> {{ $competence->description }}
                    </td>
                    <td class="text-center"><b>{{ $competence->competance->score }}</b></td>

                </tr>
            @endforeach
        @endforeach
    </tbody>
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
