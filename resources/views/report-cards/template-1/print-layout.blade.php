<?php
use App\Models\Utils;

$max_bot = 30;
$max_mot = 40;
$max_eot = 60;
$tr = isset($tr) ? $tr : null;
$ent = $r->ent;
$owner = $r->owner;

$tr = $r->get_theology_report();
$termly_report_card = $r->termly_report_card;
$theology_termly_report_card = null;

$stream_class = '.......';
if ($r->owner->stream != null) {
    if ($r->owner->stream->name != null) {
        $stream_class = $r->owner->stream->name;
    }
}

if ($tr == null) {
    $tr = $r->get_theology_report();
}
if ($tr != null) {
    $theology_termly_report_card = $tr->termly_report_card;
}

$bal = ((int) $r->owner->account->balance);
$bal_text = '';
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
<article class="ml-4 mr-4">
    <div class="row">
        <table class="w-100">
            <tr>
                <td width="15%">
                    <img style="width: 100%;" src="{{ public_path('storage/' . $ent->logo) }}">
                </td>
                <td style="width: 100%">
                    <p class="text-center p-0 m-0 text-uppercase"
                        style="font-size: 24px; font-weight: bold; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif!important; ">
                        {{ $r->ent->name }}</p>
                    <p class="text-center p font-serif  fs-3 m-0 p-0 mt-1 title-2" style="font-size: 16px;"><b
                            class="m-0 p-0">{{ $r->ent->address }}</b>
                    </p> 
                    <p class="text-center p font-serif mt-0 title-2 mb-1" style="font-size: 16px;"><b>EMAIL:</b> {{ $r->ent->email }}</p>
                    <p class="text-center p font-serif mt-0 mb-0 title-2" style="font-size: 16px;"><b>TELEPHONE NUMBER:</b>
                        {{ $ent->phone_number }} </p>
                    <p class="text-center p font-serif  fs-3 m-0 p-0 mt-1 mb-2" style="font-size: 2rem">
                        <u><b>{{ $r->termly_report_card->report_title }}</b></u>
                    </p>
                </td>
                <td>
                    <img style="height: 120px; width: 100px;" src="{{ public_path($r->owner->getAvatarPath()) }}">
                </td>
            </tr>
        </table>
    </div>

    <div class="row">
        <hr style="border: solid {{ $r->ent->color }} 1px; " class="m-0 mt-1  mb-0">
    </div>
    <div class="row mb-1 mt-2 d-flex justify-content-between summary"style="font-size: 14px; line-height: 14px;">
        <span><b>NAME:</b> <span class="value">{{ $r->owner->name }}</span></span>
        <span><b>GENDER:</b> <span class="value">{{ $r->owner->sex }}</span></span>
        <span><b>REG NO.:</b> <span class="value">{{ $r->owner->id }}</span></span>
    </div>

    {{-- START SECULAR SECTION --}}
    <p class="text-center text-uppercase mt-2" style="font-size: 18px; font-weight: bold;"><u>secular studies</u></p>
    <div class="row mt-2 mb-1" style="font-size: 12px">
        <span><b>CLASS:</b> <span class="value">&nbsp;{{ $r->academic_class->name }} - Blue&nbsp;</span></span>
        &nbsp;&nbsp;
        <span><b class="text-uppercase">Aggr:</b> <span
                class="value text-lowercase">&nbsp;{{ (int) $r->average_aggregates }}&nbsp;</span></span>
        &nbsp;&nbsp;
        <span><b class="text-uppercase">DIV:</b> <span
                class="value">&nbsp;{{ $r->grade }}&nbsp;</span></span>&nbsp;&nbsp;
        <span><b class="text-uppercase">Position:</b> <span class="value text-lowercase">
                {{ $numFormat->format($r->position) }} </span>&nbsp;&nbsp;
            <span><b class="text-uppercase">OUT OF :</b> <span class="value"> {{ $r->total_students }}</span> </span>
    </div>
    <div>
        <div class="row mt-2">
            <table class="table table-bordered marks-table p-0 m-0 w-100">
                <thead class="p-0 m-0 text-center" style="line-height: 12px;">
                    <th class="text-left p-1"><b>SUBJECTS</b></th>
                    @if ($termly_report_card->reports_include_bot == 'Yes')
                        <th class="p-1 m-0">
                            <b>B.O.T</b>
                            <small class="d-block">({{ $termly_report_card->bot_max }})</small>
                        </th>
                    @endif
                    @if ($termly_report_card->reports_include_mot == 'Yes')
                        <th class="p-1 m-0">
                            <b>M.O.T</b>
                            <small class="d-block">({{ $termly_report_card->mot_max }})</small>
                        </th>
                    @endif
                    @if ($termly_report_card->reports_include_eot == 'Yes')
                        <th class="p-1 m-0">
                            <b>E.O.T</b>
                            <small class="d-block">({{ $termly_report_card->eot_max }})</small>
                        </th>
                    @endif
                    <th class="p-1"><b>MARKS</b>
                        <small class="d-block">average - ({{ $max_mot }}%)</small>
                    </th>
                    <th class="p-1">AGGR</th>
                    <th class="remarks p-1"><b class="text-uppercase">Remarks</b></th>
                    <th class="remarks text-center p-1"><b class="text-uppercase">Initials</b></th>
                </thead>
                @php
                    $span = 0;
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
                        <th>{{ $v->subject->subject_name }}</th>
                        @if ($termly_report_card->reports_include_bot == 'Yes')
                            <td>{{ (int) $v->bot_score }}</td>
                        @endif
                        @if ($termly_report_card->reports_include_mot == 'Yes')
                            <td>{{ (int) $v->mot_score }}</td>
                        @endif
                        @if ($termly_report_card->reports_include_eot == 'Yes')
                            <td>{{ (int) $v->eot_score }}</td>
                        @endif
                        <td>{{ (int) $v->total_score_display }}</td>
                        <td>{{ $v->aggr_name }}</td>
                        <td class="remarks">{{ $v->remarks }}</td>
                        <td class="remarks text-center">{{ $v->initials }}</td>
                    </tr>
                @endforeach

                <tr class="marks">
                    <th><b>TOTAL</b></th>
                    <th colspan="{{ $span }}"></th>
                    <td><b>{{ $r->total_marks }}</b></td>
                    <td><b>{{ $r->total_aggregates }}</b></td>
                    <td colspan="2"></td>
                </tr>

            </table>
        </div>
        <div class="row">
            <div class="p-0 mt-0 mb-0 class-teacher mt-1">
                <b class="d-block">CLASS TEACHER'S COMMENT:</b>
                .........................................................................................................................................................................................
                <br> <b>CLASS TEACHER'S COMMENT:</b>
                <span class="comment">{{ Utils::getClassTeacherComment($r)['teacher'] }}</span>
            </div>
        </div>
    </div>
    {{-- END SECULAR SECTION --}}








    @if ($tr != null)
        <p class="text-center text-uppercase mt-4" style="font-size: 18px; font-weight: bold;"><u>theology studies</u>
        </p>
        <div class="row mt-2 mb-1" style="font-size: 12px">
            <span><b>CLASS:</b> <span class="value">&nbsp;{{ $tr->theology_class->name }} - Blue&nbsp;</span></span>
            &nbsp;&nbsp;
            <span><b class="text-uppercase">Aggr:</b> <span
                    class="value text-lowercase">&nbsp;{{ (int) $tr->average_aggregates }}&nbsp;</span></span>
            &nbsp;&nbsp;
            <span><b class="text-uppercase">DIV:</b> <span
                    class="value">&nbsp;{{ $tr->grade }}&nbsp;</span></span>&nbsp;&nbsp;
            <span><b class="text-uppercase">Position:</b> <span class="value text-lowercase">
                    {{ $numFormat->format($tr->position) }} </span>&nbsp;&nbsp;
                <span><b class="text-uppercase">OUT OF :</b> <span class="value"> {{ $tr->total_students }}</span>
                </span>
        </div>
        <div>
            <div class="row mt-2">
                <table class="table table-bordered marks-table p-0 m-0 w-100">
                    <thead class="p-0 m-0 text-center" style="line-height: 12px;">
                        <th class="text-left p-1"><b>SUBJECTS</b></th>
                        @if ($theology_termly_report_card->reports_include_bot == 'Yes')
                            <th class="p-1 m-0">
                                <b>B.O.T</b>
                                <small class="d-block">({{ $termly_report_card->bot_max }})</small>
                            </th>
                        @endif
                        @if ($theology_termly_report_card->reports_include_mot == 'Yes')
                            <th class="p-1 m-0">
                                <b>M.O.T</b>
                                <small class="d-block">({{ $termly_report_card->mot_max }})</small>
                            </th>
                        @endif
                        @if ($theology_termly_report_card->reports_include_eot == 'Yes')
                            <th class="p-1 m-0">
                                <b>E.O.T</b>
                                <small class="d-block">({{ $termly_report_card->eot_max }})</small>
                            </th>
                        @endif
                        <th class="p-1"><b>MARKS</b>
                            <small class="d-block">average - ({{ '100' }}%)</small>
                        </th>
                        <th class="p-1">AGGR</th>
                        <th class="remarks p-1"><b class="text-uppercase">Remarks</b></th>
                        <th class="remarks text-center p-1"><b class="text-uppercase">Initials</b></th>
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
                    @foreach ($theology_termly_report_card->get_student_marks($owner->id) as $v)
                        <tr class="marks">
                            <th>{{ $v->subject->name }}</th>
                            @if ($termly_report_card->reports_include_bot == 'Yes')
                                <td>{{ (int) $v->bot_score }}</td>
                            @endif
                            @if ($termly_report_card->reports_include_mot == 'Yes')
                                <td>{{ (int) $v->mot_score }}</td>
                            @endif
                            @if ($termly_report_card->reports_include_eot == 'Yes')
                                <td>{{ (int) $v->eot_score }}</td>
                            @endif
                            <td>{{ (int) $v->total_score_display }}</td>
                            <td>{{ $v->aggr_name }}</td>
                            <td class="remarks">{{ $v->remarks }}</td>
                            <td class="remarks text-center">{{ $v->initials }}</td>
                        </tr>
                    @endforeach

                    <tr class="marks">
                        <th><b>TOTAL</b></th>
                        <th colspan="{{ $span }}"></th>
                        <td><b>{{ $r->total_marks }}</b></td>
                        <td><b>{{ $r->total_aggregates }}</b></td>
                        <td colspan="2"></td>
                    </tr>

                </table>
            </div>
            <div class="row">
                <div class="p-0 mt-0 mb-0 class-teacher mt-1">
                    <b class="d-block">CLASS TEACHER'S COMMENT:</b>
                    .........................................................................................................................................................................................
                    <br> <b>CLASS TEACHER'S COMMENT:</b>
                    <span class="comment">{{ Utils::getClassTeacherComment($r)['teacher'] }}</span>
                </div>
            </div>
        </div>
    @endif

    <div class="row mt-2">
        <h2 class="p-1 text-center m-0 bg-black text-uppercase" style="font-size: 16px;">Aggregates
            Scale</h2>
        <table class="table table-bordered grade-table w-100">
            <tbody>
                <tr class="text-center">
                    <th class="text-left">Mark</th>
                    <th>00 - 39</th>
                    <th>40 - 44</th>
                    <th>45 - 49</th>
                    <th>50 - 54</th>
                    <th>55 - 59</th>
                    <th>60 - 69</th>
                    <th>70 - 79</th>
                    <th>80 - 89</th>
                    <th>90 - 100</th>
                </tr>
                <tr>
                    <th class="text-left">Aggregates</th>
                    <td class="bordered-table text-center value ">F9</td>
                    <td class="bordered-table text-center value">P8</td>
                    <td class="bordered-table text-center value">P7</td>
                    <td class="bordered-table text-center value">C6</td>
                    <td class="bordered-table text-center value">C5</td>
                    <td class="bordered-table text-center value">C4</td>
                    <td class="bordered-table text-center value">C3</td>
                    <td class="bordered-table text-center value">D2</td>
                    <td class="bordered-table text-center value">D1</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="row">
        <h2 class="p-1 text-center m-0  bg-black text-uppercase m-0" style="font-size: 16px;">Grading
            Scale
        </h2>
        <table class="table table-bordered grade-table">
            <tbody>
                <tr class="text-center">
                    <th class="text-left">Aggregates</th>
                    <th>4 - 12</th>
                    <th>13 - 23</th>
                    <th>24 - 29</th>
                    <th>30 - 33</th>
                    <th>34 - 36</th>
                </tr>
                <tr>
                    <th class="text-left">DIVISION</th>
                    <td class="bordered-table text-center value ">1</td>
                    <td class="bordered-table text-center value ">2</td>
                    <td class="bordered-table text-center value ">3</td>
                    <td class="bordered-table text-center value ">4</td>
                    <td class="bordered-table text-center value ">U</td>
                </tr>
            </tbody>
        </table>

        <table class="w-100">
            <tr>
                <td style="width: 80%">
                    {{--                     <b>HEAD TEACHER'S COMMENT:</b>
                    <span class="comment">{{ Utils::getClassTeacherComment($r)['hm'] }}</span> --}}
                    <div class="p-0 mt-0 mb-2 class-teacher">
                        <b>HEAD TEACHER'S COMMUNICATION:</b>
                        <span class="comment">Content coverage has been negatively affected this term because of the
                            several breaks we've had. But, teachers are working tirelessly to ensure that the work
                            designed for term 2 is completed.</span>
                    </div>
                </td>
                <td class=" pl-3 text-center">
                    <img width="70%" style=" " class="text-center "
                        src="{{ public_path('storage/images/kira-hm.png') }}">
                    <h2 class="text-center"
                        style="line-height: .6rem;font-size: 14px;   margin-bottom: 0px; padding:0px;">
                        HEAD
                        TEACHER</h2>
                </td>
            </tr>
        </table>
        <hr style="background-color:  {{ $r->ent->color }}; ">
        <div class=" mt-0 d-flex justify-content-between p-0 pt-0 " style="font-size: 14px;">
            <span><b>SCHOOL FEES BALANCE:</b> <span class="value" style="font-size: 14px!important;">
                    {{ $bal_text }}</span></span> |
            {{-- <span><b>NEXT TERM TUTION FEE:</b> <span class="value" style="font-size: 12px!important;">UGX
                18,000</span></span> --}}
            <span>&nbsp;&nbsp; <b>SCHOOL PAY CODE:</b> <span class="value"
                    style="font-size: 14px!important;">{{ $r->owner->school_pay_payment_code }}</span></span>
            |
            <span>&nbsp;&nbsp;<b>THIS TERM ENDS ON:</b> <span class="value"
                    style="font-size: 14px!important;">25<sup>th</sup> AUG,
                    2023</span></span>
        </div>


    </div>
</article>
