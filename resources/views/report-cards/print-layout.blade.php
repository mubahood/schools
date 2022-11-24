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

/*



    "id" => 3
    "created_at" => "2022-10-25 21:03:14"
    "updated_at" => "2022-10-26 03:25:59"
    "enterprise_id" => 7
    "academic_year_id" => 2
    "term_id" => 6
    "has_beginning_term" => 0
    "has_mid_term" => 1
    "has_end_term" => 0
    "report_title" => "10"
    "grading_scale_id" => 7

 "id" => 192
    "created_at" => "2022-10-25 21:03:14"
    "updated_at" => "2022-10-26 03:26:05"
    "enterprise_id" => 7
    "academic_year_id" => 2
    "term_id" => 6
    "student_id" => 2704
    "academic_class_id" => 11
    "termly_report_card_id" => 3
    "total_marks" => 194.0
    "total_aggregates" => 36.0
    "position" => 28
    "class_teacher_comment" => "Tried, Work harder next time."
    "head_teacher_comment" => "Nabakka  Husnah can do better than this."
    "class_teacher_commented" => 0
    "head_teacher_commented" => 0
    "total_students" => 77
    
"id" => 7
"created_at" => "2022-09-17 04:25:22"
"updated_at" => "2022-09-17 04:37:05"
"name" => "Kira Junior School"
"short_name" => "kjs"
"details" => "https://kirajuniorschool.ac.ug/"
"logo" => "kjs.png"
"phone_number" => "256700869880"
"email" => "info@kirajuniorschool.ac.ug"
"address" => null
"expiry" => "2022-09-16"
"administrator_id" => 2206
"subdomain" => "kjs"
"color" => "#038935"
"welcome_message" => "<p>"In pursuit of faith and wisdom"</p>"
"type" => "Primary"

*/

/*

    "id" => 345
    "created_at" => "2022-10-26 22:39:36"
    "updated_at" => "2022-10-26 23:17:47"
    "enterprise_id" => 7
    "academic_year_id" => 2
    "term_id" => 6
    "student_id" => 3010
    "theology_class_id" => 8
    "theology_termly_report_card_id" => 1
    "total_students" => 68
    "total_aggregates" => 4
    "total_marks" => 396.0
    "position" => 8
    "class_teacher_comment" => "Tried, Work harder next time."
    "head_teacher_comment" => "Tasleem  Katumba can do better than this."
    "class_teacher_commented" => 10
    "head_teacher_commented" => 10
    
    */

$school_name = 'KIIRA JUNIOR PRIMARY SCHOOL';
$school_address = 'Bwera Kasese Uganda';
$school_tel = '+256783204665';
$report_title = 'END OF TERM III REPORT CARD  2019';
$school_email = 'admin@kjs.com';
?><article>

    <div class="row">
        <div class="col-2">
            <img width="100px" class="img-fluid" src="{{ url('storage/' . $r->ent->logo) }}">
        </div>

        <div class="col-8">

            <h1 class="text-center h3 p-0 m-0 text-uppercase">{{ $r->ent->name }}</h1>
            <p class="text-center p font-serif  fs-3 m-0 p-0 mt-2 title-2"><b class="m-0 p-0">{{ $r->ent->address }}</b>
            </p>
            <p class="text-center p font-serif mt-0 mb-0 title-2"><b>WEBSITE:</b> www.kirajuniorschool.ac.ug</p>
            <p class="text-center p font-serif mt-0 title-2 mb-2"><b>EMAIL:</b> {{ $r->ent->email }}</p>
            <p class="text-center p font-serif  fs-3 m-0 p-0"><u><b>{{ $r->termly_report_card->report_title }}</b></u>
            </p>

        </div>
        {{-- 19.9 --}}

        <div class="col-2 float-right text-right">
            <img width="100px" class="img-fluid float-right text-right" src="{{ $r->owner->avatar }}">
        </div>

    </div>

    <hr style="border: solid green 1px; " class="m-0 mt-1  mb-1">

    <div class="container">
        <div class="row mb-1 d-flex justify-content-between summary"style="font-size: 14px">


            <span><b>NAME:</b> <span class="value">{{ $r->owner->name }}</span></span>
            <span><b>GENDER:</b> <span class="value">{{ $r->owner->sex }}</span></span>
            <span><b>AGE:</b> <span class="value">{{ '--' }}</span></span>
            <span><b>REG NO.:</b> <span class="value">{{ $r->owner->id }}</span></span>

        </div>

    </div>

    <div class="container   ">
        <div class="row ">
            <div class="col-6 border border-dark pt-1">
                <h2 class="text-center text-uppercase h2" style="font-size: 16px">secular studies</h2>
                <hr class="my-1">
                <div class="row mt-2 d-flex justify-content-between pl-3 pr-3 summary" style="font-size: 11px">
                    <span><b>CLASS:</b> <span class="value">{{ $r->academic_class->name }}</span></span>
                    {{-- <span><b class="text-uppercase">Aggre:</b> <span class="value">18</span></span> --}}
                    <span><b class="text-uppercase">Aggregates:</b> <span
                            class="value text-lowercase">{{ $r->average_aggregates }}</span></span>

                    <span><b class="text-uppercase">DIV:</b> <span class="value">{{ $r->grade }}</span></span>
                    <span><b class="text-uppercase">Position:</b> <span
                            class="value text-lowercase">{{ $numFormat->format($r->position) }}</span></span>


                </div>
                <div class="row mt-2">
                    <div class="col-12">
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
                    </div>
                </div>

                <div class="p-0 mt-2 mb-2 class-teacher">
                    <b>CLASS TEACHER'S COMMENT:</b>
                    <span class="comment">{{ Utils::getClassTeacherComment($r)['teacher'] }}</span>
                    {{-- <span class="comment">{{  }}</span> --}}
                </div>


            </div>
            <div class="col-6 border border-dark pt-1">
                <h2 class="text-center text-uppercase" style="font-size: 16px">Theology Studies</h2>
                <hr class="my-1">
                @if ($tr != null)
                    <div class="row mt-2 d-flex justify-content-between pl-3 pr-3 summary" style="font-size: 12px">
                        <span><b>CLASS:</b> <span class="value">{{ $tr->theology_class->name }}</span></span>
                        {{-- <span><b class="text-uppercase">Aggre:</b> <span class="value">18</span></span> --}}
                        <span><b class="text-uppercase">DIV:</b> <span class="value">{{ $tr->grade }}</span></span>
                        <span><b class="text-uppercase">Position in class:</b> <span
                                class="value text-lowercase">{{ $numFormat->format($tr->position) }}</span></span>
                        <span><b class="text-uppercase">OUT OF:</b> <span class="value">{{ $tr->total_students }}
                    </div>
                @endif
                <div class="row mt-2">
                    <div class="col-12">
                        @if ($tr != null)
                            {{-- 
        "id" => 1293
    "created_at" => "2022-10-26 22:39:36"
    "updated_at" => "2022-10-26 22:39:36"
    "enterprise_id" => 7
    "theology_subject_id" => 18
    "theologry_student_report_card_id" => 345
    "did_bot" => 0
    "did_mot" => 1
    "did_eot" => 0
    "bot_mark" => 0
    "mot_mark" => 99
    "eot_mark" => 0
    "grade_name" => "D1"
    "aggregates" => 1
    "remarks" => "Excellent"
    "initials" => "L.N"
    "total" => 99
    --}}

                            <table class="table table-bordered marks-table p-0 m-0">
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
                                    {{-- 
                                        "created_at" => "2022-10-20 07:59:02"
    "updated_at" => "2022-10-20 07:59:02"
    "enterprise_id" => 7
    "theology_class_id" => 8
    "subject_teacher" => 3036
    "teacher_1" => null
    "teacher_2" => null
    "teacher_3" => null
    "code" => null
    "details" => null
    "theology_course_id" => 1

        "id" => 1
    "created_at" => "2022-10-13 09:40:43"
    "updated_at" => "2022-10-13 09:41:46"
    "name" => "QURAN"
    "short_name" => "QUR"
    "code" => "QUR"
                                    --}}

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
                                    <td colspan="1"> </td>
                                </tr>
                            </table>

                        @endif
                    </div>
                </div>

                @if ($tr != null)
                    <div class="p-0 mt-2 mb-2 class-teacher">
                        <b>CLASS TEACHER'S COMMENT:</b>
                        <span class="comment">{{ Utils::getClassTeacherComment($r)['theo'] }}</span>
                    </div>
                @endif
            </div>
        </div>


        <div class="row">
            <div class="col-8">
                <div class="row mt-3 p-0 -info ">
                    <div class="col-12  text-white scale-title" style="background-color: black">
                        <h2 class="p-1 text-center m-0 " style="font-size: 12px;">Aggregates Scale</h2>
                    </div>
                    <div class="col-12 p-0">
                        <table class="table table-bordered grade-table">
                            <tbody>
                                <tr class="text-center">
                                    <th class="text-left">Mark</th>
                                    <th>0 - 39</th>
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
            <div class="col-4">
                <div class="row mt-3 p-0 -info pl-2">
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
            <div class="col-10">
                <div class="row">
                    <div class="col-12 p-0">
                        <div class="p-0 mt-0 mb-2 class-teacher">
                            <b>HEAD TEACHER'S COMMENT:</b>
                            <span class="comment">{{ Utils::getClassTeacherComment($r)['hm'] }}</span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 p-0">
                        <div class="p-0 mt-0 mb-2 class-teacher">
                            <b>HEAD TEACHER'S COMMUNICATION:</b>
                            <span class="comment">Assalam Alaikum Warahmatullah Wabarakatuhu. We are informing our
                                beloved parents that the Quran competition for this term three is postponed to Saturday
                                9/4/2023 next term.</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-2 p-0">
                <img width="140px" style="margin-top: -10px;" class="img-fluid"
                    src="https://schooldynamics.ug/assets/kira-hm.png">
                <h2 style="line-height: 1;font-size: 16px; margin-top:-15px; margin-bottom: 0px; padding:0px;">HEAD
                    TEACHER</h2>
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
            <span><b>TERM BEGINS ON:</b> <span class="value" style="font-size: 12px!important;">6<sup>th</sup> FEB,
                    2023</span></span>
        </div>

    </div>

</article>
