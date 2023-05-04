<?php
use App\Models\Utils;

$max_bot = 30;
$max_mot = 40;
$max_eot = 60;
$tr = isset($tr) ? $tr : null;

$stream_class = '.......';
if ($r->owner->stream != null) {
    if ($r->owner->stream->name != null) {
        $stream_class = $r->owner->stream->name;
    }
}

if ($tr == null) {
    $tr = $r->get_theology_report();
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
                <td width="8%" class=" ">
                    <img style="width: 100%;" src="{{ public_path('storage/' . $r->ent->logo) }}">
                </td>
                <td>
                    <h1 class="text-center h3 p-0 m-0 text-uppercase" style="font-size: 24px;">{{ $r->ent->name }}</h1>
                    <p class="text-center p font-serif  fs-3 m-0 p-0 mt-1 title-2"><b
                            class="m-0 p-0">{{ $r->ent->address }}</b>
                    </p>
                    <p class="text-center p font-serif mt-0 mb-0 title-2"><b>WEBSITE:</b> www.kirajuniorschool.ac.ug</p>
                    <p class="text-center p font-serif mt-0 title-2 mb-1"><b>EMAIL:</b> {{ $r->ent->email }}</p>
                    <p class="text-center p font-serif  fs-3 m-0 p-0 mt-1 mb-2" style="font-size: 1.3rem">
                        <u><b>{{ $r->termly_report_card->report_title }}</b></u>
                    </p>
                </td>
                <td width="8%" class=" ">
                    <img style="width: 100%;" src="{{ public_path($r->owner->getAvatarPath()) }}">
                </td>
            </tr>
        </table>
    </div>

    <div class="row">
        <hr style="border: solid {{ $r->ent->color }} 1px; " class="m-0 mt-1  mb-0">
    </div>
    <div class="row mb-1 d-flex justify-content-between summary"style="font-size: 14px">
        <span><b>NAME:</b> <span class="value">{{ $r->owner->name }}</span></span>
        <span><b>GENDER:</b> <span class="value">{{ $r->owner->sex }}</span></span>
        <span><b>AGE:</b> <span class="value">{{ '--' }}</span></span>
        <span><b>REG NO.:</b> <span class="value">{{ $r->owner->id }}</span></span>
    </div>
    <div class="row">
        <table class=" ">

            <tr>
                <td style="width: 40%" class="pr-3">

                    <h2 class="text-center text-uppercase h2" style="font-size: 18px"><u>secular studies</u></h2>
                    <div class="row mt-1 pb-2 d-flex justify-content-between pl-3 pr-3 summary" style="font-size: 12px">
                        <span><b>CLASS:</b> <span class="value">{{ $r->academic_class->name }}
                                {{ $stream_class }}</span></span>
                        {{-- <span><b class="text-uppercase">Aggre:</b> <span class="value">18</span></span> --}}
                        <span><b class="text-uppercase">Aggr:</b> <span
                                class="value text-lowercase">{{ (int) $r->average_aggregates }}</span></span>

                        <span><b class="text-uppercase">DIV:</b> <span class="value">{{ $r->grade }}</span></span>
                        <span><b class="text-uppercase">Position:</b> <span class="value text-lowercase">
                                {{-- {{ $numFormat->format($r->position) }} --}}
                                .........</span>
                            <span><b class="text-uppercase">OUT OF :</b> <span class="value">
                                    {{-- {{ $r->total_students }} --}}
                                    .........
                                </span>
                    </div>
                    <table class="table table-bordered marks-table p-0 m-0 w-100">
                        <thead class="p-0 m-0 text-center">
                            <th class="text-left pl-2">SUBJECTS</th>
                            @if ($r->termly_report_card->has_beginning_term)
                                <th>B.O.T <br> ({{ $max_bot }})</th>
                            @endif
                            @if ($r->termly_report_card->has_mid_term)
                                {{-- <th>M.O.T <br> ({{ $max_mot }})</th> --}}
                            @endif
                            @if ($r->termly_report_card->has_end_term)
                                {{--   <th>E.O.T <br> ({{ $max_eot }})</th> --}}
                            @endif
                            <th>Marks <br> (100%)</th>
                            <th>Aggr</th>
                            <th class="remarks">Remarks</th>
                            <th class="remarks text-center">Initials</th>
                        </thead>
                        <?php $done = []; ?>
                        @foreach ($r->items as $v)
                            <?php
                            if (in_array($v->main_course_id, $done)) {
                                continue;
                            }
                            if ( ((int)($v->total)) < 1 ) {
                                continue;
                            }
                            $done[] = $v->main_course_id; ?>
                            <tr class="marks">
                                <th>{{ $v->subject->subject_name }}</th>
                                @if ($r->termly_report_card->has_beginning_term)
                                    <td>{{ $v->bot_mark }}</td>
                                @endif

                                @if ($r->termly_report_card->has_mid_term)
                                    {{-- <td>{{ $v->mot_mark }}</td> --}}
                                @endif
                                @if ($r->termly_report_card->has_end_term)
                                    {{--   <td>{{ $v->eot_mark }}</td> --}}
                                @endif
                                <td>{{ $v->total }}</td>
                                <td>{{ $v->grade_name }}</td>
                                <td class="remarks">{{ $v->remarks }}</td>
                                <td class="remarks text-center">{{ $v->initials }}</td>
                            </tr>
                        @endforeach
                        <tr class="marks">
                            <th><b>TOTAL</b></th>
                            @if ($r->termly_report_card->has_beginning_term)
                                {{--           <td></td> --}}
                            @endif
                            @if ($r->termly_report_card->has_mid_term)
                                {{--   <td></td> --}}
                            @endif
                            @if ($r->termly_report_card->has_end_term)
                                {{--           <td></td> --}}
                            @endif
                            <td><b>{{ $r->total_marks }}</b></td>
                            <td><b>{{ $r->total_aggregates }}</b></td>
                            <td colspan="2"></td>
                        </tr>
                    </table>
                    <div class="p-0 mt-0 mb-0 class-teacher">
                        <b class="d-block">CLASS TEACHER'S COMMENT:</b>
                        .............................................................................................................................................
                        <br>
                        .............................................................................................................................................
                        <br>
                         

                 {{--        <b>CLASS TEACHER'S COMMENT:</b> 
                        <span class="comment">{{ Utils::getClassTeacherComment($r)['teacher'] }}</span> --}}
                        {{-- <span class="comment">{{  }}</span> --}}
                    </div>
                </td>
                <td style="width: 40%" class="pl-2">


                    @if ($tr != null)
                        <h2 class="text-center text-uppercase mt-0 pt-0 h2" style="font-size: 16px"><u>Theology
                                Studies</u></h2>
                        <div class="row mt-2 mb-2 d-flex justify-content-between pl-3 pr-3 summary"
                            style="font-size: 13px">
                            <span><b>CLASS:</b> <span class="value">{{ $tr->theology_class->name }}</span></span>
                            {{-- <span><b class="text-uppercase">Aggre:</b> <span class="value">18</span></span> --}}
                            <span><b class="text-uppercase">DIV:</b> <span
                                    class="value">{{ $tr->grade }}</span></span>
                            <span><b class="text-uppercase">Position in class:</b> <span class="value text-lowercase">
                                    {{-- {{ $numFormat->format($tr->position) }} --}}
                                    ..........
                                </span></span>
                                OUT OF.........

                        </div>
                    @endif


                    @if ($tr != null)
                        <table class="table table-bordered marks-table p-0 m-0 w-100">
                            <thead class="p-0 m-0 text-center">
                                <th class="text-left pl-2">SUBJECTS</th>
                                @if ($tr->termly_report_card->has_beginning_term)
                                    <th>B.O.T <br> ({{ $max_bot }})</th>
                                @endif
                                @if ($tr->termly_report_card->has_mid_term)
                                    {{--  <th>M.O.T <br> ({{ $max_mot }})</th> --}}
                                @endif
                                @if ($tr->termly_report_card->has_end_term)
                                    {{--   <th>E.O.T <br> ({{ $max_eot }})</th> --}}
                                @endif
                                <th>MARKS <br> (100%)</th>
                                <th>Aggr</th>
                                <th class="remarks">Remarks</th>
                                <th class="remarks text-center">Initials</th>
                            </thead>
                            @foreach ($tr->items as $v)
                                <tr class="marks">
                                    <th>{{ $v->subject->theology_course->name }}</th>
                                    @if ($r->termly_report_card->has_beginning_term)
                                        <td>{{ $v->bot_mark }}</td>
                                    @endif

                                    @if ($r->termly_report_card->has_mid_term)
                                        {{--  <td>{{ $v->mot_mark }}</td> --}}
                                    @endif
                                    @if ($r->termly_report_card->has_end_term)
                                        {{--   <td>{{ $v->eot_mark }}</td> --}}
                                    @endif
                                    <td>{{ $v->total }}</td>
                                    <td>{{ $v->grade_name }}</td>
                                    <td class="remarks">{{ $v->remarks }}</td>
                                    <td class="remarks text-center">{{ $v->initials }}</td>
                                </tr>
                            @endforeach
                            <tr class="marks">
                                <th><b>TOTAL</b></th>
                                @if ($tr->termly_report_card->has_beginning_term)
                                    {{-- <td></td> --}}
                                @endif
                                @if ($tr->termly_report_card->has_mid_term)
                                    {{-- <td></td> --}}
                                @endif
                                @if ($tr->termly_report_card->has_end_term)
                                    {{--      <td></td> --}}
                                @endif
                                <td><b>{{ $tr->total_marks }}</b></td>
                                <td><b>{{ $tr->total_aggregates }}</b></td>
                                <td colspan="2"> </td>
                            </tr>
                        </table>

                    @endif

                    @if ($tr != null)
                        <div class="p-0 mt-1 mb-0 class-teacher">

                            <b class="d-block">CLASS TEACHER'S COMMENT:</b>
                            ...............................................................................................................................
                            <br>
                            ...............................................................................................................................
                            <br>
                            .............................................................................................................................
                            <br>
                         
             {{--                
                            <b>CLASS TEACHER'S COMMENT:</b>
                            <span class="comment">{{ Utils::getClassTeacherComment($r)['theo'] }}</span> --}}
                        </div>
                    @endif
                </td>
            </tr>
        </table>

        <table class=" w-100   ">
            <tbody>
                <tr>
                    <td style="width: 70%;">
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
                    </td>
                    <td style="width: 30%;">
                        <h2 class="p-1 text-center m-0  bg-black text-uppercase" style="font-size: 16px;">Grading Scale
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

                    </td>

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
                        <span class="comment">At Kira Junior School, we believe that every child is a genius who only needs a
                            conducive environment to blossom. Please note that next term we have to prepare for the
                            sports gala and the election of prefects InSha'Allah.</span>
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
        <div class=" mt-0 d-flex justify-content-between p-0 pt-0 " style="font-size: 16px;">
            <span><b>SCHOOL FEES BALANCE:</b> <span class="value" style="font-size: 16px!important;">
                    ........................................{{-- {{ $bal_text }}</span></span> | --}}
                    {{-- <span><b>NEXT TERM TUTION FEE:</b> <span class="value" style="font-size: 12px!important;">UGX
                18,000</span></span> --}}
                    <span>&nbsp;&nbsp; <b>SCHOOL PAY CODE:</b> <span class="value"
                            style="font-size: 16px!important;">{{ $r->owner->school_pay_payment_code }}</span></span> |
                    <span>&nbsp;&nbsp;<b>NEXT TERM BEGINS ON:</b> <span class="value"
                            style="font-size: 16px!important;">29<sup>th</sup> MAY,
                            2023</span></span>
        </div>


    </div>
</article>
