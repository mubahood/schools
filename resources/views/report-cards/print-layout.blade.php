<?php
$max_bot = 30;
$max_mot = 40;
$max_eot = 60;
/* 
  "id" => 192
    "created_at" => "2022-10-25 21:03:14"
    "updated_at" => "2022-10-26 00:36:50"
    "enterprise_id" => 7
    "academic_year_id" => 2
    "term_id" => 6
    "student_id" => 2704
    "academic_class_id" => 11
    "termly_report_card_id" => 3
    "total_marks" => 154.0
    "total_aggregates" => 40.0
    "position" => 1
    "class_teacher_comment" => "Excelent! Keep it up."
    "head_teacher_comment" => "Nabakka  Husnah is such a brilliant pupil. Keep it up."
    "class_teacher_commented" => 0
    "head_teacher_commented" => 0
    "total_students" => 77
*/
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
        <div class="col-2">
            <img width="120px" class="img-fluid" src="{{ url('assets/logo.jpeg') }}">
        </div>

        <div class="col-8">

            <h1 class="text-center h3 p-0 m-0">{{ $school_name }}</h1>
            <p class="text-center p font-serif  fs-3 m-0 p-0 mt-2 title-2"><b class="m-0 p-0">{{ $school_address }}</b>
            </p>
            <p class="text-center p font-serif mt-0 mb-0 title-2"><b>TEL:</b> {{ $school_tel }}</p>
            <p class="text-center p font-serif mt-0 title-2 mb-2"><b>EMAIL:</b> {{ $school_email }}</p>
            <p class="text-center p font-serif  fs-3 m-0 p-0"><u><b>{{ $report_title }}</b></u></p>

        </div>

        <div class="col-2 float-right text-right">
            <img width="120px" class="img-fluid float-right text-right" src="{{ $r->owner->avatar }}">
        </div>

    </div>

    <hr style="border: solid green 1px; " class="m-0 mt-2  mb-2">

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
                <div class="row mt-2 d-flex justify-content-between pl-3 pr-3 summary" style="font-size: 12px">
                    <span><b>CLASS:</b> <span class="value">{{ $r->academic_class->name }}</span></span>
                    {{-- <span><b class="text-uppercase">Aggre:</b> <span class="value">18</span></span> --}}
                    <span><b class="text-uppercase">Grade:</b> <span class="value">B</span></span>
                    <span><b class="text-uppercase">Position in class:</b> <span class="value">{{ $r->position }}<sup class="text-lowercase">{{ date("S", mktime(0, 0, 0, 0, $r->position, 0)) }}</sup></span></span>
                    <span><b class="text-uppercase">OUT OF:</b> <span class="value">{{ $r->total_students }} 
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
                                    <th>M.O.T <br> ({{ $max_mot }})</th>
                                @endif
                                @if ($r->termly_report_card->has_end_term)
                                    <th>E.O.T <br> ({{ $max_eot }})</th>
                                @endif
                                <th>TOTAL <br> (100%)</th>
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
                                        <td>{{ $v->mot_mark }}</td>
                                    @endif
                                    @if ($r->termly_report_card->has_end_term)
                                        <td>{{ $v->eot_mark }}</td>
                                    @endif
                                    <td>{{ $v->total }}</td>
                                    <td>{{ $v->grade_name }}</td>
                                    <td class="remarks">{{ $v->remarks }}</td>
                                    <td class="remarks text-center">M.K</td>
                                </tr>
                            @endforeach

                        </table>
                    </div>
                </div>

                <div class="p-0 mt-2 mb-2 class-teacher">
                    <b>CLASS TEACHER'S COMMENT:</b>
                    <span class="comment">Lorem ipsum dolor sit amet consectetur adipisicing elit. sit amet consectetur
                        adipisicing elit. Deleniti sit alias veritatis</span>
                </div>


            </div>
            <div class="col-6 border border-dark pt-1">
                <h2 class="text-center" style="font-size: 16px">دراسات اللاهوت</h2>
                <hr class="my-1">
                <div class="row mt-2 d-flex justify-content-between pl-3 pr-3">
                    <span><b>CLASS:</b> <span class="value">P.7</span></span>
                    <span><b>Aggregates:</b> <span class="value">18</span></span>
                    <span><b>Grade:</b> <span class="value">B</span></span>
                    <span><b>Position:</b> <span class="value">B</span></span>
                </div>
                <div class="row mt-2">
                    <div class="col-12">
                        <table class="table table-bordered marks-table p-0 m-0">
                            <thead class="p-0 m-0 text-center">
                                <th class="text-left pl-2">SUBJECTS</th>
                                <th>B.O.T <br> (30)</th>
                                <th>M.O.T <br> (30)</th>
                                <th>E.O.T <br> (30)</th>
                                <th>TOTAL <br> (100%)</th>
                                <th>Aggr</th>
                                <th class="remarks">Remarks</th>
                                <th class="remarks text-center">Initials</th>
                            </thead>
                            <tr class="marks">
                                <th>Maths</th>
                                <td>10</td>
                                <td>43</td>
                                <td>11</td>
                                <td>65</td>
                                <td>D3</td>
                                <td class="remarks">Fair</td>
                                <td class="remarks text-center">M.K</td>
                            </tr>
                            <tr class="marks">
                                <th>SCIENCE</th>
                                <td>10</td>
                                <td>43</td>
                                <td>11</td>
                                <td>65</td>
                                <td>D3</td>
                                <td class="remarks">Fair</td>
                                <td class="remarks text-center">M.K</td>
                            </tr>
                            <tr class="marks">
                                <th>STT</th>
                                <td>10</td>
                                <td>43</td>
                                <td>11</td>
                                <td>65</td>
                                <td>D3</td>
                                <td class="remarks">Fair</td>
                                <td class="remarks text-center">M.K</td>
                            </tr>
                            <tr class="marks">
                                <th>ENGLISH</th>
                                <td>10</td>
                                <td>43</td>
                                <td>11</td>
                                <td>65</td>
                                <td>D3</td>
                                <td class="remarks">Fair</td>
                                <td class="remarks text-center">M.K</td>
                            </tr>
                            <tr class="marks">
                                <th><b>TOTAL</b></th>
                                <td colspan="2"> </td>
                                <td><b>100</b></td>
                                <td><b>400</b></td>
                                <td colspan="3"> </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="p-0 mt-2 mb-2 class-teacher">
                    <b>CLASS TEACHER'S COMMENT:</b>
                    <span class="comment">Lorem ipsum dolor sit amet consectetur adipisicing elit. sit amet consectetur
                        adipisicing elit. Deleniti sit alias veritatis</span>
                </div>
            </div>
        </div>

        <div class="row mt-3 p-0 -info ">
            <div class="col-12  text-white" style="background-color: black">
                <h2 class="p-1 text-center m-0 " style="font-size: 12px;">Grading scale</h2>
            </div>
            <div class="col-12 p-0">
                <table class="table table-bordered grade-table">
                    <tbody>
                        <tr class="text-center">
                            <th class="text-left">Mark</th>
                            <th>0 - 49</th>
                            <th>56 - 59</th>
                            <th>60 - 65</th>
                            <th>66 - 69</th>
                            <th>70 - 79</th>
                            <th>80 - 89</th>
                            <th>90 - 94</th>
                            <th>95 - 100</th>
                        </tr>
                        <tr>
                            <th class="text-left">Aggregates</th>
                            <td class="bordered-table text-center value ">F9</td>
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

        <div class="row">
            <div class="col-10 p-0">
                <div class="p-0 mt-0 mb-2 class-teacher">
                    <b>HEAD TEACHER'S COMMENT:</b>
                    <span class="comment">Lorem ipsum dolor sit amet consectetur adipisicing lor sit amet consectetur
                        adipisicing elit. sit amet consectetur adipisicing elit. Deleniti sit alias veritatis</span>
                </div>
            </div>
            <div class="col-2 ">
                <b>Signature:</b>
            </div>
        </div>
        <div class="row">
            <div class="col-12 p-0">
                <div class="p-0 mt-0 mb-2 class-teacher">
                    <b>HEAD TEACHER'S COMMUNICATION:</b>
                    <span class="comment">Lorem ipsum dolor sit amet consectetur adipisicing lor sit amet consectetur
                        adipisicing elit. sit amet consectetur adipisicing elit. Deleniti sit alias veritatis</span>
                </div>
            </div>
        </div>
        <div class="row mt-2 d-flex justify-content-between p-0 border-top pt-2 border-primary"
            style="font-size: 12px;">
            <span><b>SCHOOL FEES BALANCE:</b> <span class="value" style="font-size: 12px!important;">UGX
                    160,000</span></span>
            <span><b>NEXT TERM TUTION FEE:</b> <span class="value" style="font-size: 12px!important;">UGX
                    18,000</span></span>
            <span><b>SCHOOL PAY CODE:</b> <span class="value"
                    style="font-size: 12px!important;">102776152</span></span>
            <span><b>NEXT TERM BEGINS ON:</b> <span class="value" style="font-size: 12px!important;">16<sup>th</sup>
                    Oct, 2022</span></span>
        </div>

    </div>

</article>
