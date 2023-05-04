<?php

if (!isset($isBlank)) {
    $isBlank = false;
}
if (!isset($recs[0])) {
    die('Reports not selected.');
}
$portrait = false;

if ($recs[0]['r']->ent->has_theology != 'Yes') {
    $portrait = true;
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <link rel="stylesheet" href="{{ public_path('css/bootstrap-print.css') }}">


    @if ($portrait)
        <link type="text/css" href="{{ public_path('assets/print-portrait.css') }}" rel="stylesheet" />
    @else
        <link type="text/css" href="{{ public_path('assets/print.css') }}" rel="stylesheet" />
    @endif



</head>

<body>


    @php
        $x = 1;
    @endphp
    @foreach ($recs as $item)
        @php
            /*  $x++;
       if($x > 2){
        break;
       } */
            $item['isBlank'] = $isBlank;
        @endphp
        @if ($item['r']->academic_class->class_type == 'Nursery')
            @include('report-cards.print-nusery-layout', $item)
        @else
            @if ($portrait)
                @include('report-cards.print-portrait-layout', $item)
            @else
                @include('report-cards.print-layout', $item)
            @endif
        @endif
    @endforeach

    {{-- @include('report-cards.print-layout')
    @include('report-cards.print-layout') --}}

</body>


</html>
