<style>
    .item {
        font-size: 1.5rem;
    }
</style>
<div class="row">
    <div class="col-12 col-md-6">
        <h4 class="m-0"><b>{{ $u->first_name }} {{ $u->last_name }}</b></h4>
        <hr>

        <div class="row">
            <div class="col-md-6">
                <h4><b>Bills</b></h4>
                @if (empty($u->bills))
                    <div class="alert alert-info">This student no any bill.</div>
                @else
                    <ul >
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
    <div class="col-12 col-md-6"><img class="img img-fluid" src="{{ $u->avatar }}" alt=""></div>
</div>
