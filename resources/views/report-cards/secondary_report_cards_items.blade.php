<?php

?>
<table>
    <tr>
        <td class="text-center ">
            <img style="width: 100%;" src="{{ public_path('assets/logo.jpeg') }}">
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
            <img style="width: 100%;" src="{{ public_path('assets/logo.jpeg') }}">
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
                continue;
            }
            ?>
            {{-- 
        "created_at" => "2023-03-16 09:17:34"
    "updated_at" => "2023-03-16 09:17:34"
    "enterprise_id" => null
    "academic_year_id" => 6
    "secondary_subject_id" => 390
    "secondary_report_card_id" => 100
    "average_score" => 0.0
    "generic_skills" => null
    "remarks" => null
    --}}
            <tr>
                <th rowspan="3">{{ $item->subject->subject_name }}</th>
                <?php
                dd($item->subject->subject_name);
                ?>
                <td>
                    <b>PERSONAL LIFE AND FAMILY:</b>
                    The Learner narrates experiences,
                    reads and responds to stories about personal and family life.
                </td>
                <td class="text-center"><b>40</b></td>
                <td rowspan="3">Muhindo is good at speaking English</td>
                <td rowspan="3">Good</td>
                <td rowspan="3">M.J</td>
            </tr>
            <tr>
                <td>
                    <b>PERSONAL LIFE AND FAMILY:</b>
                    The Learner narrates experiences,
                    reads and responds to stories about personal and family life.
                </td>
                <td class="text-center"><b>40</b></td>

            </tr>
            <tr>
                <td>
                    <b>PERSONAL LIFE AND FAMILY:</b>
                    The Learner narrates experiences,
                    reads and responds to stories about personal and family life.
                </td>
                <td class="text-center"><b>40</b></td>
            </tr>
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
