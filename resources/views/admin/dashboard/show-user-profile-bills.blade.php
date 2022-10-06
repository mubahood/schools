<style>
    .item {
        font-size: 1.5rem;
    }
</style>
@include('admin.dashboard.show-user-profile-header', ['u' => $u])
<div class="row">
    <div class="col-12 col-md-12">
        <div class="row">
            <div class="col-md-12">
                @if (count($u->services) < 1)
                    <div class="alert alert-info">
                        This student has not subscribed to any service.
                    </div>
                @else
                    <ul>
                        @foreach ($u->services as $bill)
                            <li>
                                <p><b>UGX. {{ number_format($bill->service->fee) }}</b> - {{ $bill->service->name }}</p>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</div>
