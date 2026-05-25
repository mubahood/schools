<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="{{ public_path('css/bootstrap-print.css') }}">
    <style>
        @page { size: A4 landscape; margin: 6mm 8mm; }
        body { font-family: Arial, sans-serif; font-size: 11px; }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>
    @foreach ($items as $i => $report)
        @include('progressive-assessment.print-layout', ['report' => $report, 'assessment' => $assessment, 'ent' => $ent])
        @if (!$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach
</body>
</html>
