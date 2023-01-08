<?php
$link = public_path('css/bootstrap-print.css');
use App\Models\Utils;
use App\Models\User;
use App\Models\Document;
use App\Models\Enterprise;
use App\Models\AcademicClass;
$student = User::find($_GET['id']);
if ($student == null) {
    throw new Exception('Studnet not found.', 1);
}

$template = Document::where([
    'enterprise_id' => $student->enterprise_id,
    'name' => 'Admission letter',
])->first();

$ent = Enterprise::find($student->enterprise_id);

if ($template == null) {
    throw new Exception('Admission Template not found.', 1);
}
if ($ent == null) {
    throw new Exception('School not found.', 1);
}
$class = AcademicClass::find($student->current_class_id);
if ($class == null) {
    throw new Exception('Class not found.', 1);
}

$logo_link = public_path('/storage/' . $ent->logo);

$template->body = str_replace('[STUDENT_NAME]', $student->name, $template->body);
$template->body = str_replace('[STUDENT_SCHOOL_PAY_CODE]', $student->school_pay_payment_code, $template->body);
$template->body = str_replace('[SCHOOL_NAME]', $ent->name, $template->body);
$template->body = str_replace('[STUDENT_CLASS]', $class->name, $template->body);
$template->body = str_replace('background-color: rgb(249, 242, 244);', ' ', $template->body);
$template->body = str_replace('color: rgb(199, 37, 78);', ' ', $template->body);

$requirements_rows = '';
$requirements_row_count = 0;
$requirements_total = 0;
foreach ($class->academic_class_fees as $fee) {
    $requirements_row_count++;
    $requirements_total += $fee->amount;
    $requirements_rows .=
        "<tr>
            <th width=\"6%\">$requirements_row_count</th>
            <td>{$fee->name}</td>
            <td class=\"text-right\"> UGX " .
        number_format($fee->amount) .
        "/=</td>
        </tr>";
}

foreach ($student->active_term_services() as $fee) {
    $requirements_row_count++;
    $requirements_total += $fee->total;
    $requirements_rows .=
        "<tr>
            <th width=\"6%\">$requirements_row_count</th>
            <td>{$fee->service->name} ({$fee->quantity})</td>
            <td class=\"text-right\"> UGX " .
        number_format($fee->total) .
        "/=</td>
        </tr>";
}

$requirements_row_table = '<h4 class=" mb-2"><u>REQUIREMENTS</u></h4>';

$requirements_rows .=
    "<tr>
            <th colspan=\"2\">TOTAL</th> 
            <th class=\"text-right\"> UGX " .
    number_format($requirements_total) .
    "/=</th>
        </tr>";

$requirements_row_table .=
    '<table class="table table-bordered table-sm "><tr>
                            <th>S/n</th>
                            <th class="text-center">Requirement</th>
                            <th class="text-center">Fee</th>
                            </tr><tbody>' .
    $requirements_rows .
    '</tbody></table>';

$template->body = str_replace('[REQUIREMENTS_TABLE]', $requirements_row_table, $template->body);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="{{ public_path('css/bootstrap-print.css') }}">
    <title>Addmission letter</title>
    @if ($template->print_water_mark == 1)
        <style>
            body::before {
                content: "";
                position: absolute;
                top: 0;
                right: 0;
                bottom: 0;
                left: 0;
                z-index: -1;
                background-image: url({{ $logo_link }});
                background-size: cover;
                background-position: center;
                opacity: 0.15;
                background-repeat: no-repeat;
                background-position: center;
                background-size: 80%;
                opacity: 0.15;
            }
        </style>
    @endif

</head>

<body>

    @if ($template->print_hearder == 1)
        <table class="w-100 ">
            <tbody>
                <tr>
                    <td style="width: 15%;" class="">
                        <img class="img-fluid" src="{{ $logo_link }}" alt="{{ $ent->name }}">
                    </td>
                    <td class=" text-center">
                        <h1 class="h3 ">{{ $ent->name }}</h1>
                        <p class="mt-1">Address {{ $ent->address }}, {{ $ent->p_o_box }}</p>
                        <p class="mt-0">Email: {{ $ent->email }}</p>
                        <p class="mt-0">Tel: <b>{{ $ent->phone_number }}</b> , <b>{{ $ent->phone_number_2 }}</b></p>
                    </td>
                    <td style="width: 15%;"><br></td>
                </tr>
            </tbody>
        </table>

        <hr style="border-width: 3px; color: black; border-color: black;" class="my-3">
    @else
        <div style="height: 4cm;"></div>
    @endif


    <p class="text-right"><b>{{ Utils::my_date(time()) }}</b></p>
    <h2 class="text-center h4 mb-4"><u>Admission Letter</u></h2>

    {{-- {{$template->body}} --}}
    {!! $template->body !!}
</body>

</html>
