<?php
use App\Models\AcademicClass;
use App\Models\SchoolFeesDemand;
use App\Models\Utils;

?>
@include('print.css')
@foreach ($recs as $index => $rec)
    @php
        $class = AcademicClass::find($index);
    @endphp
    @php
        $count = 0;
        $super_count = 0;
    @endphp
    @foreach ($rec as $item)
        @php
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

                        @if ($acc->owner->residence != 'BOARDER')
                            @include('fees.meal-card-item', [
                                'ent' => $ent,
                                'demand' => $demand,
                                'item' => $rec[$super_count],
                            ])
                        @else
                            @include('fees.meal-card-item-1', [
                                'ent' => $ent,
                                'demand' => $demand,
                                'item' => $rec[$super_count],
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

                        @if ($acc->owner->residence != 'BOARDER')
                            @include('fees.meal-card-item', [
                                'ent' => $ent,
                                'demand' => $demand,
                                'item' => $rec[$super_count],
                            ])
                        @else
                            @include('fees.meal-card-item-1', [
                                'ent' => $ent,
                                'demand' => $demand,
                                'item' => $rec[$super_count],
                            ])
                        @endif
                    @endif
                </td>
            </tr>
        </table>

        @php
            if ($super_count > count($rec)) {
                break;
            }
            $super_count++;
        @endphp
    @endforeach
    <div style="page-break-after: always;"></div>
    @php
    @endphp
@endforeach
