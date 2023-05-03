<?php

namespace App\Http\Controllers;

use App\Models\ReportCard;
use App\Models\SecondaryReportCard;
use App\Models\StudentReportCard;
use App\Models\TheologryStudentReportCard;
use App\Models\TheologyStudentReportCardItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class PrintController2 extends Controller
{
    public function secondary_report_cards(Request $req)
    {

        $pdf = App::make('dompdf.wrapper');
        $data = [
            SecondaryReportCard::find(100)
        ];
        $pdf->loadHTML(view('report-cards/secondary_report_cards', [
            'data' => $data,
        ]));
        return $pdf->stream();
    }


    public static function get_row($t1 = "Title 1", $d1 = "Deatils 1", $t2 = "Title 2", $d2 = "Deatils 2")
    {
        return '<tr>
                    <th class="title-cell" >' . $t1 . '</th>
                    <td>' . $d1 . '</td> 
                    <th class="title-cell">' . $t2 . '</th>
                    <td>' . $d2 . '</td> 
                </tr>';
    }

    public function index(Request $req)
    {

        ini_set('max_execution_time', '-1');
        ini_set('memory_limit', '-1');

        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);

        $term_id = 0;
        if (isset($_GET['term_id'])) {
            $term_id = (int)($_GET['term_id']);
        }
        $termly_report_card_id = 2;
        if (isset($_GET['term_id'])) {
            $term_id = (int)($_GET['term_id']);
        }
        if (isset($_GET['termly_report_card_id'])) {
            $termly_report_card_id = (int)($_GET['termly_report_card_id']);
        }

        $isBlank = false;
        if (isset($_GET['task'])) {
            if ($_GET['task'] == 'blank') {
                $isBlank = true;
            }
        }

        if (isset($_GET['calss_id'])) {

            $icalss_id = ((int)($_GET['calss_id']));
            $reps  = [];
            foreach (StudentReportCard::where([
                'academic_class_id' => $icalss_id,
                'term_id' => $term_id,
                /*                 'termly_report_card_id' => $termly_report_card_id, */
            ])->get() as $r) {


                $tr = TheologryStudentReportCard::where([
                    'student_id' => $r->student_id,
                    'term_id' => $term_id,
                ])->first();

                $reps[] = [
                    'r' => $r,
                    'tr' => $tr,
                ];
            }


            $pdf = App::make('dompdf.wrapper');
            $pdf->loadHTML(view('report-cards.print', ['recs' => $reps, 'isBlank' => $isBlank]));
            return $pdf->stream();
        }

        $id = ((int)($req->id));
        $r = StudentReportCard::find($id);
        $tr = null;
        if ($r == null) {
            $theo_id = ((int)($req->theo_id));


            $tr = TheologryStudentReportCard::where([
                'id' => $theo_id,
                'term_id' => $term_id,
            ])->first();
            if ($tr != null) {
                $r = StudentReportCard::where([
                    'student_id' => $tr->owner->id,
                    'term_id' => $tr->term_id,
                    'termly_report_card_id' => $termly_report_card_id,
                ])->first();
            }
        } else {
            $tr = TheologryStudentReportCard::where([
                'student_id' => $r->owner->id,
                'term_id' => $r->term_id,
            ])->first();
        }


        if ($r == null) {
            die("Report card not found.");
        }

        $pdf = App::make('dompdf.wrapper');
        $pdf->loadHTML(view('report-cards.print', ['recs' => [['r' => $r, 'tr' => $tr]]]));
        return $pdf->stream();





        $item = $r;
        $ranges_titles = [];
        $ranges_values = [];
        foreach ($item->termly_report_card->grading_scale->grade_ranges as $val) {
            $ranges_titles[] = $val->name;
            $ranges_values[] = $val->min_mark . " - " . $val->max_mark;
        }
        $grading_tabel = '<table class="ranges_values bordered-table">';
        $grading_tabel .= '<tbody>';



        $grading_tabel .= '<tr  class=\"bordered-table\">';
        $grading_tabel .= "<th  class=\"bordered-table\">Marks</th>";
        foreach ($ranges_values as $t) {
            $grading_tabel .= "<th  class=\"bordered-table\">$t</th>";
        }
        $grading_tabel .= '</tr>';



        $grading_tabel .= '<tr  class=\"bordered-table\">';
        $grading_tabel .= "<th  class=\"bordered-table\">Aggre.</th>";
        foreach ($ranges_titles as $t) {
            $grading_tabel .= "<td class=\"bordered-table text-center \" >$t</td>";
        }
        $grading_tabel .= '</tr>';


        $grading_tabel .= '</tbody>';
        $grading_tabel .= "</table>";

        $bottom_table = '<table>';
        $bottom_table .= '<tr><td>Class teacher\'s remarts</td><td><br>Signature:</td></tr>';
        $bottom_table .= '<tr><td>Head teacher\'s remarts</td><td><br>Signature:</td></tr>';
        $bottom_table .= '<tr><td><b>Fees  balance</b>:............ <br>NEXT TERM BENINS ON:</b>:..../..../......</td><td><br>Signature:</td></tr>';
        $bottom_table .= '</table>';


        //dd($item->termly_report_card->grading_scale->grade_ranges);
        $rows = "";
        foreach ($item->items as $v) {

            $rows .= "<tr>";
            $rows .= "<td>{$v->main_course->name}</td>";
            $rows .= "<td>{$v->main_course->code}</td>";
            $rows .= "<td>{$v->bot_mark}</td>";
            $rows .= "<td>{$v->mot_mark}</td>";
            $rows .= "<td>{$v->eot_mark}</td>";
            $rows .= "<td>" . ($v->eot_mark + $v->mot_mark + $v->main_course->bot_mark) . "</td>";
            $rows .= "<td>{$v->grade_name}</td>";
            $rows .= "<td>{$v->aggregates}</td>";
            $rows .= "<td>{$v->remarks}</td>";
            $rows .= "<td>{$v->remarks}</td>";
            $rows .= "</tr>";
        }


        $r = new ReportCard();

        $data = '<link type="text/css" href="' . url('assets/bootstrap.css') . '" rel="stylesheet" />';
        $data = '<link type="text/css" href="' . url('assets/print.css') . '" rel="stylesheet" />';
        $data .= "
            <style>
            @page { margin: 15px; }
            .font-serif{
                font-family:  sans-serif!important;
            } 
            p{
                font-size: 12px;
                padding: 0;
                margin: 0; 
            }
            .title-cell{
                width: 25%;
                font-family: sans-serif;
                font-size: 12px;
                background-color: #D9D9D9;
                font-family:  sans-serif;
                font-weight: 100;
            }
           
            table, th, td {
                font-weight: 100;
                text-align: reight;
                font-family:  sans-serif;
                font-size: 12px; 
                border-collapse: collapse;
                padding: 4px;
            }

            .marks-cell tr td, 
            .marks-cell thead tr th, 
            {
                font-weight: 100;
                text-align: reight;
                font-family:  sans-serif;
                font-size: 12px; 
                border-collapse: collapse;
                border: 1px solid black;
                padding: 4px;
            }

            .bordered-table{
                border: 1px solid black;
                border-collapse: collapse;
            }
            table{
                  width: 100%;
              }

            p,h1,h2,h3,h4,h5,h6,.h1,.h2,.h3,.h4,.h5,.h6{
                padding: 0px;
                margin: 0px;
            }
            .fs-1{
                font-size: 24px;
            }
            .fs-2{
                font-size: 22px;
            }
            .fs-3{
                font-size: 20px;
            }
            .fs-4{
                font-size: 18px;
            }
            .fs-5{
                font-size: 16px;
            }
            .fs-6{
                font-size: 14px;
            }
            .fs-7{
                font-size: 12px;
            }
            .fs-8{
                font-size: 10px;
            }
            .fs-9{
                font-size: 8px;
            }
            .fs-10{
                font-size: 6px;
            }
            .fs-11{
                font-size: 4px;
            }
            .fs-12{
                font-size: 2px;
            }

            @page { margin: 20px; } 
            </style>
        ";


        $r->school_name = 'Sudais Muslim Secondary School';
        $r->school_address = 'P.O.BOX  504, Bwera Kasese';
        $r->school_tel = '0779755798 / 0751244522';
        $r->report_title = 'END OF TERM III 2022 REPORT';
        $r->school_photo_url = url('assets/logo.jpeg');
        $r->school_student_photo = url('assets/student.jpg');


        $head = '';
        $head .= '<h1 class="text-center h4 p-0 m-0">' . $r->school_name . '</h1>';
        $head .= '<p class="text-center p font-serif  fs-3 m-0 p-0" ><b class="m-0 p-0">' . $r->school_address . '</b></p>';
        $head .= '<p class="text-center p font-serif mt-1"><b>TEL:</b> ' . $r->school_tel . '</p>';
        $head .= '<p class="text-center p font-serif"><b>EMAIL:</b> ' . $r->school_tel . '</p>';
        $head .= '<p class="text-center p font-serif  fs-3 mt-1" ><u><b>' . $r->report_title . '</b></u></p>';

        $data .= '<table>
                    <tr>
                        <td style="width: 15%;" ><img class="img-fluid" src="' . $r->school_photo_url . '"></td>
                        <td class="text-center">' . $head . '</td> 
                        <td style="width: 15%;" ><img class="img-fluid" src="' . $r->school_student_photo . '"></td>
                    </tr>
                </table>';

        $data .= '<table style="width: 100%;" >
                    <tr>
                        <td class="fs-5">NAME: <b>Muhindo Mubaraka</b></td>
                        <td class="fs-5">GENDER: <b>Male</b></td>
                        <td class="fs-5 text-right">REG No.: <b>U1211</b></td> 
                    </tr>        
                    <tr>
                        <td class="fs-5">CLASS: <b>S.6 Lion</b></td>
                        <td class="fs-5">Aggregates.: <b>12</b> </td>
                        <td class="fs-5 text-right">DIV: <b>B</b></td> 
                    </tr>    
                </table>';

        $data .= '<table class="bordered-table marks-cell" >
                    <thead>
                        <tr>
                            <th>SUBJECTS</th>
                            <th>CODE</th>
                            <th>B.O.T (30)</th>
                            <th>M.O.T (30)</th>
                            <th>E.O.T (40)</th>
                            <th>TOTAL (100%)</th>
                            <th>Grade</th>
                            <th>Aggr</th>
                            <th>Remarks</th>
                            <th>Initials</th>
                        </tr>        
                    </thead>       
                    <tbody>
                    ' . $rows . '       
                    </tbody>       
            </table>';


        $data .= '<br><h4 class="text-center">TOTAL POINTS: 18</h4>';
        $data .= $grading_tabel;
        $data .= $bottom_table;

        return $data . $data;
        $pdf = App::make('dompdf.wrapper');
        $pdf->loadHTML('romina');
        return $pdf->stream();
    }

    // 
}
