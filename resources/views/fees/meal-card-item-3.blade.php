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
<div class="p-2 m-0" style="border: solid {{ $ent->sec_color }} .2rem;">

    {{-- $logo --}}
    <table class="w-100">

        <tr>
            <td class="text-left p-0 m-0" style="width: 50px; height: 50px;">
                <img src="{{ $logo }}" alt="logo" style="width: 50px; height: 50px;">
            </td>
            <td class="text-center">
                <p class="p-0 m-0 fs-16 lh-14 text-center" style="font-size: 12px;"><b>{{ strtoupper($ent->name) }}</b>
                </p>
                <p class="p-p m-0 pl-1 pr-1 mt-1 pb-0 pt-0 fs-12 text-center"
                    style="font-weight: 900; background-color: {{ $ent->color }}; color: white; border: solid {{ $ent->color }} 2px; display: inline-block; font-size: 12px;">
                    <b>SCHOOL GATE PASS</b>
                </p>
            </td>
            <td class="text-left p-0 m-0" style="width: 50px; height: 50px;">
                @if (isset($demand->include_student_photos) && $demand->include_student_photos == 'Yes')
                    <div
                        style="width: 50px; height: 50px; background: #fff; display: flex; align-items: center; justify-content: center;
                    vertical-align: middle;  
                    border-radius: 4px; border: 2px solid #000; overflow: hidden;">
                        <img src="{{ $item->owner->avatar }}" alt="student photo"
                            style="max-width: 100%;   object-fit: contain; display: block; vertical-align: middle; width: 100%;">
                    </div>
                @else
                    <div
                        style="width: 50px; height: 50px; background-color: #f0f0f0; display: flex; align-items: center; justify-content: center;">
                        &nbsp;
                    </div>
                @endif
            </td>
        </tr>
    </table>

    <p class="fs-14 text-uppercase mt-1 mb-2 mt-2 " style=" font-size: 12px; line-height: 1.1">
        NAME: <u><b>&nbsp;{{ $item->owner->name }}&nbsp;</b></u> CLASS:
        <u><b>&nbsp;{{ $item->owner->current_class->short_name }} </b></u> MONTH: <b><u>{{ $month }}</u></b>
        BALANCE: <b><u>{{ $balance }}</u></b>
    </p>
    {{--  <p class="fs-14 text-uppercase mt-1 mb-2 " style=" font-size: 14px; line-height: 1.3">
        SCHOOL FEES BALANCE: <b class=" bg-dark text-white"> {{ $balance }} </b>
    </p> --}}
    <p class="fs-14 text-uppercase mt-1 mb-2 " style=" font-size: 14px; line-height: 1.1">

        </b> EXIPIRY DATE: <b><u>{{ $demand->message_4 }}</u></b>
    </p>

    @php
        $sig_path = null;
        if ($ent->bursar_signature && strlen($ent->bursar_signature) > 3) {
            $sig_path = public_path('storage/' . $ent->bursar_signature);
            if (file_exists($sig_path)) {
                $sig_path = asset('storage/' . $ent->bursar_signature);
            } else {
                $sig_path = null;
            }
        }

    @endphp
    @if ($sig_path)
        <p class="text-right p-0 m-0"><b style="color: {{ $ent->color }};">Signature: <img src="{{ $sig_path }}"
                    alt="Signature" style="width: 100px; height: 30px; margin-top: -10px;"> </b></p>
    @else
        <p class="m-0 mt-2" style="color: {{ $ent->color }}; font-weight: 900;">Signature: __________________________
        </p>
    @endif

</div>
