<?php
use App\Models\Utils;

function getClassTeacherComment($r)
{
    $position = $r->average_aggregates;
    $total_students = $r->total_aggregates;

    $percentage = 0;
    if ($total_students > 0) {
        $percentage = ($position / $total_students) * 100;
    } else {
        $position = 0;
    }

    $Comment1 = getClassTeacherComment1();
    $Comment2 = getClassTeacherComment2();
    $Comment3 = getClassTeacherComment3();

    $theologyComments1 = theologyComments1();
    $theologyComments2 = theologyComments2();
    $theologyComments3 = theologyComments3();

    $hmComment1 = hmComment1();
    $hmComment2 = hmComment2();
    $hmComment3 = hmComment3();
    $hmComment4 = hmComment4();
    $hmComment5 = hmComment5();

    $sex = 'He ';
    if (strtolower($r->owner->sex) == 'male') {
        $sex = 'He ';
    } else {
        $sex = 'She ';
    }
    $nurseryComments1 = nurseryComments1($sex);
    $nurseryComments2 = nurseryComments2($sex);
    $nurseryComments3 = nurseryComments3($sex);

    shuffle($Comment1);
    shuffle($Comment2);
    shuffle($Comment3);

    shuffle($hmComment1);
    shuffle($hmComment2);
    shuffle($hmComment3);
    shuffle($hmComment4);
    shuffle($hmComment5);

    shuffle($theologyComments1);
    shuffle($theologyComments2);
    shuffle($theologyComments3);

    shuffle($nurseryComments1);
    shuffle($nurseryComments2);
    shuffle($nurseryComments3);

    $comment['teacher'] = '-';
    $comment['hm'] = '-';
    $comment['theo'] = '-';
    $comment['n'] = '-';
    if ($percentage < 40) {
        $comment['teacher'] = $Comment1[1];
        $comment['theo'] = $theologyComments1[1];
        $comment['n'] = $nurseryComments1[1];
    } elseif ($percentage < 60) {
        $comment['theo'] = $theologyComments2[1];
        $comment['teacher'] = $Comment2[1];
        $comment['n'] = $nurseryComments2[1];
    } elseif ($percentage < 101) {
        $comment['theo'] = $theologyComments3[1];
        $comment['teacher'] = $Comment3[2];
        $comment['n'] = $nurseryComments3[1];
    }

    if ($percentage < 20) {
        $comment['hm'] = $hmComment1[1];
    } elseif ($percentage < 40) {
        $comment['hm'] = $hmComment2[1];
    } elseif ($percentage < 60) {
        $comment['hm'] = $hmComment3[2];
    } elseif ($percentage < 80) {
        $comment['hm'] = $hmComment4[2];
    } elseif ($percentage < 101) {
        $comment['hm'] = $hmComment5[2];
    }

    if ($r->average_aggregates > 34) {
    }

    return $comment;
}

function getClassTeacherComment2()
{
    return ['Wonderful results. Don’t relax', 'Promising performance. Keep working hard for first grade.', 'Encouraging results, Continue reading hard.'];
}

function getClassTeacherComment1()
{
    return ['An excellent performance. Keep it up.', 'You are an academician. Keep shining.', 'A remarkable performance observed. Keep excelling.', 'You have exhibited excellent results.'];
}

function getClassTeacherComment3()
{
    return ['Work hard in all subjects.', 'More effort still needed for better performance,', 'There is still room for improvement.', 'Double your effort in all subjects.', 'You need to concentrate more during exams.'];
}

function theologyComments1()
{
    return ['Good work, thank you', 'We congratulate you upon this great performance.', 'Thank you for your performance'];
}

function theologyComments2()
{
    return ['Strive for first grade.', 'We expect a first grade from you.', 'Aim higher for better performance.'];
}

function theologyComments3()
{
    return ['Revise more than this.', 'Consultation is the key to excellence.', 'Befriend excellent students.', 'More effort is still needed.', 'Double your effort in all subjects.'];
}

function nurseryComments1($Sex)
{
    return [$Sex . ' performance has greatly improved; she produces attractive work.', 'In all the fundamental subjects, he is performing admirably well.', $Sex . ' is focused and enthusiastic learner with much determination.', $Sex . ' has produced an excellent report ' . $Sex . ' shouldn’t relax.', $Sex . ' performance is very good. He just needs more encouragement.', $Sex . ' is hardworking, determined, co-operative and well disciplined.'];
}

function nurseryComments2($Sex)
{
    return [$Sex . ' has a lot of potential and is working hard to realize it.', $Sex . ' is a focused and enthusiastic learner with much determination.', $Sex . ' is self-confident and has excellent manners. Thumbs up.', $Sex . ' has done some good work, but it hasn’t been consistent because of her frequent relaxation.', $Sex . ' can produce considerably better results. Though she frequently seeks the attention and help from peers.', $Sex . ' has troubles focusing in class which hinders his or her ability to participate fully in class activities and tasks.', $Sex . ' is genuinely interested in everything we do, though experiencing some difficulties.'];
}

function nurseryComments3($Sex)
{
    return [$Sex . ' has demonstrated a positive attitude towards wanting to improve.', 'Directions are still tough for him to follow.', $Sex . ' can do better than this, but more effort is needed in reading.', $Sex . ' is an exceptionally thoughtful student.'];
}
function hmCommunication()
{
    return 'Assalam Alaikum Warahmatullah Wabarakatuhu. We are informing our beloved parents that the Quran competition for this term three is postponed to Saturday 9/4/2023 next term.';
}

function hmComment1()
{
    return ['Excellent performance reflected, thank you.', 'Excellent results displayed; keep the spirit up.', 'Very good and encouraging performance, keep it up.', 'Wonderful results reflected, ought to be rewarded.', 'Thank you for the wonderful and excellent performance keep it up.'];
}

function hmComment2()
{
    return ['Promising performance displayed, keep working harder to attain the best.', 'Steady progress reflected, keep it up to attain the best next time.', 'Encouraging results shown, do not relax.', 'Positive progress observed, continue with the energy for a better grade.', 'Promising performance displayed, though more is still needed to attain the best aggregate.'];
}
function hmComment3()
{
    return ['Work harder than this to attain a better aggregate.', 'Aim higher than thus to better your performance.', 'Steady progress reflected, aim higher than this next time.', 'Positive progress observed do not relax.', 'Steady progress though more is still desired to attain the best.'];
}
function hmComment4()
{
    return ['You need to concentrate more weaker areas to better your performance next time.', 'Double your energy and concentration to better your results.', 'A lot more is still desired from for a better performance next time.', 'You are encouraged to concentrate in class for a better performance.', 'Slight improvement reflected; you are encouraged to continue working harder.'];
}
function hmComment5()
{
    return ['Double your energy in all areas for a better grade.', 'Concentration in class at all times to better your performance next time.', 'Always consult your teachers in class to better aim higher than this.', 'Always aim higher than this.', 'Teacher- parent relationship is needed to help the learner improve.'];
}

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
            <img width="120px" class="img-fluid" src="{{ url('storage/' . $r->ent->logo) }}">
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
                <div class="row mt-2 d-flex justify-content-between pl-3 pr-3 summary" style="font-size: 11px">
                    <span><b>CLASS:</b> <span class="value">{{ $r->academic_class->name }}</span></span>
                    {{-- <span><b class="text-uppercase">Aggre:</b> <span class="value">18</span></span> --}}
                    {{-- span><b class="text-uppercase">Aggregates:</b> <span
                            class="value text-lowercase">{{ $r->average_aggregates }}</span></span> --}}

                    {{-- <span><b class="text-uppercase">DIV:</b> <span class="value">{{ $r->grade }}</span></span>
                    <span><b class="text-uppercase">Position:</b> <span
                            class="value text-lowercase">{{ $numFormat->format($r->position) }}</span></span>

                    <span><b class="text-uppercase">OUT OF :</b> <span class="value">{{ $r->total_students }} --}}
                </div>
                <div class="row mt-2">
                    <div class="col-12">
                        <table class="table table-bordered marks-table p-0 m-0">
                            <thead class="p-0 m-0 text-center">
                                <th class="text-left pl-2">SUBJECTS</th>
                                <th>Area of learning</th>
                                <th class="remarks">GRADE</th>
                                {{-- <th class="remarks text-center">Initials</th> --}}
                            </thead>
                            @foreach ($r->items as $v)
                                <?php
                                $_v = Utils::compute_competance($v);
                                ?>
                                <tr class="marks-1">
                                    <th style="font-size: 10px;">{{ $_v['competance'] }}</th>
                                    <td>{{ $_v['comment'] }}</td>
                                    <td class="remarks text-center"><b>{{ $_v['grade'] }}</b></td>
                                    {{--  <td class="remarks text-center">{{ $v->initials }}</td> --}}
                                </tr>
                            @endforeach

                        </table>
                    </div>
                </div>

                <div class="p-0 mt-2 mb-2 class-teacher">
                    <b>CLASS TEACHER'S COMMENT:</b>
                    <span class="comment">{{ getClassTeacherComment($r)['n'] }}</span>
                </div>


            </div>
            <div class="col-6 border border-dark pt-1">
                <h2 class="text-center text-uppercase" style="font-size: 16px">Theology Studies</h2>
                <hr class="my-1">
                @if ($tr != null)
                    {{-- <div class="row mt-2 d-flex justify-content-between pl-3 pr-3 summary" style="font-size: 12px">
                        <span><b>CLASS:</b> <span class="value">{{ $tr->theology_class->name }}</span></span> 
                        <span><b class="text-uppercase">DIV:</b> <span
                                class="value">{{ $tr->grade }}</span></span>
                        <span><b class="text-uppercase">Position in class:</b> <span
                                class="value text-lowercase">{{ $numFormat->format($tr->position) }}</span></span>
                        <span><b class="text-uppercase">OUT OF:</b> <span class="value">{{ $tr->total_students }}
                    </div> --}}
                @endif
                <div class="row mt-2">
                    <div class="col-12">
                        @if ($tr != null)

                            <table class="table table-bordered marks-table p-0 m-0">
                                <thead class="p-0 m-0 text-center">
                                    <th class="text-left pl-2">SUBJECTS</th>
                                    <th>Area of learning</th>
                                    <th class="remarks">GRADE</th>
                                    {{-- <th class="remarks text-center">Initials</th> --}}
                                </thead>
                                @foreach ($tr->items as $v)
                                    <?php
                                    $_v = Utils::compute_competance_theology($v);
                                    ?>
                                    <tr class="marks-1">
                                        <th style="font-size: 10px;">{{ $_v['competance'] }}</th>
                                        <td>{{ $_v['comment'] }}</td>
                                        <td class="remarks text-center"><b>{{ $_v['grade'] }}</b></td>
                                        {{--  <td class="remarks text-center">{{ $v->initials }}</td> --}}
                                    </tr>
                                @endforeach

                            </table>

                        @endif
                    </div>
                </div>

                @if ($tr != null)
                    <div class="p-0 mt-2 mb-2 class-teacher">
                        <b>CLASS TEACHER'S COMMENT:</b>
                        <span class="comment">{{ $tr->class_teacher_comment }}</span>
                    </div>
                @endif
            </div>
        </div>


        <div class="row">
            <div class="col-12">
                <div class="row mt-3 p-0 -info ">
                    <div class="col-12  text-white scale-title" style="background-color: black">
                        <h2 class="p-1 text-center m-0 " style="font-size: 12px;">Grading</h2>
                    </div>
                    <div class="col-12 p-0">
                        <table class="table table-bordered grade-table">
                            <tbody>
                                <tr class="text-center">
                                    <th class="text-left">Grade</th>
                                    {{-- <th>LA</th> --}}
                                    <th>E</th>
                                    <th>W</th>
                                    <th>V.G</th>
                                    <th>G</th>
                                    <th>F</th>
                                </tr>
                                <tr>
                                    <th class="text-left">Meaning</th>
                                    {{-- <td class="bordered-table text-center  ">Learning area</td> --}}
                                    <td class="bordered-table text-center  ">Excellent</td>
                                    <td class="bordered-table text-center  ">Working on skills</td>
                                    <td class="bordered-table text-center  ">Very Good</td>
                                    <td class="bordered-table text-center  ">Good</td>
                                    <td class="bordered-table text-center  ">Fair</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            {{-- <div class="col-4">
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
                                    <th class="text-left">GRADE</th>
                                    <td class="bordered-table text-center value ">A</td>
                                    <td class="bordered-table text-center value ">B</td>
                                    <td class="bordered-table text-center value ">C</td>
                                    <td class="bordered-table text-center value ">D</td>
                                    <td class="bordered-table text-center value ">U</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div> --}}
        </div>

        <div class="row">
            <div class="col-10">
                <div class="row">
                    <div class="col-12 p-0">
                        <div class="p-0 mt-0 mb-2 class-teacher">
                            <b>HEAD TEACHER'S COMMENT:</b>
                            <span class="comment">{{ getClassTeacherComment($r)['hm'] }}</span>
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
                <img width="140px" style="margin-top: -20px;" class="img-fluid"
                    src="https://schooldynamics.ug/assets/kira-hm.png">
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
