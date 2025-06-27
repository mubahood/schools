@php
    use App\Models\Service;
    use App\Models\Utils;

    // Paths & context
    $logoPath = public_path('storage/' . $ent->logo);
    $activeTerm = $ent->active_term();
    if (!$activeTerm) {
        throw new \Exception('No active term for enterprise.');
    }
    $termName = $activeTerm->name; // e.g. "1"
    $yearName = $activeTerm->academic_year->name; // e.g. "2024"

    // Build combined rows
    $rows = [];
    $tuitionTotal = 0;

    // 1) Tuition lines
    for ($i = 1; $i <= 8; $i++) {
        if (($programme->{'has_semester_' . $i} ?? 'No') === 'Yes') {
            $amt = $programme->{'semester_' . $i . '_bill'} ?? 0;
            $tuitionTotal += $amt;
            $rows[] = [
                'item' => "Semester {$i} Tuition",
                'description' => "Tuition for semester {$i}",
                'amount' => $amt,
            ];
        }
    }
    $rows[] = ['item' => 'Tuition subtotal', 'description' => '', 'amount' => $tuitionTotal, 'isSubtotal' => true];

    // 2) Compulsory Services
    $svcTotal = 0;
    Service::where('enterprise_id', $ent->id)
        ->where('is_compulsory', 'Yes')
        ->get()
        ->each(function ($s) use (&$rows, &$svcTotal, $programme, $termName) {
            // course filter
            $courses = $s->applicable_to_courses;
            $toAllCr = $s->is_compulsory_to_all_courses === 'Yes';
            // semester filter
            $sems = $s->applicable_to_semesters;
            $toAllSems = $s->is_compulsory_to_all_semesters === 'Yes';

            if (
                ($toAllCr || in_array($programme->id, $courses)) &&
                ($toAllSems || in_array((string) $termName, $sems))
            ) {
                $svcTotal += $s->fee;
                $reason = $toAllCr ? 'Compulsory for all programmes' : 'Compulsory for this programme';
                $reason .= $toAllSems ? '' : " in semester {$termName}";

                $rows[] = [
                    'item' => $s->name,
                    'description' => $reason,
                    'amount' => $s->fee,
                ];
            }
        });
    $rows[] = [
        'item' => 'Compulsory services subtotal',
        'description' => '',
        'amount' => $svcTotal,
        'isSubtotal' => true,
    ];

    $grandTotal = $tuitionTotal + $svcTotal;

    // 3) Optional Services
    $optSvcs = Service::where('enterprise_id', $ent->id)->where('is_compulsory', 'No')->get();
@endphp

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Fees Structure – {{ $programme->name }}</title>
    <link rel="stylesheet" href="{{ public_path('css/bootstrap-print.css') }}">
    <style>
        body,
        p,
        table {
            font-size: 12px;
        }

        th,
        td {
            padding: .3rem .5rem;
        }

        .subtotal {
            font-weight: bold;
        }

        .accent {
            color: {{ $ent->color }};
        }

        hr {
            border-color: {{ $ent->color }};
        }
    </style>
    @if ($ent->print_water_mark ?? false)
        <style>
            body::before {
                content: "";
                position: absolute;
                inset: 0;
                background: url('{{ $logoPath }}') no-repeat center/80%;
                opacity: .05;
                z-index: -1;
            }
        </style>
    @endif
</head>

<body>

    {{-- Header --}}
    <table class="w-100 mb-2">
        <tr>
            <td style="width:15%"><img src="{{ $logoPath }}" style="max-width:100%;" /></td>
            <td class="text-center">
                <h2 class="h5 text-uppercase">{{ $ent->name }}</h2>
                <p class="mb-0">{{ $ent->address }} | P.O. Box {{ $ent->p_o_box }}</p>
                <p class="mb-0">Tel: {{ $ent->phone_number }}@if ($ent->phone_number_2)
                        , {{ $ent->phone_number_2 }}
                    @endif
                </p>
                <p class="mb-0">Email: {{ $ent->email }}@if ($ent->website)
                        | Web: {{ $ent->website }}
                    @endif
                </p>
            </td>
            <td style="width:15%"></td>
        </tr>
    </table>
    <hr class="mb-3" />

    {{-- Title & Metadata --}}
    <p class="text-right"><small>{{ Utils::my_date(time()) }}</small></p>
    <h4 class="text-center accent mb-2">Programme Fees Structure</h4>
    <p class="mb-1"><strong>Name:</strong> {{ $programme->name }}</p>
    <p class="mb-1"><strong>Code:</strong> {{ $programme->code }}</p>
    <p class="mb-1">
        <strong>Term:</strong> Semester {{ $termName }}
        &nbsp;|&nbsp;
        <strong>Academic Year:</strong> {{ $yearName }}
    </p>
    @if ($programme->description)
        <p class="mb-3"><em>{{ $programme->description }}</em></p>
    @endif

    {{-- Tuition + Compulsory Services --}}
    <table class="table table-bordered table-sm mb-4">
        <thead>
            <tr class="accent">
                <th style="width:35%">Item</th>
                <th>Description</th>
                <th class="text-right" style="width:20%">Amount (UGX)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $r)
                <tr @if (!empty($r['isSubtotal'])) class="subtotal" @endif>
                    <td>{{ $r['item'] }}</td>
                    <td>{{ $r['description'] }}</td>
                    <td class="text-right">{{ number_format($r['amount'], 2) }}</td>
                </tr>
            @endforeach
            <tr class="subtotal accent">
                <td colspan="2">Grand Total</td>
                <td class="text-right">{{ number_format($grandTotal, 2) }}</td>
            </tr>
        </tbody>
    </table>

    {{-- Optional Services --}}
    <h5 class="accent mb-2">Optional Services</h5>
    <p class="small text-muted mb-2">Enroll individually at Student Finance.</p>
    <table class="table table-bordered table-sm mb-4">
        <thead>
            <tr class="accent">
                <th>Service</th>
                <th class="text-right">Fee (UGX)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($optSvcs as $s)
                <tr>
                    <td>{{ $s->name }}</td>
                    <td class="text-right">{{ number_format($s->fee, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Footer --}}
    @if ($ent->print_footer ?? false)
        <hr />
        <p class="text-center small">{{ $ent->name }} &mdash; {{ $ent->website }}</p>
    @endif

</body>

</html>
