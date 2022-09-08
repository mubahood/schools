<li class="dropdown">
    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
        <i class="fa fa-th"></i>
    </a>
    <ul class="dropdown-menu" style="padding: 0;box-shadow: 0 2px 3px 0 rgba(0,0,0,.2);">
        <li>
            <div class="box box-solid" style="width: 300px;height: 300px;margin-bottom: 0;">
                <div class="box-body">

                    @foreach ($links as $i)
                        <a class="btn btn-app" href="{{ $i['url'] }}">
                            <i class="fa fa-{{ $i['icon'] }}"></i> {{ $i['title'] }}
                        </a>
                    @endforeach
                </div>
            </div>
        </li>
    </ul>
</li>
