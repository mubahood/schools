<div class="row pb-0 mb-0">
    <div class="d-none d-md-block col-3">
        <div class=" m-4  rounded mr-0 p-2 mb-0 bg-primary">
            <img class="img-fluid" src="{{ $icon }}">
        </div>
    </div>
    <div class="col-12 pl-5 p-md-0 col-md-8    pt-1">
        <h2 class="h3 text-dark " style="line-height: 2rem;">{{ $title ?? 'Title' }}</h2>
        <p class=" bg-light list-group list-group-flush p-md-0 rounded-0">

            @foreach ($links as $item)
            <a href="{{ $item['link'] }}" class="  text-primary list-group-item
            p-0 pt-1 rounded-0 px-0 bg-light
            "
            style="font-size: 1.5rem;">
            <i class="fa fa-angle-right"></i> {{ $item['text'] }}</a>
        @endforeach


        </p>
    </div>
</div>
