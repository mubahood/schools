<?php

namespace App\Http\Controllers;

use App\Models\AcademicClass;
use App\Models\AcademicClassSctream;
use App\Models\AssessmentSheet;
use App\Models\Enterprise;
use App\Models\FixedAsset;
use App\Models\FixedAssetPrint;
use App\Models\MarkRecord;
use App\Models\ReportCard;
use App\Models\ReportCardPrint;
use App\Models\SecondaryReportCard;
use App\Models\StudentHasClass;
use App\Models\StudentReportCard;
use App\Models\TermlyReportCard;
use App\Models\TheologryStudentReportCard;
use App\Models\TheologyClass;
use App\Models\TheologyStudentReportCardItem;
use App\Models\User;
use App\Models\UserBatchImporter;
use App\Models\Utils;
use Encore\Admin\Auth\Database\Administrator;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Maatwebsite\Excel\Facades\Excel;

class ReportCardsPrintingController extends Controller
{


    public function assessment_sheets_generate(Request $req)
    {
        $id = $req->id;
        $assessment = AssessmentSheet::find($id);
        $assessment = AssessmentSheet::prepare($assessment);
        $assessment->save();


        $class = $assessment->has_class;
        $ent = Enterprise::find($assessment->enterprise_id);


        $conds = [];
        if ($assessment->type == "Class") {
            $assessment->academic_class_sctream_id = null;
        } else {
            $stream = AcademicClassSctream::find($assessment->academic_class_sctream_id);
            if ($stream == null) {
                throw new Exception("Stream not found", 1);
            }
            $assessment->academic_class_id = $stream->academic_class_id;
            $conds['stream_id'] = $assessment->academic_class_sctream_id;
        }
        $class = AcademicClass::find($assessment->academic_class_id);
        if ($class == null) {
            throw new Exception("Class not found", 1);
        }
        $conds['termly_report_card_id'] = $assessment->termly_report_card_id;
        $conds['academic_class_id'] = $assessment->academic_class_id;


        $reportCards = StudentReportCard::where($conds)
            ->orderBy('total_marks', 'desc')
            ->get();

        $assessment->generated = "Yes";
        $name = $assessment->title;
        $name = str_replace(' ', '-', $name);
        $name = str_replace('---', '-', $name);
        $name = str_replace('--', '-', $name);
        $name = $assessment->id . "-" . $name . '.pdf';
        $name = strtolower($name);
        $store_file_path = public_path('storage/files/' . $name);


        $assessment->pdf_link = 'files/' . $name;
        $assessment->save();

        $pdf = App::make('dompdf.wrapper');
        $pdf->setPaper('A4', 'landscape');

        //check if file exists
        if (file_exists($store_file_path)) {
            unlink($store_file_path);
        }

        $pdf->loadHTML(view('print.assessment-sheets', [
            'assessment' => $assessment,
            'subjects' => $class->subjects,
            'reportCards' => $reportCards,
            'ent' => $ent
        ]));
        $output = $pdf->output();
        try {
            file_put_contents($store_file_path, $output);
        } catch (\Exception $e) {
            die($e->getMessage());
        }

        $url = url('storage/files/' . $name);

        $html = '<h1>Assessment Sheet ready!</h1>';
        $html .= '<h2>' . $assessment->title . '</h2>';
        $html .= '<h3>' . $class->name . '</h3>';
        //download link open 
        $html .= '<a href="' . $url . '">Download</a>';
        return $html;
    }
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

        $min_count = $printing->min_count;
        $max_count = $printing->max_count;
        $pdf = App::make('dompdf.wrapper');
        $i = 0;
        /*  if ($printing->type == 'Theology') {
            $theologgy_reps = TheologryStudentReportCard::where([
                'theology_termly_report_card_id' => $printing->theology_termly_report_card_id,
                'theology_class_id' => $printing->theology_class_id
            ])->get();
            foreach ($theologgy_reps as $key => $tr) {
                if ($i < $min_count) {
                    continue;
                }
                if ($i > $max_count) {
                    break;
                }
                $i++;
                $r = StudentReportCard::where([
                    'student_id' => $tr->student_id,
                    'term_id' => $tr->term_id,
                ])->first();
                $items[] = [
                    'r' => $r,
                    'tr' => $tr,
                ];
            }
        } else if ($printing->type == 'Secular') {
        }
 */

        if ($printing->type == 'Theology') {
            $theo_class = TheologyClass::find($printing->theology_class_id);
            if ($theo_class == null) {
                die("Theology class not found.");
            }
            $reports = TheologryStudentReportCard::where([
                'theology_termly_report_card_id' => $printing->theology_termly_report_card_id,
                'theology_class_id' => $printing->theology_class_id
            ])->get();
            $student_ids = [];
            foreach ($reports as $key => $r) {
                $student_ids[] = $r->student_id;
            }

            $reps = StudentReportCard::where([
                'termly_report_card_id' => $printing->termly_report_card_id,
            ])
                ->whereIn('student_id', $student_ids)
                ->orderBy('id', 'asc')
                ->get();
        } else {
            $reps = StudentReportCard::where([
                'termly_report_card_id' => $printing->termly_report_card_id,
                'academic_class_id' => $printing->academic_class_id
            ])
                ->orderBy('id', 'asc')
                ->get();
        }


        foreach ($reps as $key => $r) {
            if ($i < $min_count) {
                continue;
            }
            if ($i > $max_count) {
                break;
            }
            $i++;
            $tr = TheologryStudentReportCard::where([
                'student_id' => $r->student_id,
                'term_id' => $r->term_id,
                'theology_termly_report_card_id' => $printing->theology_termly_report_card_id,
            ])->first(); 
            $items[] = [
                'r' => $r,
                'tr' => $tr,
            ];
            //break;
        }

        //check if $items is empty
        if (count($items) == 0) {
            die("Nothing to print.");
        }
        $printing->secular_tempate = 'Template_3';
        if ($printing->secular_tempate == 'Template_3') {

            if (isset($_GET['html'])) {
                return view('report-cards.template-3.print', [
                    'items' => $reps,
                    'ent' => $printing->enterprise,
                    'report_type' => $printing->type,
                    'min_count' => $printing->min_count,
                    'max_count' => $printing->max_count,
                ]);
            }
            $pdf->loadHTML(view('report-cards.template-3.print', [
                'items' => $reps,
                'ent' => $printing->enterprise,
                'report_type' => $printing->type,
                'min_count' => $printing->min_count,
                'max_count' => $printing->max_count,
            ]));
        } elseif ($printing->secular_tempate == 'Template_6') {

            if (isset($_GET['html'])) {
                return view('report-cards.template-6.print', [
                    'items' => $items,
                    'report_type' => $printing->type,
                    'min_count' => $printing->min_count,
                    'max_count' => $printing->max_count,
                ]);
            }

            $pdf->loadHTML(view('report-cards.template-6.print', [
                'items' => $items,
                'report_type' => $printing->type,
                'min_count' => $printing->min_count,
                'max_count' => $printing->max_count,
            ]));
        } else {
            if (isset($_GET['html'])) {
                return view('report-cards.template-6.print', [
                    'items' => $items,
                    'report_type' => $printing->type,
                    'min_count' => $printing->min_count,
                    'max_count' => $printing->max_count,
                ]);
            }
            $pdf->loadHTML(view('report-cards.template-6.print', [
                'items' => $items,
                'report_type' => $printing->type,
                'min_count' => $printing->min_count,
                'max_count' => $printing->max_count,
            ]));
        }


        $name = $printing->title . '-' . $printing->min_count . $printing->max_count . $termly_report_card->name_text;
        $name = str_replace(' ', '_', $name);
        $name = $termly_report_card->id . "-" . $printing->id . "-" . $name;
        $name = $name . '.pdf';
        $store_file_path = public_path('storage/files/' . $name);

        //check if file exists
        if (file_exists($store_file_path)) {
            unlink($store_file_path);
        }

        $output = $pdf->output();
        try {
            file_put_contents($store_file_path, $output);
        } catch (\Exception $e) {
            die($e->getMessage());
        }

        $url = url('storage/files/' . $name);
        $printing->download_link = $url;
        $printing->save();

        $ready_for_download_text = "ready for download! LINK: " . $url;
        return '<a href="' . $url . '">' . $ready_for_download_text . '</a>';

        return redirect($url);

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
            foreach (
                StudentReportCard::where([
                    'academic_class_id' => $icalss_id,
                    'term_id' => $term_id,
                    /*                 'termly_report_card_id' => $termly_report_card_id, */
                ])->get() as $r
            ) {


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



    public function report_card_individual_printings(Request $req)
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

        $min_count = $printing->min_count;
        $max_count = $printing->max_count;
        $pdf = App::make('dompdf.wrapper');
        $i = 0;

        $reps = StudentReportCard::where([
            'termly_report_card_id' => $printing->termly_report_card_id,
            'academic_class_id' => $printing->academic_class_id
        ])
            ->orderBy('id', 'asc')
            ->get();
        foreach ($reps as $key => $r) {
            if ($i < $min_count) {
                continue;
            }
            if ($i > $max_count) {
                break;
            }
            $i++;
            $r->report_card_print_id = $printing->id;
            $r->save();

            $msg = "";
            try {
                $r->download_self();
                $msg = $i . ". Generated for " . $r->owner->name . " " . $r->owner->id . " Successfully";
            } catch (\Exception $e) {
                $msg = $i . ". Failed for " . $r->owner->name . " " . $r->owner->id . " - because: " . $e->getMessage() . "";
            }
            $download_link = url('storage/files/' . $r->pdf_url);
            echo '<p>' . $msg . ' <a target="_blank" href="' . $download_link . '"> Download</a></p>';
        }
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

    public function data_import(Request $req)
    {
        $m = UserBatchImporter::find($req->id);
        $file_path = Utils::docs_root() . 'storage/' . $m->file_path;
        if (!file_exists($file_path)) {
            die("$file_path File does not exist.");
        }

        $cla = AcademicClass::find($m->academic_class_id);
        if ($cla == null) {
            die("Class not found.");
        }


        $array = Excel::toArray([], $file_path);

        $i = 0;
        $enterprise_id = $m->enterprise_id;
        $_duplicates = '';
        $update_count = 0;
        $import_count = 0;
        $is_first = true;
        foreach ($array[0] as $key => $v) {
            if ($is_first) {
                $is_first = false;
                continue;
            }


            $i++;
            if (
                (!isset($v[0])) ||
                (!isset($v[1])) ||
                ($v[0] == null)
            ) {
                $update_count++;
                continue;
            }
            $user_id = trim($v[0]);
            $u = Administrator::where([
                'enterprise_id' => $enterprise_id,
                'user_id' => $user_id
            ])->first();
            if ($u != null) {
                $msg = "Skipped $user_id because already eixst<br>";
                echo $msg;
                $update_count++;
                continue;
            }

            $u = new Administrator();
            $u->user_id = $user_id;
            $u->school_pay_account_id = $user_id;
            $u->school_pay_payment_code = $user_id;
            $u->current_class_id = $cla->id; //CLASS ID
            $u->username = $enterprise_id . $user_id;
            $u->password = password_hash('4321', PASSWORD_DEFAULT);
            $u->enterprise_id = $enterprise_id;
            $u->avatar = url('user.png');
            $u->first_name = trim($v[1]);
            $u->given_name = "";
            $u->last_name = "";
            if (
                isset($v[2]) &&
                strlen($v[2]) > 2
            ) {
                $u->given_name = trim($v[2]);
            }

            if (
                isset($v[3]) &&
                strlen($v[3]) > 2
            ) {
                $u->last_name = trim($v[3]);
            }

            if (strlen($u->last_name) < 2) {
                if (strlen($u->given_name) < 2) {
                    $names = explode(' ', $u->first_name);
                    if (isset($names[0])) {
                        $u->first_name = $names[0];
                    }
                    if (isset($names[1])) {
                        $u->last_name = $names[1];
                    }
                    if (isset($names[3])) {
                        $u->given_name = $names[3];
                    }
                }
            }

            $u->name = $u->first_name;
            if (strlen($u->given_name) > 2) {
                $u->name .= ' ' . $u->given_name;
            }
            if (strlen($u->last_name) > 2) {
                $u->name .= ' ' . $u->last_name;
            }

            if (strlen(trim($u->name)) < 4) {
                $update_count++;
                continue;
            }


            if (isset($v[4])) {
                $u->sex = trim($v[4]);
                if ($u->sex != null) {
                    if (strlen($u->sex) > 0) {
                        if (strtoupper(substr($u->sex, 0, 1)) == 'M') {
                            $u->sex = 'Male';
                        } else {
                            $u->sex = 'Female';
                        }
                    }
                }
            }

            $u->user_type = 'student';

            try {
                $u->save();
                $import_count++;
                echo "Imported {$u->name} SUCCESSFULLY! <br>";
            } catch (\Throwable $th) {
                //throw $th;
            }


            if ($u != null) {
                $class = new StudentHasClass();
                $class->enterprise_id = $enterprise_id;
                $class->academic_class_id = $m->academic_class_id;
                $class->administrator_id = $u->id;
                $class->academic_year_id = $cla->academic_year_id;
                $class->stream_id = 0;
                $class->done_selecting_option_courses = 0;
                $class->optional_subjects_picked = 0;
                try {
                    $class->save();
                } catch (\Throwable $th) {
                    //throw $th;
                }
            }
        }
        $m->description = "Imported $import_count new students and skipped $update_count students.";
        $m->save();
    }
}
