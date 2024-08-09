<?php
use App\Models\MarkRecord;
$subject_ids = [];

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $assessment->title }}</title>
    {{-- inclide css blade --}}
    @include('print.css')
    <style>
        /* cell dirction vertical */
        .vert {
            writing-mode: vertical-rl;
            transform: rotate(270deg);
            text-align: center;
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


    <table class="table table-bordered">
        <thead>
            <tr>
                <th class="p-1"><b>Sn.</b></th>
                <th class="p-1"><b>Name</b></th>
                @foreach ($subjects as $sub)
                    <?php ?>
                    <th class="  p-1">{{ $sub->short_name() }}</th>
                @endforeach
                <th class="p-1">Total Marks</th>
                <th class="p-1">AGGR</th>
                <th class="p-1">GRADE</th>
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
                if ($i == 10) {
                    //break;
                }
                ?>
                <tr>
                    <td class="p-1">{{ $i }}</td>
                    <td class="p-1">{{ $reportCard->student_text }}</td>
                    @foreach ($subjects as $sub)
                        <?php
                        
                        $records = MarkRecord::where([
                            'termly_report_card_id' => $assessment->termly_report_card_id,
                            'academic_class_id' => $assessment->academic_class_id,
                            'administrator_id' => $reportCard->student_id,
                            'subject_id' => $sub->id,
                        ])->get();
                        $rec = null;
                        if ($records->count() > 0) {
                            $rec = $records->first();
                        }
                        
                        ?>
                        {{-- 
        "id" => 61642
        "created_at" => "2024-07-10 16:34:51"
        "updated_at" => "2024-07-20 01:13:25"
        "enterprise_id" => 7
        "termly_report_card_id" => 17
        "term_id" => 41
        "administrator_id" => 2581
        "academic_class_id" => 130
        "academic_class_sctream_id" => 93
        "main_course_id" => 1
        "subject_id" => 1075
        "bot_score" => 0
        "mot_score" => 91
        "eot_score" => 0
        "bot_is_submitted" => "No"
        "mot_is_submitted" => "Yes"
        "eot_is_submitted" => "No"
        "bot_missed" => "Yes"
        "mot_missed" => "Yes"
        "eot_missed" => "Yes"
        "initials" => "DA"
        "remarks" => "Excellent"
        "total_score" => 91
        "total_score_display" => 91
        "aggr_name" => "D1"
        "aggr_value" => 1
                        --}}

                        @if ($rec == null)
                            <td class="p-0 text-center">-</td>
                        @else
                            <td class="p-0 text-center">{{ $rec->total_score_display }}</td>
                        @endif
                    @endforeach
                    <th class="text-center p-0">{{ $reportCard->total_marks }}</th>
                    <th class="text-center p-0">{{ $reportCard->average_aggregates }}</th>
                    <th class="text-center p-0">{{ $reportCard->grade }}</th>
                    <th class="text-center p-0">{{ $reportCard->position }}</th>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>

</html>
