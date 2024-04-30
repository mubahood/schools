<?php
if (!isset($isBlank)) {
    $isBlank = false;
}

$ent = null;
if (isset($items[0])) {
    if (isset($items[0]['r'])) {
        $ent = $items[0]['r']->ent;
    } elseif (isset($items[0]['tr'])) {
        $ent = $items[0]['tr']->ent;
    }
}
if ($ent == null) {
    echo('Ent not found.');
    dd($items);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="{{ public_path('css/bootstrap-print.css') }}">
    <style>
        body {
            background: #FFF;
            font-family: Arial;
        }

        @page {
            size: A4 portrait;
        }

        * {
            margin: 0;
            padding: 0;
        }

        article {
            page-break-after: always;
        }

        article:last-child {
            page-break-after: avoid;
        }

        body {
            display: table-cell;
            vertical-align: middle;
        }

        .title-2 {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI",
                Roboto, Oxygen, Ubuntu, Cantarell, "Open Sans", "Helvetica Neue",
                sans-serif;
            line-height: 1rem;
        }

        .value {
            font-family: "Arial";
            font-size: 14px;
            color: black;
            font-weight: 800;
            margin: 0;
            text-transform: uppercase;
        }

        p,
        h1,
        h2,
        h3,
        h4,
        body {
            font-family: Arial, Helvetica, sans-serif "Helvetica Neue", sans-serif;
            font-size: 16px;
        }

        h2,
        .h2 {
            font-size: 16px;
            font-weight: 600;
        }

        .marks-table {
            font-family: sans-serif;
        }

        .marks-table thead th {
            font-size: 12px;
            font-weight: 600;
            color: black;
            padding: 0%;
            margin: 0%;
            padding-top: 3px;
            padding-bottom: 3px;
        }

        .marks th,
        .marks-1 th {
            font-size: 12px;
            padding: 0%;
            margin: 0%;
            padding-left: 10px;
        }

        .marks td {
            padding: 0%;
            margin: 0%;
            text-align: center;
            font-size: 12px;

            font-family: "Arial";
            color: black;
            font-weight: 800;
        }

        .marks-1 td {
            padding: 0%;
            margin: 0%;
            text-align: start;
            font-size: 12px;
            font-family: Arial, Helvetica, sans-serif;
        }

        .marks .remarks {
            text-align: left;
            padding-left: 2px;
            font-family: "Courier New", Courier;
        }

        .marks-table {
            border: solid 2px black !important;
        }

        .marks-table thead th {
            border-bottom: solid 1px black !important;
        }

        .grade-table {
            font-size: 12px;
            padding: 0px;
        }

        .grade-table td,
        .grade-table th,
        td {
            padding: 2px;
        }

        .grade-table tbody td {
            font-size: 14px !important;
            padding: 1px;
        }

        .class-teacher,
        .class-teacher .comment {
            font-size: 14px;
            text-align: justify;
        }

        .comment {
            border-bottom: 1px dotted;
        }

        .summary .value {
            border-bottom: 1px dotted;
        }

        .scale-title {
            background-color: black !important;
        }

        .bg-black {
            background-color: black;
            color: white;
        }

        body {
            padding-left: 10px;
            padding-right: 10px;
            padding-top: 5px;
            border: 4px solid <?=$ent->color ?>;
            margin: 20px;
            border-radius: 15px;
            font-family: sans-serif;
            font-size: 12px;
        }

        .text-primary {
            color: <?=$ent->color ?> !important;
        }

        .bg-primary {
            background-color: <?=$ent->color ?> !important;
        }

        p {
            font-size: 12px;
            padding: 0%;
            margin: 0%;
            font-family: 'sans-serif';
        }
    </style>

</head>


<body>
    @foreach ($items as $item)
        @include('report-cards.template-6.print-layout', [
            'report_type' => $report_type,
            'r' => $item['r'],
            'tr' => $item['tr'],
        ])
    @endforeach


</body>


</html>
