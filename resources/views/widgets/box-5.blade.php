<?php
$title = $title ?? 'Title';
$number = $number ?? '0';
$sub_title = $sub_title ?? '';
$link = $link ?? 'javascript:;';
$icon = $icon ?? 'circle-o';
$is_dark = isset($is_dark) ? (bool) $is_dark : false;
$style = $style ?? 'default';

$cls = 'ds-stat';
if ($is_dark) $cls .= ' ds-stat--accent';
if ($style === 'danger') $cls .= ' ds-stat--danger';
?><a href="{!! $link !!}" class="{{ $cls }}">
    <div class="ds-stat__icon"><i class="fa fa-{!! $icon !!}"></i></div>
    <div class="ds-stat__body">
        <span class="ds-stat__label">{!! $title !!}</span>
        <span class="ds-stat__value">{!! $number !!}</span>
        @if($sub_title)<span class="ds-stat__caption">{!! $sub_title !!}</span>@endif
    </div>
</a>
