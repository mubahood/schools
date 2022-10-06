<style>
    .item {
        font-size: 1.5rem;
    }
</style>
@include('admin.dashboard.show-user-profile-header', ['u' => $u])

<?php
$avatar = url('user.jpeg');
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
    <div class="col-12 col-md-8">
        <div class="row">
            <div class="col-md-12">
                @if (empty($u->bills))
                    <div class="alert alert-info">This student no any bill.</div>
                @else
                    <ul>
                        @foreach ($u->bills as $bill)
                            <li>
                                <p><b>UGX. {{ number_format($bill->fee->amount) }}</b> - {{ $bill->fee->name }}</p>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

    </div>
    <div class="col-12 col-md-4"><img class="img img-fluid" src="{{ $u->avatar }}" alt=""></div>
</div>
