<style>

</style>
<?php
$avatar = $u->avatar;
$payable = 0;
$paid = 0;
$balance = 0;
if ($u->account != null) {
    if ($u->account->transactions != null) {
        foreach ($u->account->transactions as $key => $v) {
            if ($v->amount < 0) {
                $payable += $v->amount;
            } else {
                $paid += $v->amount;
            }
        }
    }
}
$student_data = null;
if ($u->user_type == 'student') {
    $student_data = $u->get_finances();
}

//$balance = $payable + $paid;
$balance = $u->account->balance;
?>
<div class="row">
    <div class="col-xs-4 col-md-2 ">
        <img class="img img-fluid " width="130" src="{{ $avatar }}">
    </div>
    <div class="col-xs-8 col-md-6 no-padding pt-0 mt-0">
        <h4 class="no-padding " style="line-height: .8">{{ $u->name }}</h4>
        <p class="mb-2 mb-md-3 item" style="line-height: .6"><b>Current Class:</b> {{ $u->current_class->name_text }}
        <h5 class="no-padding " style="line-height: .6"><b>PAYMENT CODE:</b> {{ $u->school_pay_payment_code }}</h5>
        <hr class="border-primary" style="margin-top: 0px; margin-bottom: 8px; ">
        <p class="mb-2 mb-md-3 item" style="line-height: .6"><b>Sex:</b> {{ $u->sex }}</p>

        </p>
        @if ($u->date_of_birth != null && strlen($u->date_of_birth) > 2)
            <p class="mb-2 mb-md-3 item" style="line-height: .6"><b>Date of birth:</b> {{ $u->date_of_birth }}</p>
        @endif

        @if ($u->place_of_birth != null && strlen($u->place_of_birth) > 2)
            <p class="mb-2 mb-md-3 item" style="line-height: .6"><b>Place of birth:</b> {{ $u->place_of_birth }}</p>
        @endif


        @if ($u->date_of_birth != null && strlen($u->date_of_birth) > 2)
            <p class="mb-2 mb-md-3 item" style="line-height: .6"><b>Home address:</b> {{ $u->date_of_birth }}</p>
        @endif

        @if ($u->current_address != null && strlen($u->current_address) > 2)
            <p class="mb-2 mb-md-3 item" style="line-height: .6"><b>Current address:</b> {{ $u->current_address }}</p>
        @endif

        @if ($u->phone_number_1 != null && strlen($u->phone_number_1) > 2)
            <p class="mb-2 mb-md-3 item" style="line-height: .6"><b>Phone number 1:</b> {{ $u->phone_number_1 }}</p>
        @endif

        @if ($u->phone_number_2 != null && strlen($u->phone_number_2) > 2)
            <p class="mb-2 mb-md-3 item" style="line-height: .6"><b>Phone number 2:</b> {{ $u->phone_number_2 }}</p>
        @endif

        @if ($u->nationality != null && strlen($u->nationality) > 2)
            <p class="mb-2 mb-md-3 item" style="line-height: .6"><b>Nationality:</b> {{ $u->nationality }}</p>
        @endif

        @if ($u->religion != null && strlen($u->religion) > 2)
            <p class="mb-2 mb-md-3 item" style="line-height: .6"><b>Religion:</b> {{ $u->religion }}</p>
        @endif

        @if ($u->father_name != null && strlen($u->father_name) > 2)
            <p class="mb-2 mb-md-3 item" style="line-height: .6"><b>Father's name:</b> {{ $u->father_name }}</p>
        @endif

        @if ($u->father_phone != null && strlen($u->father_phone) > 2)
            <p class="mb-2 mb-md-3 item" style="line-height: .6"><b>Father's phone number:</b> {{ $u->father_phone }}
            </p>
        @endif

        @if ($u->mother_name != null && strlen($u->mother_name) > 2)
            <p class="mb-2 mb-md-3 item" style="line-height: .6"><b>Mother's name:</b> {{ $u->mother_name }}</p>
        @endif

        @if ($u->date_of_birth != null && strlen($u->date_of_birth) > 2)
        @endif

        @if ($u->mother_phone != null && strlen($u->mother_phone) > 2)
            <p class="mb-2 mb-md-3 item" style="line-height: .6"><b>Mother's phone number:</b> {{ $u->mother_phone }}
            </p>
        @endif

        @if ($u->languages != null && strlen($u->languages) > 2)
            <p class="mb-2 mb-md-3 item" style="line-height: .6"><b>Languages/Dilect:</b> {{ $u->languages }}</p>
        @endif

    </div>
    <div class="col-xs-12 col-md-4  mt-4 mt-md-0">
        <div class="border border-1 border-primary p-2 ">
            @if ($student_data != null)
                <h4 class="text-center"><b><u>SCHOOL FEES SUMMARY (FOR THIS TERM)</u></b></h4>
                {{--                 <p class="m-0 p-0" style="line-height: 1.2;"><b>CLASS :</b> {{ $student_data['class']->name_text }}</p> --}}
                <p class="m-0 p-0" style="line-height: 1.2;"><b>SCHOOL FEES:</b> UGX
                    {{ number_format($student_data['fees']) }}</p>
                <p class="m-0 p-0" style="line-height: 1.2;"><b>SERVICES:</b> UGX
                    {{ number_format($student_data['services']) }}</p>
                <p class="m-0 p-0" style="line-height: 1.2;"><b>PREVIOUS TERM BALANCE:</b> UGX
                    {{ number_format($student_data['balance_bf']) }}</p>
                <hr class="border-primary" style="margin-top: 8px; margin-bottom: 8px; ">
                <p class="m-0 p-0" style="line-height: 1.2;"><b>TOTAL AMOUNT PAYABLE:</b> UGX
                    {{ number_format($student_data['total_payable']) }}</p>
                <p class="m-0 p-0" style="line-height: 1.2;"><b>TOTAL AMOUNT PAID:</b> UGX
                    {{ number_format($student_data['total_paid']) }}</p>
                <hr class="border-primary" style="margin-top: 8px; margin-bottom: 8px; ">
                <p class="m-0 p-0" style="line-height: 1.2;"><b>FEES BALANCE</b> UGX
                    {{ number_format($u->account->balance) }}</p>
            @endif
            {{--             <h4 class="text-center"><b><u>FEES SUMMARY</u></b></h4>

            <p><b>TOTAL PAID FEES:</b> UGX {{ number_format($paid) }}</p>
            <p><b>FEES BALANCE:</b> UGX {{ number_format($balance) }}</p> --}}
        </div>
        
        @if ($u->user_type == 'student' && isset($attendance_summary))
        <div class="border border-1 border-success p-2 mt-3">
            <h4 class="text-center text-success"><b><u>ATTENDANCE SUMMARY (THIS TERM)</u></b></h4>
            <div class="row">
                <div class="col-md-6">
                    <p class="m-0 p-0" style="line-height: 1.2;"><b>TOTAL SESSIONS:</b> {{ number_format($attendance_summary['total_sessions']) }}</p>
                    <p class="m-0 p-0" style="line-height: 1.2; color: green;"><b>PRESENT:</b> {{ number_format($attendance_summary['total_present']) }}</p>
                    <p class="m-0 p-0" style="line-height: 1.2; color: red;"><b>ABSENT:</b> {{ number_format($attendance_summary['total_absent']) }}</p>
                </div>
                <div class="col-md-6">
                    <p class="m-0 p-0" style="line-height: 1.2;"><b>ATTENDANCE RATE:</b> 
                        <span class="badge badge-{{ $attendance_summary['overall_rate'] >= 80 ? 'success' : ($attendance_summary['overall_rate'] >= 60 ? 'warning' : 'danger') }}">
                            {{ number_format($attendance_summary['overall_rate'], 1) }}%
                        </span>
                    </p>
                </div>
            </div>
            
            <hr class="border-success" style="margin-top: 8px; margin-bottom: 8px;">
            <h6 class="text-center"><b>BY TYPE</b></h6>
            
            @if(!empty($attendance_summary['by_type']))
                @foreach($attendance_summary['by_type'] as $type_data)
                <div class="row" style="font-size: 0.9rem;">
                    <div class="col-md-6">
                        <p class="m-0 p-0" style="line-height: 1.1;"><b>{{ $type_data['type_name'] }}:</b></p>
                    </div>
                    <div class="col-md-6">
                        <p class="m-0 p-0" style="line-height: 1.1;">
                            <span class="badge badge-{{ $type_data['rate'] >= 80 ? 'success' : ($type_data['rate'] >= 60 ? 'warning' : 'danger') }}">
                                {{ number_format($type_data['rate'], 1) }}%
                            </span>
                            ({{ $type_data['present'] }}/{{ $type_data['total'] }})
                        </p>
                    </div>
                </div>
                @endforeach
            @else
                <p class="text-center text-muted" style="font-size: 0.9rem;">No attendance data available</p>
            @endif
        </div>
        @endif
    </div>
</div>
<hr class="border-primary" style="margin-top: 14px; margin-bottom: 8px; ">
