@php
    use App\Models\Utils;
    $logoPath = public_path('storage/' . $ent->logo);
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Fees Structure – {{ $programme->name }}</title>
    <link rel="stylesheet" href="{{ public_path('css/bootstrap-print.css') }}">
    @if ($ent->print_water_mark ?? false)
        <style>
            body::before {
                content: "";
                position: absolute;
                inset: 0;
                background: url('{{ $logoPath }}') no-repeat center/80%;
                opacity: 0.08;
                z-index: -1;
            }
        </style>
    @endif
</head>

<body>

    {{-- Header --}}
    <table class="w-100 mb-3">
        <tr>
            <td style="width:15%">
                <img src="{{ $logoPath }}" style="max-width:100%;" />
            </td>
            <td class="text-center">
                <h2 class="h4 text-uppercase">{{ $ent->name }}</h2>
                <p class="mb-0">{{ $ent->address }} | P.O. Box {{ $ent->p_o_box }}</p>
                <p class="mb-0">Tel: {{ $ent->phone_number }} @if ($ent->phone_number_2)
                        , {{ $ent->phone_number_2 }}
                    @endif
                </p>
                <p class="mb-0">Email: {{ $ent->email }} @if ($ent->website)
                        | Web: {{ $ent->website }}
                    @endif
                </p>
            </td>
            <td style="width:15%"></td>
        </tr>
    </table>
    <hr class="mb-4" style="border-width: 4px; color: black; border-color: {{ $ent->color ?? 'black' }};" />

    {{-- Title + metadata --}}
    <p class="text-right"><small>{{ Utils::my_date(time()) }}</small></p>
    <h3 class="text-center mb-4">Programme Fees Structure</h3>
    <p class="mb-1"><strong>Name:</strong> {{ $programme->name }}</p>
    <p class="mb-4"><strong>Code:</strong> {{ $programme->code }}</p>
    @if ($programme->description)
        <p class="mb-4"><em>{{ $programme->description }}</em></p>
    @endif

    {{-- Fees Table --}}
    <table class="table table-bordered table-sm">
        <thead class="thead-light">
            <tr>
                <th style="width:20%">Semester</th>
                <th class="text-right">Tuition (UGX)</th>
            </tr>
        </thead>
        <tbody>
            @for ($i = 1; $i <= 8; $i++)
                @php
                    $has = $programme->{'has_semester_' . $i} ?? 'No';
                    $bill = $programme->{'semester_' . $i . '_bill'} ?? 0;
                @endphp
                @if ($has === 'Yes')
                    <tr>
                        <td>Semester {{ $i }}</td>
                        <td class="text-right">{{ number_format($bill, 2) }}</td>
                    </tr>
                @endif
            @endfor
        </tbody>
        <tfoot>
            <tr>
                <th>Total</th>
                <th class="text-right">{{ number_format($programme->total_semester_bills, 2) }}</th>
            </tr>
        </tfoot>
    </table>

    {{-- Optional footer --}}
    @if ($ent->print_footer ?? false)
        <hr />
        <p class="text-center"><small>{{ $ent->name }} &mdash; {{ $ent->website }}</small></p>
    @endif

</body>

</html>
