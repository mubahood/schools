<?php
use App\Models\AcademicClass;
use App\Models\SchoolFeesDemand;
use App\Models\Utils;

$month = date('F');
$year = date('Y');
$active_term = $ent->active_term();

if ($demand->message_4 != null && $demand->message_4 != '') {
    $month = date('F', strtotime($demand->message_4));
    $year = date('Y', strtotime($demand->message_4));
}

?>
<style>
    .my-bordered-table {
        border-collapse: collapse;
        width: 100%;
        border: solid black 2px;
        padding: 0;
        margin: 0;
    }

    .my-bordered-table th,
    .my-bordered-table td {
        border: solid black 2px;
        padding: 0;
        margin: 0;
        text-align: center;
        font-size: 10px;
    }

    .my-bordered-table th {
        padding: 0;
        margin: 0;
        font-size: 10px;
        font-weight: bold;
        padding-bottom: 3px;
    }
</style>
<div class="p-2 m-0" style="border: solid black .2rem;">
    <p class="p-0 m-0 fs-16 lh-14 text-center" style="font-size: 14px;"><b>{{ strtoupper($ent->name) }}</b></p>

    <div class="text-center">
        <p class="p-p m-0 pl-1 pr-1 mt-1 pb-1 fs-14"
            style="border: solid black 2px; display: inline-block; font-size: 14px;"><b>SCHOOL GATE PASS</b></p>
    </div>

    <p class="fs-14 text-uppercase mt-1 mb-2 mt-2 " style=" font-size: 12px; line-height: 1.1">
        NAME: <u><b>&nbsp;{{ $item->owner->name }}&nbsp;</b></u> CLASS:
        <u><b>&nbsp;{{ $item->owner->current_class->short_name }}
    </p>
    <p class="fs-14 text-uppercase mt-1 mb-2 " style=" font-size: 14px; line-height: 1.1">
        SCHOOL FEES BALANCE: <b class=" bg-dark text-white"> {{ $balance }} </b>
    </p>
    <p class="fs-14 text-uppercase mt-1 mb-2 " style=" font-size: 14px; line-height: 1.1">

        </b> EXIPIRY DATE: <b><u>{{ $demand->message_4 }}</u></b>
    </p>
    <p class="m-0 mt-2">Signature: __________________________</p>
</div>
