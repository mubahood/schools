<?php
//use Utils;
use App\Models\Utils;
?><style>
    .item {
        font-size: 1.5rem;
    }
</style>
@include('admin.dashboard.show-user-profile-header', ['u' => $u])
<p class="bg-primary p-2 m-0" style="font-weight: 800">School Fees Billing & Payement (All time)</p>
<div class="row">
    <div class="col-md-12">
        <div class="row">
            <div class="col-md-12">
                @if (empty($all_transactions))
                    <div class="alert alert-info">This student no Transactions.</div>
                @else
                    <table class="table table-sm table-striped table-bordered">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th>DATE</th>
                                <th>AMOUNT</th>
                                <th>DESCRIPTION</th>
                                <th>DUE TERM</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($all_transactions as $tra)
                                @php
                                    $color = 'green';
                                    if ($tra->amount < 0) {
                                        $color = 'red';
                                    }
                                @endphp
                                <tr>
                                    <td>{{ Utils::my_date($tra->payment_date) }}</td>
                                    <td>UGX. {{ number_format($tra->amount) }}</td>
                                    <td>
                                        <div
                                            style="
                                    background-color: {{ $color }}; 
                                    border-radius: 5px; 
                                    margin-right: 3px;
                                    margin-top: -5px;
                                    width: 
                                    10px; 
                                    height: 10px; 
                                    display: inline-block;">
                                        </div> {{ $tra->description }}
                                    </td>
                                    <td>{{ $tra->term->name_text }}</td>
                                </tr>
                            @endforeach
                    </table>
                @endif
            </div>
        </div>

    </div>
</div>
