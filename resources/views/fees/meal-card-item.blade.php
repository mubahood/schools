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
    }

    .my-bordered-table th {
        padding: 0;
        margin: 0;
        font-size: 14px;
        font-weight: bold;
        padding-bottom: 3px;
    }
</style>
<div class="p-2 m-0" style="border: solid black .2rem;">
    <p class="p-0 m-0 fs-16 lh-16 text-center"><b>{{ strtoupper($ent->name) }}</b></p>

    <div class="text-center">
        <p class="p-p m-0 pl-1 pr-1 mt-1 pb-1 fs-14" style="border: solid black 2px; display: inline-block;"><b>STUDENT
                MEAL CARD</b></p>
    </div>

    <p class="fs-14 text-uppercase mt-1 mb-2">
        NAME: <u><b>&nbsp;{{ $item->owner->name }}&nbsp;</b></u> CLASS:
        <u><b>&nbsp;{{ $item->owner->current_class->short_name }}&nbsp;</b></u> MONTH: <b><u>{{ $month }}</u></b>
        TERM: <b><u>{{ $active_term->name_text }}</u></b>
    </p>

    <table class="table table-bordered my-bordered-table mt-0" style="border-color: black!important;">
        <tr>
            <th>MON</th>
            <th>TUE</th>
            <th>WED</th>
            <th>THU</th>
            <th>FRI</th>
            <th>SAT</th>
            <th>SUN</th>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
    </table>
    <p class="m-0 mt-2">Signature: __________________________</p>

</div>
