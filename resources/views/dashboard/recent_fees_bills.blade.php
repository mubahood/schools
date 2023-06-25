<?php
use App\Models\Utils;
?><style>
    .ext-icon {
        color: rgba(0, 0, 0, 0.5);
        margin-left: 10px;
    }

    .installed {
        color: #00a65a;
        margin-right: 10px;
    }

    .card {
        border-radius: 5px;
    }

    .case-item:hover {
        background-color: rgb(254, 254, 254);
    }
</style>
<div class="card mb-4 mb-md-5 border-0">
    <!--begin::Header-->
    <div class="d-flex justify-content-between px-3 px-md-4 ">
        <h3 class="h4 pt-3 text-uppercase">
            <b>{{ $title }}</b>
        </h3>
        <div>
            <a href="{{ url('/fees') }}" class="btn btn-sm btn-primary mt-md-4 mt-4">
                View All
            </a>
        </div>
    </div>
    <div class="card-body py-2 py-md-3">
        <div class="row">
            <div class="col-md-12">
                @foreach ($data as $item)
                    <div class="py-1" title="{{ $item->description }}">
                        <div class="d-flex justify-content-between  align-items-center text-uppercase p-0 m-0"
                            style="font-weight: 600; line-height: 12px; font-size: 12px; 
                            ">
                            {{ Str::substr($item->account->name, 0, 15) }}...
                            <span class="text-danger"> {{ number_format($item->amount) }}</span>
                        </div>
                        <div class="">
                            <small class="p-0 m-0">{{ $item->description }}</small>
                        </div>
                    </div>
                    <hr class="p-0 m-0 mb-1">
                @endforeach
            </div>
        </div>
    </div>
</div>
