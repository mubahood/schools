<style>
    .item {
        font-size: 1.5rem;
    }
</style>
@include('admin.dashboard.show-user-profile-header', ['u' => $u])
<div class="row">
    <div class="col-md-12">
        <div class="row">
            <div class="col-md-12">
                @if (empty($u->account->transactions))
                    <div class="alert alert-info">This student no Transactions.</div>
                @else
                    <ul>
                        @foreach ($u->account->transactions as $tra)
                            <li>
                                <p>{{ $tra->payment_date }} -  <b>UGX. {{ number_format($tra->amount) }}</b> -
                                    {{ $tra->description }}</p>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

    </div>
</div>
