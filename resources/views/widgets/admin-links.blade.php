<li class="dropdown notifications-menu">
    <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
        <i class="fa fa-bell-o"></i>
        <span class="label label-danger">{{ count($items) }}</span>
    </a>
    <ul class="dropdown-menu">
        <li class="header">You have {{ count($items) }} system warnings</li>
        <li>


            <ul class="menu">
                @foreach ($items as $item)
                    <li>
                        <a href="{{ $item['link'] }}">
                            <i class="fa fa-warning text-danger"></i> {{ $item['message'] }}
                        </a>
                    </li>
                @endforeach
            </ul>

        </li>
        <li class="footer"><a href="#">View all</a></li>
    </ul>
</li>

</li>
