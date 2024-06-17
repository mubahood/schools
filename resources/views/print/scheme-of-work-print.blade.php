<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <link rel="stylesheet" href="{{ public_path('/assets/styles.css') }}">
    <link rel="stylesheet" href="{{ public_path('css/bootstrap-print.css') }}">
    <style>
        @page {
            size: A4;
            margin: 24px;
        }

        @media print {
            body {
                margin: 0;
                padding: 0;
            }

            .no-print,
            .no-print * {
                display: none !important;
            }
        }

        /* set orientation horizontal */
        @page {
            size: landscape;
        }
        tbody {
            page-break-inside: avoid;
        }
        table tbody tr td {
            page-break-inside: avoid;
            page-break-after: auto;
            font-size: 12px;
            font-weight: 400;
        }
        table thead tr th {
            font-size: 12px;
            font-weight: 800;
            /* text tansformation */
            text-transform: uppercase;
            padding: 2px;
        }

        /* @media print */
    </style>
</head>

<body>


    <table class="w-100">
        <tr>
            <td style="width: 5%">
                <img style="width: 100%; " src="{{ public_path('storage/' . $ent->logo) }}">
            </td>
            <td>
                <div class="text-center">
                    <p class="fs-26 text-center fw-700 mt-2 text-uppercase  " style="color: {{ $ent->color }};">
                        {{ $ent->name }}</p>
                    <p><i>"{{ $ent->motto }}"</i></p>
                    <p class="fs-14 lh-6 mt-0">TEL: {{ $ent->phone_number }},&nbsp;{{ $ent->phone_number_2 }}, EMAIL:
                        {{ $ent->email }}, WEBSITE: {{ $ent->website }}, {{ $ent->p_o_box }}</p>
                </div>
            </td>
            <td style="width: 5%">
            </td>
        </tr>
    </table>


    <hr style="height: 5px; background-color:  {{ $ent->color }};" class=" my-1 mb-3">
    <p class="fs-20 text-center fw-200 mt-1 text-uppercase black mb-3"><u>
            SCHEME OF WORK FOR {{ $class->name }}, {{ $sub->name }}, Term {{ $term->name_text }} </u></p>

    <table class="mt-1 w-100 table table-bordered">
        <thead>
            <tr style=" border-bottom: 1px black solid;">
                <th class="text-center  p-1" style="width: 5px;">WK</th>
                <th class="text-center  p-1" style="width: 5px;">PDs</th>
                <th class="text-left p-1" style="">Topic</th>
                <th class="text-left p-1" style="">Competence</th>
                <th class="text-center">Methods</th>
                <th class="text-center">Skills</th>
                <th class="text-center">Suggested Activities</th>
                <th class="text-center">Instructional Materials</th>
                <th class="text-center">References</th>
                <th class="text-center">Remarks</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($items as $item)
                <tr>
                    <td class="text-center p-1">{{ $item->week }}</td>
                    <td class="text-center p-1">{{ $item->period }}</td>
                    <td class="text-left p-1">{{ $item->topic }}</td>
                    <td class="text-left p-1">{{ $item->competence }}</td>
                    <td class="text-left p-1">{{ $item->methods }}</td>
                    <td class="text-left p-1">{{ $item->skills }}</td>
                    <td class="text-left p-1">{{ $item->suggested_activity }}</td>
                    <td class="text-left p-1">{{ $item->instructional_material }}</td>
                    <td class="text-left p-1">{{ $item->references }}</td>
                    <td class="text-left p-1">{{ $item->teacher_comment }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>


</body>

</html>
