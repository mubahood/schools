<!DOCTYPE html>
<html lang="en">

<head>
 
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
