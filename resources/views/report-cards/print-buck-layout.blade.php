<?php
use App\Models\Utils;

$max_bot = 30;
$max_mot = 40;
$max_eot = 60;
$tr = isset($tr) ? $tr : null;
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

$school_name = 'KIIRA JUNIOR PRIMARY SCHOOL';
$school_address = 'Bwera Kasese Uganda';
$school_tel = '+256783204665';
$report_title = 'END OF TERM III REPORT CARD  2019';
$school_email = 'admin@kjs.com';
?><article>

    <div class="row">
        <div style="width: 18%;">
            <img class="img-fluid" src="{{ url('assets/bjs.png') }}">
        </div>

        <div class="col p-0">

            <h1 class="text-center h3 p-0 m-0 text-uppercase">{{ $r->ent->name }}</h1>
            <p class="text-center p font-serif  fs-3 m-0 p-0 mt-2 title-2"><b class="m-0 p-0">{{ $r->ent->address }}</b>
            </p>
            <p class="text-center p font-serif mt-0 mb-0 title-4"><b>WEBSITE:</b> www.buckinghamjuniorschool.com</p>
            <p class="text-center p font-serif mt-0 title-2 mb-2"><b>EMAIL:</b> buckinghamschs@gmail.com</p>
            <p class="text-center p font-serif mt-0 title-2 mb-4"><b> Tel:</b>+256702440940, +256782 440950</p>
            <p class="text-center p font-serif  fs-3 m-0 p-0 "><u><b>{{ $r->termly_report_card->report_title }}</b></u>
            </p>

        </div>
        {{-- 19.9 --}}



    </div>

    <hr style="border: solid green 1px; " class="m-0 mt-4  mb-3">

    <div class="container mb-3">
        <div class="row mb-1 d-flex justify-content-between summary"style="font-size: 14px">
            <span><b>NAME:</b> <span class="value">{{ $r->owner->name }}</span></span>
            <span><b>GENDER:</b> <span class="value">{{ $r->owner->sex }}</span></span>
            <span><b>AGE:</b> <span class="value">{{ '--' }}</span></span>
            <span><b>REG NO.:</b> <span class="value">{{ $r->owner->id }}</span></span>

        </div>


        <div class="row border-bottom border-dark mt-2 d-flex justify-content-between  summary pb-3"
            style="font-size: 15px">
            <span><b>CLASS:</b> <span class="value">{{ $r->academic_class->name }}</span></span>
            {{-- <span><b class="text-uppercase">Aggre:</b> <span class="value">18</span></span> --}}
            <span><b class="text-uppercase">Aggregates:</b> <span
                    class="value text-lowercase">{{ $r->average_aggregates }}</span></span>

            <span><b class="text-uppercase">DIV:</b> <span class="value">{{ $r->grade }}</span></span>
            <span><b class="text-uppercase">Position:</b> <span
                    class="value text-lowercase">{{ $numFormat->format($r->position) }}</span></span>

            <span><b class="text-uppercase">OUT OF :</b> <span class="value">{{ $r->total_students }}
        </div>

    </div>


    <div class="row ">
        <div class="col-12 ">
            <table class="table table-bordered marks-table p-0 m-0">
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
                @foreach ($r->items as $v)
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
                    <td colspan="3"> </td>
                </tr>

            </table>

            <div class="p-0 mt-3 mb-2 class-teacher">
                <b>CLASS TEACHER'S COMMENT:</b>
                <span class="comment">{{ Utils::getClassTeacherComment($r)['teacher'] }}</span>
                {{-- <span class="comment">{{  }}</span> --}}
            </div>
        </div>
    </div>


    <div class="container   ">


        <div class="row">
            <div class="col-12">
                <div class="row mt-3 p-0 -info ">
                    <div class="col-12  text-white scale-title" style="background-color: black">
                        <h2 class="p-1 text-center m-0 " style="font-size: 12px;">Aggregates Scale</h2>
                    </div>
                    <div class="col-12 p-0">
                        <table class="table table-bordered grade-table">
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
                </div>
            </div>
            <div class="col-12">
                <div class="row mt-1 p-0  ">
                    <div class="col-12  text-white scale-title" style="background-color: black">
                        <h2 class="p-1 text-center m-0 " style="font-size: 12px;">Grading Scale</h2>
                    </div>
                    <div class="col-12 p-0">
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
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="row">
                    <div class="col-12 p-0">
                        <div class="p-0 mt-0 mb-2 class-teacher">
                            <b>HEAD TEACHER'S COMMENT:</b>
                            <span class="comment">{{ Utils::getClassTeacherComment($r)['hm'] }}</span>
                        </div>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-12 p-0">
                        <div class="p-0 mt-0 mb-2 class-teacher">
                            <b>HEAD TEACHER'S COMMUNICATION:</b>
                            <span class="comment">We thank you for all the support you have accorded us since you joined
                                Kira Junior School.
                                We remain open to positive feedback, which we believe helps us to improve the services
                                we provide to our children.
                                <br>
                                <br>
                                <br>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-2 d-flex justify-content-between p-0 border-top pt-2 border-primary"
            style="font-size: 12px;">
            <span><b>SCHOOL FEES BALANCE:</b> <span class="value" style="font-size: 12px!important;">
                    {{ $bal_text }}</span></span>
            {{-- <span><b>NEXT TERM TUTION FEE:</b> <span class="value" style="font-size: 12px!important;">UGX
                    18,000</span></span> --}}
            <span><b>SCHOOL PAY CODE:</b> <span class="value"
                    style="font-size: 12px!important;">{{ $r->owner->school_pay_payment_code }}</span></span>
            <span><b>TERM ENDS ON:</b> <span class="value" style="font-size: 12px!important;">5<sup>th</sup> MAY,
                    2023</span></span>
        </div>

    </div>

</article>
