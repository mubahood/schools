<?php
use App\Models\AcademicClass;
use App\Models\SchoolFeesDemand;
use App\Models\Utils;

$logo = public_path('storage/' . $ent->logo);

if (!isset($min)) {
    $min = 0;
}

if (!isset($max)) {
    $max = 100000;
}

if (!file_exists($logo)) {
    $logo = public_path('storage/logo.png');
}

?>
@include('print.css')
<div class="mb-3 text-center">
    <p><strong>Balance:</strong> {{ $demand->direction }} UGX {{ number_format($demand->amount) }}. RANGE:
        {{ $min }} - {{ $max }}</p>
</div>
<hr>
@php
    $my_counter = 0;
@endphp
@foreach ($recs as $index => $rec)
    <?php
        $class = AcademicClass::find($index);
        if ($type == 'LIST') {
            echo '<h2 class="text-center">' . $class->name_text . '</h2>';
            
            ?>
    <table class="w-100 table table-bordered table-sm table-striped">
        <tr>
            <th class="text-left">Name</th>
            <th class="text-left">Class</th>
            <th class="text-left">Residence</th>
            <th class="text-center">Balance (UGX)</th>
        </tr>
        <?php
        $ii = 0;
        $balance_total = 0;
        //sort $rec by balance
        // $rec->sortBy('balance');
        foreach ($rec as $item) {
            //check if $my_counter is within the range min and max
            if ($my_counter < $min || $my_counter > $max) {
                $my_counter++;
                continue;
            }
            $my_counter++;
            $ii++;
        
            $balance_total += $item->balance;
            $owner = $item->owner;
            echo '<tr class="text-left p-1">';
            echo '<td class="text-left p-1">' . $ii . '. ' . $owner->name . '</td>';
            echo '<td class="text-left p-1">' . $owner->current_class->name_text . '</td>';
            echo '<td class="text-left p-1">' . $owner->residence . '</td>';
            echo '<td class="text-right p-1">UGX <b>' . number_format($item->balance) . '</b></td>';
            echo '</tr>';
        }
        ?>
        <tr>
            <td colspan="3" class="text-right p-1">Total</td>
            <td class="text-right p-1">UGX <b>{{ number_format($balance_total) }}</b></td>
        </tr>
    </table>



    <?php
            continue;
        }
    ?>
    @php
        if ($type == 'LIST') {
            break;
        }
        $count = 0;
        $super_count = 0;
    @endphp
    @foreach ($rec as $item)
        @php

            if ($my_counter < $min || $my_counter > $max) {
                $my_counter++;
                continue;
            }
            $my_counter++;
            $count++;
            $super_count++;
            $break_style = '';
            if ($count == 2) {
                $count = 0;
                $break_style = 'page-break-after: always;';
            }
        @endphp

        <table class="w-100">
            <tr>
                <td style="width: {{ 100 / 2 }}%!important" class="pr-1 pb-2">
                    @if (isset($rec[$super_count]))
                        @php
                            $acc = $rec[$super_count];
                        @endphp
                        @if ($IS_GATE_PASS && isset($rec[$super_count]))
                            @include('fees.meal-card-item-3', [
                                'ent' => $ent,
                                'demand' => $demand,
                                'item' => $rec[$super_count],
                                'logo' => $logo,
                                'balance' => 'UGX ' . number_format($acc->balance),
                            ])
                        @elseif ($acc->owner->residence != 'BOARDER')
                            @include('fees.meal-card-item', [
                                'ent' => $ent,
                                'demand' => $demand,
                                'item' => $rec[$super_count],
                                'balance' => 'UGX ' . number_format($acc->balance),
                            ])
                        @else
                            @include('fees.meal-card-item-1', [
                                'ent' => $ent,
                                'demand' => $demand,
                                'item' => $rec[$super_count],
                                'balance' => 'UGX ' . number_format($acc->balance),
                            ])
                        @endif
                    @endif
                </td>
                <td style="width: {{ 100 / 2 }}%!important" class="pl-2 pb-2">
                    @if (isset($rec[$super_count + 1]))
                        {{-- BOARDER --}}
                        @php
                            $acc = $rec[$super_count + 1];
                        @endphp
                        @if ($IS_GATE_PASS)
                            @include('fees.meal-card-item-3', [
                                'ent' => $ent,
                                'demand' => $demand,
                                'item' => $rec[$super_count + 1],
                                'balance' => 'UGX ' . number_format($acc->balance),
                            ])
                        @elseif ($acc->owner->residence != 'BOARDER')
                            @include('fees.meal-card-item', [
                                'ent' => $ent,
                                'demand' => $demand,
                                'item' => $rec[$super_count + 1],
                                'balance' => 'UGX ' . number_format($acc->balance),
                            ])
                        @else
                            @include('fees.meal-card-item-1', [
                                'ent' => $ent,
                                'demand' => $demand,
                                'item' => $rec[$super_count + 1],
                                'balance' => 'UGX ' . number_format($acc->balance),
                            ])
                        @endif
                    @endif
                </td>
            </tr>
        </table>

        @php
            $super_count++;
            if ($super_count > count($rec)) {
                break;
            }
        @endphp
    @endforeach
    <div style="page-break-after: always;"></div>
    @php
    @endphp
@endforeach
