<style>
    .item {
        font-size: 1.5rem;
    }
</style>
@include('admin.dashboard.show-user-profile-header', ['u' => $u])
<div class="row">
    <div class="col-12 col-md-12">
        <p class="bg-primary p-2 m-0" style="font-weight: 800">Service subscription of this student (For This Term)</p>
        <div class="row">
            <div class="col-md-12">
                @if (count($services_for_this_term) < 1)
                    <div class="alert alert-info mt-3">
                        This student has not subscribed to any service.
                    </div>
                @else
                    <ul>
                        @foreach ($services_for_this_term as $bill) 
                            <li>
                                <p>
                                    <b>SERVICE: </b> {{ $bill->service->name }} <br>
                                    <b>QUANTITY: </b> {{ $bill->quantity }} <br>
                                    <b>TOTAL: </b> UGX. {{ number_format($bill->total) }} <br>
                                    <b>TERM: </b> {{ $bill->due_term->name_text }} <br>
                                    <b>DATE: </b> {{ $bill->created_at }} <br>
                                </p>
                            </li>
                        @endforeach
                        {{-- 
    "id" => 1
    "created_at" => "2022-10-11 01:30:26"
    "updated_at" => "2022-11-23 20:28:33"
    "enterprise_id" => 7
    "service_id" => 1
    "administrator_id" => 2319
    "quantity" => 1
    "total" => 50000
    "due_academic_year_id" => 2
    "due_term_id" => 6
--}}
                    </ul>
                @endif
            </div>
        </div>
    </div>
</div>
