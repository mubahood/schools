<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link type="text/css" href="{{ url('assets/bootstrap.css') }}" rel="stylesheet" />


    @if ($recs[0]['r']->enterprise_id == 9)
        <link type="text/css" href="{{ url('assets/buck-print.css') }}" rel="stylesheet" />
    @else
        <link type="text/css" href="{{ url('assets/print.css') }}" rel="stylesheet" />
    @endif

</head>

<body>


    @foreach ($recs as $item)
        @if ($item['r']->academic_class->class_type == 'Nursery')
            @include('report-cards.print-nusery-layout', $item)
        @else
            @if ($item['r']->enterprise_id == 9)
                @include('report-cards.print-buck-layout', $item)
            @else
                @include('report-cards.print-layout', $item)
            @endif
        @endif
    @endforeach

    {{-- @include('report-cards.print-layout')
    @include('report-cards.print-layout') --}}

</body>


</html>
