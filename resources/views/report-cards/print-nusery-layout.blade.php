<?php
use App\Models\Utils;

if (!isset($isBlank)) {
    $isBlank = false;
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

$school_name = 'KIIRA JUNIOR PRIMARY SCHOOL';
$school_address = 'Bwera Kasese Uganda';
$school_tel = '+256783204665';
$report_title = 'END OF TERM III REPORT CARD  2019';
$school_email = 'admin@kjs.com';
?><article class="ml-4 mr-4">

    <div class="row">
        <table class="w-100">
            <tr>
                <td width="8%" class=" ">
                    <img style="width: 100%;" src="{{ public_path('storage/' . $r->ent->logo) }}">
                </td>
                <td>
                    <h1 class="text-center h3 p-0 m-0 text-uppercase">{{ $r->ent->name }}</h1>
                    <p class="text-center p font-serif  fs-3 m-0 p-0 mt-2 title-2"><b
                            class="m-0 p-0">{{ $r->ent->address }}</b>
                    </p>
                    <p class="text-center p font-serif mt-0 mb-0 title-2"><b>WEBSITE:</b> www.kirajuniorschool.ac.ug</p>
                    <p class="text-center p font-serif mt-0 title-2 mb-2"><b>EMAIL:</b> {{ $r->ent->email }}</p>
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
        <hr style="border: solid {{ $r->ent->color }} 1px; " class="m-0 mt-1  mb-1">

    </div>
    <div class="row mb-1 d-flex justify-content-between summary"style="font-size: 14px">


        <span><b>NAME:</b> <span class="value">{{ $r->owner->name }}</span></span>
        <span><b>GENDER:</b> <span class="value">{{ $r->owner->sex }}</span></span>
        <span><b>AGE:</b> <span class="value">{{ '--' }}</span></span>
        <span><b>REG NO.:</b> <span class="value">{{ $r->owner->id }}</span></span>

    </div>

    <div class="row">
        <table >
            <tbody>
                <tr>
                    <td style="width: 40%!important">
                        <h2 class="text-center mt-1 text-uppercase h2" style="font-size: 16px"><u>secular studies</u>
                        </h2>
                        <div class="row mt-1 d-flex justify-content-between pl-3 pr-3 summary mb-1"
                            style="font-size: 11px">
                            <span><b>CLASS:</b> <span class="value">{{ $r->academic_class->name }}</span></span>
                        </div>
                        <table class="table table-bordered marks-table p-0 m-0">
                            <thead class="p-0 m-0 text-center">
                                <th class="text-left pl-2">SUBJECTS</th>
                                <th>Area of learning</th>
                                <th class="remarks">GRADE</th>
                                {{-- <th class="remarks text-center">Initials</th> --}}
                            </thead>
                            <?php $done = []; ?>
                            @foreach ($r->items as $v)
                                <?php
                                $_v = Utils::compute_competance($v);
                                if (in_array($_v['competance'], $done)) {
                                    continue;
                                }
                                $done[] = $_v['competance'];
                                $dp = $_v['competance'];
                                if($dp == 'Literacy 1A'){
                                    $dp = 'Learning Area 1';
                                }

                                if($dp == 'Literacy 1B'){
                                    $dp = 'Learning Area 2';
                                } 

                                ?>
                                <tr class="marks-1">
                                    <th style="font-size: 10px;">{{ $dp }}</th>
                                    <td>{!! $_v['comment'] !!}</td>
                                    <td class="remarks text-center">
                                        @if (!$isBlank)
                                            <b>{!! $_v['grade'] !!}</b>
                                        @else
                                        @endif 

                                    </td>
                                    {{--  <td class="remarks text-center">{{ $v->initials }}</td> --}}
                                </tr>
                            @endforeach
                        </table>
                        <div class="p-0 mt-2 mb-2 class-teacher">
                            <b class="d-block">CLASS TEACHER'S COMMENT:</b>
                            .............................................................................................................................................
                            <br>
                            .............................................................................................................................................
                            <br>
                            .............................................................................................................................................
                            <br>

                            {{-- <span class="comment">{{ Utils::getClassTeacherComment($r)['n'] }}</span> --}}
                        </div>

                    </td>
                    <td style="width: 40%">
                        <h2 class="text-center text-uppercase" style="font-size: 16px"><u>Theology Studies</u></h2>
                        <hr class="my-1">
                        @if ($tr != null)
                            <table class="table table-bordered marks-table p-0 m-0 w-100 ">
                                <thead class="p-0 m-0 text-center">
                                    <th style="width: 7%" class="text-left pl-2">SUBJECTS</th>
                                    <th style="width: 30%">Area of learning</th>
                                    <th style="width: 7%" class="remarks">GRADE</th>
                                    {{-- <th class="remarks text-center">Initials</th> --}}
                                </thead>
                                @foreach ($tr->items as $v)
                                    <?php
                                    $_v = Utils::compute_competance_theology($v);
                                    ?>
                                    <tr class="marks-1">
                                        <th style="font-size: 10px;">{{ $_v['competance'] }}</th>
                                        <td>{{ $_v['comment'] }}</td>
                                        <td class="remarks text-center">
                                            @if (!$isBlank)
                                                <b>{{ $_v['grade'] }}</b>
                                            @else
                                            @endif
                                        </td>
                                        {{--  <td class="remarks text-center">{{ $v->initials }}</td> --}}
                                    </tr>
                                @endforeach
                            </table>
                        @endif

                        @if ($tr != null)
                            <div class="p-0 mt-2 mb-2 class-teacher"><br>
                                <b class="d-block">CLASS TEACHER'S COMMENT:</b>
                                ...............................................................................................................................
                                <br>
                                ...............................................................................................................................
                                <br>
                                .............................................................................................................................
                                <br>
                                ..............................................................................................................................
                                <br>


                                {{--                                 <span class="comment">{{ Utils::getClassTeacherComment($tr)['theo'] }}</span> --}}
                            </div>
                        @endif

                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="row">
        <h2 class="p-1 text-center m-0 bg-black text-uppercase " style="font-size: 16px; font-weight: 600;">Grading</h2>

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

    <table class="w-100">
        <tr>
            <td style="width: 80%">
                {{--  <div class="row">
                    <div class="col-12 p-0">
                        <div class="p-0 mt-0 mb-2 class-teacher">
                            <b>HEAD TEACHER'S COMMENT:</b>
                            <span class="comment">{{ Utils::getClassTeacherComment($r)['hm'] }}</span>
                        </div>
                    </div>
                </div> --}}
                <div class="row">
                    <div class="col-12 p-0">
                        <div class="p-0 mt-0 mb-2 class-teacher">
                            <b>HEAD TEACHER'S COMMUNICATION:</b>
                            <span class="comment">
                                At Kira Junior School, we believe that every child is a genius who only needs a
                                conducive environment to blossom. Please note that next term we have to prepare for the
                                sports gala and the election of prefects InSha'Allah.
                            </span>
                        </div>
                    </div>
                </div>
            </td>
            <td class=" pl-3 text-center">
                <img width="70%" style=" " class="text-center "
                    src="{{ public_path('storage/images/kira-hm.png') }}">
                <h2 class="text-center" style="line-height: .6rem;font-size: 14px;   margin-bottom: 0px; padding:0px;">
                    HEAD
                    TEACHER</h2>
            </td>
        </tr>
    </table>


    <div class="row mt-2 d-flex justify-content-between p-0 border-top pt-2 border-primary" style="font-size: 12px;">
        <span><b>SCHOOL FEES BALANCE:</b> <span class="value" style="font-size: 12px!important;">
                ........................................{{-- {{ $bal_text }}</span></span> | --}}
                {{-- <span><b>NEXT TERM TUTION FEE:</b> <span class="value" style="font-size: 12px!important;">UGX
            18,000</span></span> --}}
                <span><b>SCHOOL PAY CODE:</b> <span class="value"
                        style="font-size: 12px!important;">{{ $r->owner->school_pay_payment_code }}</span></span> |
                <span><b>NEXT TERM BEGINS ON:</b> <span class="value"
                        style="font-size: 12px!important;">29<sup>th</sup> MAY,
                        2023</span></span>
    </div>
</article>
