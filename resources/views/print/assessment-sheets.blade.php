<?php
use App\Models\MarkRecord;
use App\Models\TheologyMarkRecord;
use App\Models\Utils;
$subject_ids = [];
$subjects_grades = [];
if ($assessment->subjects != null) {
    if (strlen($assessment->subjects) > 4) {
        try {
            $subjects_grades = json_decode($assessment->subjects);
        } catch (\Throwable $th) {
            $subjects_grades = [];
        }
    }
}
if ($subjects == null) {
    $subjects = [];
}

$theology_termly_report = $assessment->get_theology_termly_report_card();

$class = $assessment->get_class();
$stream = $assessment->get_stream();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $assessment->get_title() }}</title>
    {{-- inclide css blade --}}
    @include('print.css')
    <style>
        /* cell dirction vertical */
        .vert {
            writing-mode: vertical-rl;
            transform: rotate(270deg);
            text-align: center;
        }

        .page_break {
            page-break-before: always;
        }


        td {
            /*             padding: 0%!important;
            background-color: red!important; */
            font-size: 12px !important;
        }

        /* make table border black color */
        .table {
            border-collapse: collapse;
        }

        .table th,
        .table td {
            border: 1px solid black !important;
        }
    </style>
</head>

<body>

    <div class="row">
        <table class="w-100">
            <tr>

                <td style="width: 100%">
                    <p class="text-center p-0 m-0 text-uppercase"
                        style="font-size: 24px; font-weight: bold; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif!important; ">
                        {{ $ent->name }}</p>
                    <p class="text-center p font-serif  fs-3 m-0 p-0 mt-0 title-2" style="font-size: 16px;"><b
                            class="m-0 p-0">{{ $ent->address }}</b>
                    </p>
                    <p class="text-center p font-serif mt-0 title-2 mb-0" style="font-size: 16px;"><b>EMAIL:</b>
                        {{ $ent->email }}</p>
                    <p class="text-center p font-serif mt-0 mb-0 title-2" style="font-size: 16px;"><b>TELEPHONE
                            NUMBER:</b>
                        {{ $ent->phone_number }} </p>

                </td>
                <td>
                    <img style="width: 80px;" src="{{ public_path('storage/' . $ent->logo) }}">
                </td>
            </tr>
        </table>
    </div>
    <hr style="background-color: {{ $ent->color }}; height: 6px; padding: 0x; margin: 0px;" class="mt-1">
    <hr style="background-color: black; height: 1px;" class="mt-1 mb-1">
    <p class="text-center p font-serif  fs-3 m-0 p-0 mt-0 mb-2" style="font-size: 1.5rem">
        <u><b>{{ $assessment->title }}</b></u>
    </p>

    <h2>Performance Analysis Summary</h2>
    <table class="table table-bordered">
        <tbody>
            <tr>
                <td class="p-1"> @include('print.title-detail', [
                    't' => 'Term',
                    'd' => 'Term ' . $assessment->term->name_text,
                ])</td>

                @if ($class != null)
                    <td class="p-1"> @include('print.title-detail', [
                        't' => 'Class',
                        'd' => $class->name_text ?? '-',
                    ])</td>
                @else
                    <td class="p-1"> @include('print.title-detail', [
                        't' => 'Class',
                        'd' => '-',
                    ])</td>
                @endif

                @if ($stream != null)
                    <td class="p-1"> @include('print.title-detail', [
                        't' => 'Stream',
                        'd' => $stream->name ?? '-',
                    ])</td>
                @else
                    <td class="p-1"> @include('print.title-detail', [
                        't' => 'Stream',
                        'd' => '-',
                    ])</td>
                @endif
                <td class="p-1" colspan='2'> @include('print.title-detail', [
                    't' => 'Class Teacher',
                    'd' => /* $assessment->name_of_teacher ?? */ '-',
                ])</td>
                <td class="p-1"> @include('print.title-detail', [
                    't' => 'Generated on',
                    'd' => Utils::my_date_3($assessment->updated_at),
                ])</td>
            </tr>
            <tr>
                <td class="p-1"> @include('print.title-detail', [
                    't' => 'Total Students',
                    'd' => $assessment->total_students,
                ])</td>
                <td class="p-1"> @include('print.title-detail', [
                    't' => 'first grades',
                    'd' => $assessment->first_grades,
                ])</td>
                <td class="p-1"> @include('print.title-detail', [
                    't' => 'second grades',
                    'd' => $assessment->second_grades,
                ])</td>
                <td class="p-1"> @include('print.title-detail', [
                    'd' => $assessment->third_grades,
                    't' => 'third grades',
                ])</td>
                <td class="p-1"> @include('print.title-detail', [
                    'd' => $assessment->fourth_grades,
                    't' => 'fourth grades',
                ])</td>
                <td class="p-1"> @include('print.title-detail', [
                    'd' => $assessment->x_grades,
                    't' => 'U',
                ])</td>
            </tr>
        </tbody>
    </table>

    {{-- 
/* dd($subject_ids);
    +"id": 1069
    +"name": "English"
    +"d1": 31
    +"d2": 65
    +"c3": 39
    +"c4": 21
    +"c5": 11
    +"c6": 1
    +"p7": 4
    +"f9": 5
    +"x": 0
*/
--}}

    <table class="table table-bordered">
        <thead>
            <th>SUBJECT/GRADES</th>
            <th class="p-0 text-center">D1</th>
            <th class="p-0 text-center">D2</th>
            <th class="p-0 text-center">C3</th>
            <th class="p-0 text-center">C4</th>
            <th class="p-0 text-center">C5</th>
            <th class="p-0 text-center">C6</th>
            <th class="p-0 text-center">P7</th>
            <th class="p-0 text-center">P8</th>
            <th class="p-0 text-center">F9</th>
            <th class="p-0 text-center">X</th>
        </thead>
        <tbody>
            @foreach ($subjects_grades as $sub)
                <tr>
                    <td class="p-0 text-center">{{ $sub->name }}</td>
                    <td class="p-0 text-center">{{ $sub->d1 }}</td>
                    <td class="p-0 text-center">{{ $sub->d2 }}</td>
                    <td class="p-0 text-center">{{ $sub->c3 }}</td>
                    <td class="p-0 text-center">{{ $sub->c4 }}</td>
                    <td class="p-0 text-center">{{ $sub->c5 }}</td>
                    <td class="p-0 text-center">{{ $sub->c6 }}</td>
                    <td class="p-0 text-center">{{ $sub->p7 }}</td>
                    <td class="p-0 text-center">{{ $sub->p8 }}</td>
                    <td class="p-0 text-center">{{ $sub->f9 }}</td>
                    <td class="p-0 text-center">{{ $sub->x }}</td>
                </tr>
            @endforeach
    </table>

    <h2 class="page_break" style="page-break-before: always;">Detailed Summary</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th class="p-1"><b>Sn.</b></th>
                <th class="p-1"><b>Name</b></th>
                @foreach ($subjects as $sub)
                    <?php ?>
                    <th class="  p-1  text-center" colspan='2'>{{ $sub->short_name() }}</th>
                @endforeach
                <th class="p-1  text-center">Total Marks</th>
                <th class="p-1  text-center">AGGR</th>
                <th class="p-1  text-center">GRADE</th>
                <th class="p-1 text-center">POSITION</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $i = 0;
            ?>
            @foreach ($reportCards as $reportCard)
                <?php
                $i++;
                if ($i == 5) {
                    // break;
                }
                ?>
                <tr>
                    <td class="p-1">{{ $i }}</td>
                    <td class="p-1">{{ $reportCard->student_text }}</td>
                    @foreach ($subjects as $sub)
                        <?php
                        if ($assessment->target == 'Theology') {
                            $records = TheologyMarkRecord::where([
                                'term_id' => $reportCard->term_id,
                                'theology_class_id' => $reportCard->theology_class_id,
                                'administrator_id' => $reportCard->student_id,
                                'theology_subject_id' => $sub->id,
                            ])
                                ->orderBy('id', 'desc')
                                ->get();
                        } else {
                            $records = MarkRecord::where([
                                'termly_report_card_id' => $assessment->termly_report_card_id,
                                'academic_class_id' => $assessment->academic_class_id,
                                'administrator_id' => $reportCard->student_id,
                                'subject_id' => $sub->id,
                            ])->get();
                        }
                        $rec = null;
                        if ($records->count() > 0) {
                            $rec = $records->first();
                        }
                        
                        ?>

                        @if ($rec == null)
                            <td class="p-0 text-center">-</td>
                            <td class="p-0 text-center">-</td>
                        @else
                            <td class="p-0 text-center">{{ $rec->total_score_display }}</td>
                            <td class="p-0 text-center">{{ $rec->aggr_name }}</td>
                        @endif
                    @endforeach
                    <th class="text-center p-0">{{ $reportCard->total_marks }}</th>
                    <th class="text-center p-0">{{ $reportCard->average_aggregates }}</th>
                    <th class="text-center p-0">{{ $reportCard->grade }}</th>
                    <th class="text-center p-0">{{ $i }}</th>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>

</html>
