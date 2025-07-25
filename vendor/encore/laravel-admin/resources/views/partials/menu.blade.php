@php
    $u = Admin::user();
    $ent = $u->ent;
    $has_access = true;

    if (isset($item['access_by']) && is_array($item['access_by'])) {
        //check if is not empty

        $access_by = $item['access_by'];
        if (!empty($access_by)) {
            $has_access = false;

            //check if contains "All"
            if (in_array('All', $access_by)) {
                $has_access = true;
            } else {
                //check if contains ent
                if (in_array($ent->type, $access_by)) {
                    $has_access = true;
                }
            }
        }
    }

@endphp

@if ($has_access)
    @if (Admin::user()->visible(\Illuminate\Support\Arr::get($item, 'roles', [])) &&
            Admin::user()->can(\Illuminate\Support\Arr::get($item, 'permission')))
        @if (!isset($item['children']))
            <li>
                @if (url()->isValidUrl($item['uri']))
                    <a href="{{ $item['uri'] }}" target="_blank">
                    @else
                        <a href="{{ admin_url($item['uri']) }}">
                @endif
                <i class="fa {{ $item['icon'] }}"></i>
                @if (Lang::has($titleTranslation = 'admin.menu_titles.' . trim(str_replace(' ', '_', strtolower($item['title'])))))
                    <span>{{ __($titleTranslation) }}</span>
                @else
                    <span>{{ admin_trans($item['title']) }}</span>
                @endif
                </a>
            </li>
        @else
            <li class="treeview">
                <a href="#">
                    <i class="fa {{ $item['icon'] }}"></i>
                    @if (Lang::has($titleTranslation = 'admin.menu_titles.' . trim(str_replace(' ', '_', strtolower($item['title'])))))
                        <span>{{ __($titleTranslation) }}</span>
                    @else
                        <span>{{ admin_trans($item['title']) }}</span>
                    @endif
                    <i class="fa fa-angle-left pull-right"></i>
                </a>
                <ul class="treeview-menu">
                    @foreach ($item['children'] as $item)
                        @include('admin::partials.menu', $item)
                    @endforeach
                </ul>
            </li>
        @endif
    @endif
@endif
