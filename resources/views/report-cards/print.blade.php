<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link type="text/css" href="{{ url('assets/bootstrap.css') }}" rel="stylesheet" />
    <link type="text/css" href="{{ url('assets/print.css') }}" rel="stylesheet" />
</head>

<body>


    @foreach ($recs as $item)
        @if ($item['r']->academic_class->class_type == 'Nursery')
            @include('report-cards.print-nusery-layout', $item)
        @else
            @include('report-cards.print-layout', $item)
        @endif
    @endforeach

    {{-- @include('report-cards.print-layout')
    @include('report-cards.print-layout') --}}

</body>


</html>
