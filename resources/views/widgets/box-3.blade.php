<?php
if (!isset($icon)) {
    $icon = 'icon-announcement.png';
}
if (!isset($count)) {
    $count = 0;
}
if (!isset($sub_title)) {
    $sub_title = '-';
}
?>
<div class="row">
    <div class="col-sm-4 p-2">
        <img width="85" height="85" src="{{ url('assets/icons/' . $icon) }}" alt="">
    </div>
    <div class="col-sm-8">
        <h2 class="box-counter">{{ $count }}</h2>
        <p class="text-right box-subtitle">{{ $sub_title }}</p>
    </div>
</div>
