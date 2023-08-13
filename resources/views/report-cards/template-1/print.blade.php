<?php
if (!isset($isBlank)) {
    $isBlank = false;
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <link rel="stylesheet" href="{{ public_path('css/bootstrap-print.css') }}">


    @if (true)
        <link type="text/css" href="{{ public_path('assets/print-portrait.css') }}" rel="stylesheet" />
    @else
        <link type="text/css" href="{{ public_path('assets/print.css') }}" rel="stylesheet" />
    @endif


    <style>
        .page-break {
            page-break-after: always;
        } 
        a,
        b,
        p,
        span,
        div,
        td,
        tr,
        th {
            font-size: 12px;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif !important;
        }
        .value{
            font-weight: bold;
            font-size: 12px; 
            border-bottom: dotted 2px #000;
        }
    </style>

</head>

<body>

    @foreach ($items as $item)
        @include('report-cards.template-1.print-layout', ['r' => $item])
    @endforeach

</body>


</html>
