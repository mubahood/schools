<?php
if (!isset($is_dark)) {
    $is_dark = true;
}
$is_dark = ((bool) $is_dark);

$bg = '';
$text = 'text-primary';
if ($is_dark) {
    $bg = 'bg-primary';
    $text = 'text-white';
}
?><div class="card {{ $bg }} border-primary mb-4 mb-md-5">
    <div class="card-body py-0">
        <p class="h3  text-bold mb-2 mb-md-3 {{ $text }} ">Channel analytics</p>
        <p class="display-3  m-0 text-right" style="line-height: 3.2rem">917</p>
        <p class="mt-4">120 Male, 345 Females.</p>
    </div>
</div>
