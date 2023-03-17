<?php
$r = $data[0];

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
            border: 4px solid {{ $r->ent->color }};
        }
    </style>



    @if (count($data) > 1)
        <link type="text/css" href="{{ public_path('assets/buck-print.css') }}" rel="stylesheet" />
    @else
        <link type="text/css" href="{{ public_path('assets/secondary_report_cards.css') }}" rel="stylesheet" />
    @endif

</head>

<body>
    @foreach ($data as $d)
        @include('report-cards/secondary_report_cards_items', [
            'r' => $d,
        ])
    @endforeach
</body>

</html>
