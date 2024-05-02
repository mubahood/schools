<?php

namespace App\Http\Controllers;

use App\Models\FixedAsset;
use App\Models\FixedAssetPrint;
use App\Models\ReportCard;
use App\Models\ReportCardPrint;
use App\Models\SecondaryReportCard;
use App\Models\StudentReportCard;
use App\Models\TermlyReportCard;
use App\Models\TheologryStudentReportCard;
use App\Models\TheologyStudentReportCardItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class ReportCardsPrintingController extends Controller
{

    public function index(Request $req)
    {

        ini_set('max_execution_time', '-1');
        ini_set('memory_limit', '-1');

        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
        $min = -1;
        $max = -1;
        if (isset($_GET['min'])) {
            $min = (int)($_GET['min']);
        }
        if (isset($_GET['max'])) {
            $max = (int)($_GET['max']);
        }
        $printing = ReportCardPrint::find($req->id);
        if ($printing == null) {
            die("Printing not found.");
        }

        $termly_report_card = TermlyReportCard::find($printing->termly_report_card_id);
        if ($termly_report_card == null) {
            die("Termly report card not found.");
        }

        $items = [];

        $pdf = App::make('dompdf.wrapper');
        /* dd($printing);
        if ($termly_report_card->reports_template == 'template_1') {
            $pdf->loadHTML(view('report-cards.template-1.print', ['items' => $items]));
        } else if ($termly_report_card->reports_template == 'Template_2') {
            $pdf->loadHTML(view('report-cards.template-2.print', ['items' => $items]));
        } else if ($termly_report_card->reports_template == 'Template_3') {
            //return(view('report-cards.template-3.print', ['items' => $items]));
            $pdf->loadHTML(view('report-cards.template-3.print', ['items' => $items]));
        } else {
            $pdf->loadHTML(view('report-cards.template-1.print', ['items' => $items]));
        } */


        /* 
        
        "id" => 1
        "created_at" => "2024-04-29 18:11:21"
        "updated_at" => "2024-04-29 18:39:43"
        "enterprise_id" => 7
        "title" => "P.4 ARABIC"
        "type" => "Theology"
        "theology_termly_report_card_id" => 13
        "termly_report_card_id" => 16
        "academic_class_id" => null
        "theology_class_id" => 62
        "download_link" => null
        "re_generate" => "Yes"
        "theology_tempate" => "Template_6"
        "secular_tempate" => null
        */
        if ($printing->type == 'Theology') {
            $theologgy_reps = TheologryStudentReportCard::where([
                'theology_termly_report_card_id' => $printing->theology_termly_report_card_id,
                'theology_class_id' => $printing->theology_class_id
            ])->get();
            foreach ($theologgy_reps as $key => $tr) {

                $r = StudentReportCard::where([
                    'student_id' => $tr->student_id,
                    'term_id' => $tr->term_id,
                ])->first();
                $items[] = [
                    'r' => $r,
                    'tr' => $tr,
                ];
                break;
            }
        } else if ($printing->type == 'Secular') {
            $reps = StudentReportCard::where([
                'termly_report_card_id' => $printing->termly_report_card_id,
                'academic_class_id' => $printing->academic_class_id
            ])->get();
            foreach ($reps as $key => $r) {
                $tr = TheologryStudentReportCard::where([
                    'student_id' => $r->student_id,
                    'term_id' => $r->term_id,
                ])->first();
                $items[] = [
                    'r' => $r,
                    'tr' => $tr,
                ];
                //break;
            }
        }

        //check if $items is empty
        if (count($items) == 0) {
            die("Nothing to print.");
        }


        $pdf->loadHTML(view('report-cards.template-6.print', [
            'items' => $items,
            'report_type' => $printing->type,
        ]));

        return $pdf->stream();


        die("Nothing to print.");

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


    //fixed-asset-prints
    public function fixed_asset_prints(Request $req)
    {
        $pdf = App::make('dompdf.wrapper');
        $data = [];
        $report = null;
        if (isset($_GET['id'])) {
            $report = FixedAssetPrint::find($_GET['id']);
        }

        if ($report == null) {
            die("Report not found.");
        }
        $conds = [];
        $conds['enterprise_id'] = $report->enterprise_id;

        if (
            ($report->start_date != null) &&
            (strlen($report->start_date) > 0)
        ) {
            $conds['created_at'] = ['>=', $report->start_date];
        }

        if (
            ($report->end_date != null) &&
            (strlen($report->end_date) > 0)
        ) {
            $conds['created_at'] = ['<=', $report->end_date];
        }

        $recs = FixedAsset::where($conds)->get();
        $html = $report->name;
        foreach ($recs as $key => $value) {
            $barcode = url('storage' . $value->barcode);
            $html .= '<br><br><img style="width: 500px;" src="' . $barcode . '" />';
        }

        $pdf->loadHTML($html);
        return $pdf->stream();
    }

    // 
}
