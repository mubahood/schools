<?php
//use Utils;
use App\Models\Utils;
?>
<style>
    .item {
        font-size: 1.5rem;
    }
</style>

@include('admin.dashboard.show-user-profile-header', ['u' => $u])
<div class="row">
    <div class="col-xs-12 col-md-12">
        @if ($u->user_type == 'student')
            <p class="bg-primary p-2 m-0" style="font-weight: 800">School Fees Billing & Payement (For This term)</p>
            <div class="row">
                <div class="col-md-12">
                    @if (empty($active_term_transactions))
                        <div class="alert alert-info">This student no Transactions.</div>
                    @else
                        @foreach ($active_term_transactions as $tra)
                            @php
                                $color = 'green';
                                if ($tra->amount < 0) {
                                    $color = 'red';
                                }
                            @endphp
                            <p>
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
                            </div>
                            {{ Utils::my_date($tra->payment_date) }} - <b>UGX. {{ number_format($tra->amount) }}</b> -
                            {{ $tra->description }}</p>
                        @endforeach
                    @endif
                </div>
            </div>
        @endif


        @if ($u->user_type == 'employee')
            <hr>
            <p class="mb-2 mb-md-3 item"><b>Emergency person to contact name:</b> {{ $u->emergency_person_name }}</p>
            <p class="mb-2 mb-md-3 item"><b>Emergency person to contact phone number:</b>
                {{ $u->emergency_person_phone }}
            </p>
            <p class="mb-2 mb-md-3 item"><b>Spouse's name:</b> {{ $u->spouse_name }} </p>
            <p class="mb-2 mb-md-3 item"><b>Spouse's phone number:</b> {{ $u->spouse_name }} </p>
            <hr>
            <p class="mb-2 mb-md-3 item"><b>Primary school name:</b> {{ $u->primary_school_name }} </p>
            <p class="mb-2 mb-md-3 item"><b>Primary school year graduated:</b> {{ $u->primary_school_year_graduated }}
            </p>
            <p class="mb-2 mb-md-3 item"><b>Seconday school name:</b> {{ $u->seconday_school_name }} </p>
            <p class="mb-2 mb-md-3 item"><b>Seconday school year graduated:</b>
                {{ $u->seconday_school_year_graduated }} </p>
            <p class="mb-2 mb-md-3 item"><b>High school name:</b> {{ $u->high_school_name }} </p>
            <p class="mb-2 mb-md-3 item"><b>High school name year graduated:</b> {{ $u->high_school_year_graduated }}
            </p>
            <p class="mb-2 mb-md-3 item"><b>Degree university name:</b> {{ $u->degree_university_name }} </p>
            <p class="mb-2 mb-md-3 item"><b>Degree university year graduated:</b>
                {{ $u->degree_university_year_graduated }} </p>
            <p class="mb-2 mb-md-3 item"><b>Masters Degree university name:</b> {{ $u->masters_university_name }} </p>
            <p class="mb-2 mb-md-3 item"><b>Masters Degree graduation year:</b>
                {{ $u->masters_university_year_graduated }} </p>
            <p class="mb-2 mb-md-3 item"><b>PHD university name:</b> {{ $u->phd_university_name }} </p>
            <p class="mb-2 mb-md-3 item"><b>PHD university year:</b> {{ $u->phd_university_year_graduated }} </p>
        @endif

    </div>
</div>
