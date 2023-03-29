<?php

?>
<table>
    <tr>
        <td class="text-center ">
            <img style="width: 100%;" src="{{ public_path('assets/charles.jpeg') }}">
        </td>
        <td style="width: 72%;" class="px-3">
            <p class="text-center" style="font-size: 22px"><b>ST. CHARLES VOCATIONAL S.S KASANGA</b></p>
            <p class="text-center mt-1" style="font-size: 13px">P.O. Box 513, ENTEBBE, UGANDA</p>
            <p class="text-center" style="font-size: 13px"><b>E-MAIL:</b> animalhealth@agriculture.co.ug</p>
            <p class="text-center" style="font-size: 13px"><b>TELEPHONE:</b> +256 0414 320 627, 320166, 320376</p>
            <h2 class="text-center mt-2" style="font-weight: 800; font-size: 20px"><u>END OF TERM III 2022 REPORT
                    CARD</u></h2>
            {{--          <p class="mt-2 text-center text-sm small"><i>"For Broader Minds"</i></p> --}}
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
    <thead>
        <tr>
            <th style="width: 15%;">SUBJECT</th>
            <th style="width: 65%;">TOPIC & COPETENCT</th>
            <th style="width: 5px;" class="text-center">SC<br>ORE</th>
            <th>GENERIC SKILLS</th>
            <th>REMARKS</th>
            <th>INITIALS</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($r->items as $item)
            <?php
            
            if ($item->subject == null) {
                dd($item);
                echo ('Subject not found '.$item->secondary_subject_id);
                continue;
            }
            ?>

            <tr>
                <?php
                $competences = $item->items();
                $first_competence = $competences[0];
                $last_competences = array_slice($competences, 1, count($competences));
                /*

                  "id" => 1
    "created_at" => "2023-03-17 22:34:11"
    "updated_at" => "2023-03-17 22:34:11"
    "enterprise_id" => 11
    "academic_year_id" => 6
    "secondary_subject_id" => 57
    "secondary_report_card_id" => 1
    "average_score" => 0.0
    "generic_skills" => null
    "remarks" => null
    "teacher" => "-"


 "id" => 1
    "created_at" => "2023-03-17 23:09:43"
    "updated_at" => "2023-03-17 23:10:57"
    "enterprise_id" => 11
    "academic_class_id" => 48
    "parent_course_id" => 1
    "secondary_subject_id" => 57
    "term_id" => 16
    "academic_year_id" => 6 
    "submitted" => 1
    "activity_id" => 197
    "administrator_id" => 3759

    
    "id" => 197
    "created_at" => "2023-03-17 23:09:43"
    "updated_at" => "2023-03-17 23:09:43"
    "enterprise_id" => 11
    "academic_year_id" => 6
    "academic_class_id" => 48
    "parent_course_id" => 1
    "term_id" => 16
    "class_type" => "S.1"
    "theme" => "English Theme"
    "topic" => "English Topic 0"
    "description" => "Some details about this activity go here. Some details about this activity go here. Some details about this activity go here. Some details about this activity g ▶"
    "max_score" => 3
    "subject_id" => 57
    "competance" => App\Models\SecondaryCompetence {#2452 ▶}

*/
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
                <td rowspan="{{ count($competences) }}">{{ $item->generic_skills }}</td>
                <td rowspan="{{ count($competences) }}">{{ $item->remarks }}</td>
                <td rowspan="{{ count($competences) }}">{{ $rand }}</td>
            </tr>
            @foreach ($last_competences as $competence)
                <tr>
                    <td>
                        <b>{{ $first_competence->topic }}:</b> {{ $first_competence->description }}
                    </td>
                    <td class="text-center"><b>{{ $first_competence->competance->score }}</b></td>

                </tr>
            @endforeach
        @endforeach
    </tbody>
</table>

<div class="p-0 mt-2 mb-2 class-teacher">
    <p style="font-size: 14px;"><b>CLASS TEACHER'S COMMENT:</b>
        <span class="comment">Always consult your teachers in class to better aim higher than this.</span>
    </p>

    <p style="font-size: 14px;"><b>HEAD TEACHER'S COMMENT:</b>
        <span class="comment">Always consult your teachers in class to better aim higher than this.</span>
    </p>
    <p style="font-size: 14px;"><b>GENERAL COMMUNICATION:</b>
        <span class="comment">Assalam Alaikum Warahmatullah Wabarakatuhu. We are informing our beloved parents
            that.</span>
    </p>
</div>
<div class="row px-3 mt-1">
    <hr style="border: solid black .5px; " class="m-0 mt-1  mb-2">
    <span><b>TERM ENDS ON:</b> <span class="value" style="font-size: 12px!important;">5<sup>th</sup> MAY,
            2023</span></span>
</div>
