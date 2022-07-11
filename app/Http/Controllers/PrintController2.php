<?php

namespace App\Http\Controllers;

use App\Models\ReportCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class PrintController2 extends Controller
{

    public static function get_row($t1 = "Title 1", $d1 = "Deatils 1", $t2 = "Title 2", $d2 = "Deatils 2")
    {
        return '<tr>
                    <th class="title-cell" >' . $t1 . '</th>
                    <td>' . $d1 . '</td> 
                    <th class="title-cell">' . $t2 . '</th>
                    <td>' . $d2 . '</td> 
                </tr>';
    }

    public function index()
    {

        $r = new ReportCard();
        $data = '<link type="text/css" href="' . url('assets/bootstrap.css') . '" rel="stylesheet" />';
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
                        <td class="fs-5">NAME: <b>Muhindo Mubaraka</b><td>
                        <td class="fs-5 ">SEX: <b>Male</b> <td>
                        <td class="fs-5 text-right">REG No.: <b>U1211</b> <td>
                    </tr>        
                    <tr>
                        <td class="fs-5">CLASS: <b>S.6 Lion</b><td> 
                        <td class="fs-5 ">Aggregates.: <b>12</b> <td>
                        <td class="fs-5 text-right">Grade: <b>B</b> <td>
                    </tr>        
                </table>';

        $data .= '<table class="bordered-table" >
                    <thead>
                        <tr>
                            <th>SUBJECTS<th>
                            <th>B.O.T (30)<th>
                            <th>M.O.T (30)<th>
                            <th>E.O.T (100%)<th>
                            <th>Grade<th>
                            <th>Aggr<th>
                            <th>Remarks<th>
                            <th>Initials<th>
                        </tr>        
                    </thead>       
                    <tbody>
                        <tr>
                            <th>English<th>
                            <td>23<td>
                            <td>46<td>
                            <td>69<td>
                            <td>C<td>
                            <td>3<td>
                            <td>V.Good<td>
                            <td>M. Muhindo<td>
                        </tr>        
                        <tr>
                            <th>Mathematics<th>
                            <td>23<td>
                            <td>46<td>
                            <td>69<td>
                            <td>C<td>
                            <td>3<td>
                            <td>V.Good<td>
                            <td>M. Muhindo<td>
                        </tr>        
                        <tr>
                            <th>Geography<th>
                            <td>23<td>
                            <td>46<td>
                            <td>69<td>
                            <td>C<td>
                            <td>3<td>
                            <td>V.Good<td>
                            <td>M. Muhindo<td>
                        </tr>        
                        <tr>
                            <th>Biology<th>
                            <td>23<td>
                            <td>46<td>
                            <td>69<td>
                            <td>C<td>
                            <td>3<td>
                            <td>V.Good<td>
                            <td>M. Muhindo<td>
                        </tr>        
                    </tbody>       
            </table>';



        $pdf = App::make('dompdf.wrapper');
        $pdf->loadHTML($data);
        return $pdf->stream();
    }

    // 
}
