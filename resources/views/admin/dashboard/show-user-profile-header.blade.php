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
$balance = $payable + $paid;
?>
<div class="row">
    <div class="col-xs-4 col-md-2 ">
        <img class="img img-fluid " width="130" src="{{ $avatar }}">
    </div>
    <div class="col-xs-8 col-md-6  ">
        <h3 class="no-padding ">{{ $u->name }}</h3>
        <h5 class="no-padding "><b>PAYMENT CODE:</b> {{ $u->school_pay_payment_code }}</h5>
    </div>
    <div class="col-xs-12 col-md-4  ">
        <div class="border border-1 border-primary p-2">
            <h4 class="text-center"><b><u>FEES SUMMARY</u></b></h4>
            <p><b>TOTAL PAYABLE FEES:</b> UGX {{ number_format($payable) }}</p>
            <p><b>TOTAL PAID FEES:</b> UGX {{ number_format($paid) }}</p>
            <p><b>FEES BALANCE:</b> UGX {{ number_format($balance) }}</p>
        </div>
    </div>
</div>
<hr>
