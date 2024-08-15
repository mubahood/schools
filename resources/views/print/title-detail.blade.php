<?php
$t = isset($t) ? $t : '-';
$d = isset($d) ? $d : '-';

//$t to uppercase
$t = strtoupper($t);
?>
<p><b>{{ $t }}:</b> {!! $d !!}</p>
