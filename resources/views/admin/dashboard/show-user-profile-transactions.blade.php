<style>
    .item {
        font-size: 1.5rem;
    }
</style>
<div class="row">
    <div class="col-12 col-md-8">
        <h4 class="m-0"><b>{{ $u->first_name }} {{ $u->last_name }}</b></h4>
        <hr>
        <div class="row">
            <div class="col-md-12">
                @if (empty($u->account->transactions))
                    <div class="alert alert-info">This student no Transactions.</div>
                @else
                    <ul>
                        @foreach ($u->account->transactions as $tra)
                            <li>
                                <p><b>UGX. {{ number_format($tra->amount) }}</b> - {{ $tra->description }}</p>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

    </div>
    <div class="col-12 col-md-4"><img class="img img-fluid" src="{{ $u->avatar }}" alt=""></div>
</div>
