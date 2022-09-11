<?php
if (!isset($menu_items)) {
    $menu_items = [];
}
?><div class="row">
    @foreach ($menu_items as $item)
        <div class="col-6 col-md-3"> 
            @include('widgets.box-3', [
                'title' => $item->title,
                'sub_title' => $item->sub_title,
                'icon' => url('storage/' . $item->image),
            ])
        </div>
    @endforeach
</div>
