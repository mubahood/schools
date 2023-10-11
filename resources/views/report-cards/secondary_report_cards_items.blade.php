<?php
/* 


    "id" => 1
    "created_at" => "2023-10-07 20:46:38"
    "updated_at" => "2023-10-11 19:23:27"
    "enterprise_id" => 12
    "academic_year_id" => 8
    "term_id" => 22
    "" => "End of Term 1 2023"
    "general_commnunication" => "Some test message."
    "do_update" => "No"
    "reports_template" => null
    "reports_display_report_to_parents" => null
    "use_background_image" => null
    "generate_class_teacher_comment" => "No"
    "generate_head_teacher_comment" => "No"
    "hm_communication" => null
    "classes" => "["75"]"
    "background_image" => null
    "bottom_message" => null
    "max_score" => 3.0
    ================================
    "id" => 12
    "created_at" => "2023-03-01 00:26:03"
    "updated_at" => "2023-03-01 00:33:47"
    "name" => "St. Charles Vocational Secondary School"
    "short_name" => "scvss"
    "details" => "St. Charles Vocational Secondary School"
    "logo" => "images/7775c5a1b4d3d42386ca947b2844a089.jpeg"
    "" => "256774802614"
    "email" => "stcharlesvocationalkasanga@gmail.com"
    "address" => "Kasese district, Bwera sub-county, Kasanga, Kasanga Roman Catholic Parish"
    "expiry" => "2024-01-02"
    "administrator_id" => 3757
    "subdomain" => "scvss"
    "color" => "#26386E"
    "welcome_message" => "<p>St. Charles Vocational Secondary School</p>"
    "type" => "Advanced"
    "phone_number_2" => null
    "hm_signature" => null
    "dos_signature" => null
    "bursar_signature" => null
    "dp_year" => 8
    "school_pay_code" => null
    "school_pay_password" => null
    "has_theology" => "No"
    "dp_term_id" => 22
    "motto" => null
    "website" => null
    "hm_name" => null
    "wallet_balance" => 0
    "can_send_messages" => "No"
    "has_valid_lisence" => "Yes"
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
            <th style="width: 15%;">SUBJECT</th>
            <th style="width: 65%;">TOPIC & COMPETENCE</th>
            <th style="width: 5px;" class="text-center">SCORE</th>
            <th>AVERAGE<br>SCORE</th>
            <th>GENERIC SKILL</th>
            <th>GENERAL<br>REMARKS</th>
            <th>TEAHCER<br>INITIALS</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($r->items as $item)
            <?php
            
            if ($item->subject == null) {
                //dd($item);
                dd($item->secondary_subject_id);
                echo 'Subject not found ' . $item->secondary_subject_id;
                continue;
            }
            
            ?>

            <tr>
                <?php
                $competences = $item->items();
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
