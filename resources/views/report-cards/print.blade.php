<!DOCTYPE html>
<html lang="en">

<head>

    <head>
        <meta charset="utf-8">
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    </head>
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
