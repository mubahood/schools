<?php
use App\Models\Utils;
use App\Models\StudentHasClass;
use App\Models\StudentHasTheologyClass;

$max_bot = 30;
$max_mot = 40;
$max_eot = 60;
$tr = isset($tr) ? $tr : null;
$ent = $r->ent;
$owner = $r->owner;

$tr = $r->get_theology_report();
$termly_report_card = $r->termly_report_card;
$theology_termly_report_card = null;

$stream_class = '.......';
$theo_stream_class = '.......';
$hasClass = StudentHasClass::where(['administrator_id' => $r->owner->id, 'academic_class_id' => $r->academic_class_id])->first();
if ($hasClass != null) {
    if ($hasClass->stream != null) {
        $stream_class = ' - ' . $hasClass->stream->name;
    }
}

if ($tr != null) {
    $hasClass = StudentHasTheologyClass::where(['administrator_id' => $tr->owner->id, 'theology_class_id' => $tr->theology_class_id])->first();
    if ($hasClass != null) {
        if ($hasClass->stream != null) {
            $theo_stream_class = ' - ' . $hasClass->stream->name;
        }
    }
}
if ($tr == null) {
    $tr = $r->get_theology_report();
}
if ($tr != null) {
    $theology_termly_report_card = $tr->termly_report_card;
}

$bal = ((int) $r->owner->account->balance);
$bal_text = '';
if ($bal == 0) {
    $bal_text = 'NIL BALANCE';
} else {
    if ($bal < 0) {
        $bal = -1 * $bal;
    }
    $bal_text = 'UGX ' . number_format($bal);
}

if (!$r->owner->account->status) {
    $bal_text = '...................';
}

$numFormat = new NumberFormatter('en_US', NumberFormatter::ORDINAL);
foreach ($r->termly_report_card->term->exams as $exam) {
    if ($exam->type == 'B.O.T') {
        $max_bot = $exam->max_mark;
    }
    if ($exam->type == 'M.O.T') {
        $max_mot = $exam->max_mark;
    }
    if ($exam->type == 'E.O.T') {
        $max_eot = $exam->max_mark;
    }
}
?>
<article>
    <p class="text-center bg-info" style="font-family: 'Tilt Prism'; font-size: 50px; "><b>

            This is simple Test Message الله الرحمن </b></p>
    <p class="text-center bg-success"><b>بسم الله الرحمن الرحيم</b></p>
</article>
