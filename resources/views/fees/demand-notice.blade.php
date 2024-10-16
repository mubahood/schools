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
    <h4 class="text-center"> {{ $class->name }}</h4>
    <hr>

    @php
        $count = 0;
    @endphp
    @foreach ($rec as $item)
        @php
            $count++;
            $break_style = '';
            if ($count == 2) {
                $count = 0;
                $break_style = 'page-break-after: always;';
            }
        @endphp
        <div class="p-3 pb-4 mb-4" style="border: solid black .2rem; {{ $break_style }}">
            <table class="w-100 ">
                <tbody>
                    <tr>
                        <td style="width: 12%;" class="pr-2">
                            <img class="img-fluid" src="{{ public_path('storage/' . $ent->logo) }}"
                                alt="{{ $ent->name }}">
                        </td>
                        <td class="text-center">
                            <p class="p-0 m-0 fs-22 lh-20"><b>{{ strtoupper($ent->name) }}</b></p>
                            <p class=" p-0 mb-1 lh-14 mt-1">P.O.BOX {{ $ent->p_o_box }}</p>
                            <p class=" p-0 mb-1 lh-16 mt-1">Tel: <b>{{ $ent->phone_number }}</b> ,
                                <b>{{ $ent->phone_number_2 }}
                            </p>
                            </p>
                        </td>
                        <td style="width: 15%; text-align: right;">
                            {{--                             <b><span style="color: red;">{{ Utils::my_date($demand->created_at) }}</span></b> --}}
                            <br><br><br>
                        </td>
                    </tr>
                </tbody>
            </table>
            <hr class="p-0 m-0 mt-2 mb-1 bg-dark">
            <p class="text-right mt-2">{{ Utils::my_date($demand->created_at) }}</p>
            <h2 class="text-center fs-16 mt-2 fs-18"><u>FEES DEMAND NOTICE</u></h2>
            <div class="text-justify">
                {!! SchoolFeesDemand::get_demand_message($demand, $item) !!}
            </div>
            <br>
            <p class="text-right"><b>Signature:................................</b></p>
        </div>
    @endforeach
    <div style="page-break-after: always;"></div>
@endforeach
