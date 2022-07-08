<?php

namespace App\Http\Controllers;

use App\Models\District;
use App\Models\Movement;
use App\Models\SubCounty;
use App\Models\Utils;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Facades\Admin;
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
 
 
        $data = "
            <style>
            @font-face {
                font-family: 'Roboto-Regular';
                font-weight: normal;
                font-weight: 400; 
                font-style: normal;
                font-variant: normal; 
            }
            
            p{
                font-size: 12px;
                padding: 0;
                margin: 0;
                font-family: Roboto-Regular;
            }
            .title-cell{
                width: 25%;
                font-family: Roboto-Regular;
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
            .bordered-table,.bordered-table td{
                border: 1px solid black;
            }
            table{
                  width: 100%;
              }
              </style>
        ";


 

        $data .= 'Romin K.';

 

        $data .= "<br>";






        $pdf = App::make('dompdf.wrapper');
        $pdf->loadHTML($data);
        return $pdf->stream();
    }

    // 
}
