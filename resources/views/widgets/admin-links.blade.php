@if (isset($u) && $u != null && $u->ent != null && $u->ent->active_academic_year() != null)
    <?php
    $canSwitchYears = false;
    $dpYear = $u->ent->dpYear();
    if ($u->isRole('admin') || $u->isRole('dos') || $u->isRole('hm') || $u->isRole('bursar')) {
        $canSwitchYears = true;
    }
    ?>
    @if ($dpYear != null)


        <li class="dropdown">
            <a href="#" class="dropdown-toggle auto-refresh" data-toggle="dropdown" title="Academic Year - Display"
                aria-expanded="true">
                <i class="fa fa-play"></i>&nbsp;&nbsp;
                <span class="interval-text">{{ $dpYear->name }}</span>
            </a>
            @if ($canSwitchYears)

                <ul class="dropdown-menu" style="width: 30px !important;">
                    @foreach ($u->ent->academic_years as $year)
                        <li><a href="{{ admin_url('?change_dpy_to=' . $year->id) }}"
                                title='Change display academic year to {{ $year->name }}'
                                data-interval="2">{{ $year->name }}</a></li>
                    @endforeach
                </ul>
            @endif

        </li>
    @endif
@endif
{{-- 
    
          $u = Auth::user();
        if ($u != null) {
        dd($u->ent->active_academic_years);
    --}}
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
