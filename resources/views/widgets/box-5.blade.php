<?php

$title = isset($title) ? $title : 'Title';
$number = isset($number) ? $number : '0.00';
$sub_title = isset($sub_title) ? $sub_title : 'Sub-titles';
$link = isset($link) ? $link : 'javascript:;';

if (!isset($is_dark)) {
    $is_dark = true;
}
$is_dark = ((bool) $is_dark);

$bg = '';
$text = 'text-primary';
$text2 = 'text-dark';
if ($is_dark) {
    $bg = 'bg-primary';
    $text = 'text-white';
    $text2 = 'text-white';
}
?><a href="{{ $link }}" class="card {{ $bg }} border-primary mb-4 mb-md-5">
    <div class="card-body py-0">
        <p class="h3  text-bold mb-2 mb-md-3 {{ $text }} ">{{ $title }}</p>
        <p class="display-3  m-0 text-right {{ $text2 }}" style="line-height: 3.2rem">{{ $number }}</p>
        <p class="mt-4 {{ $text2 }}">{{ $sub_title }}</p>
    </div>
</a>
