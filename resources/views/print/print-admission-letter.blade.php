<?php
/**
 * Admission Letter — rendered through DomPDF
 * Route: GET /print-admission-letter?id={student_id}
 *
 * DomPDF rules observed:
 *  - Images must use public_path() (absolute FS path), NOT asset() or url()
 *  - Layout via <table>, no flexbox/grid
 *  - position:fixed for watermark (repeats every page in DomPDF v2)
 *  - opacity on <img> tag, not body::before
 */
use App\Models\Document;
use App\Models\Enterprise;
use App\Models\AcademicClass;

// ── Student ───────────────────────────────────────────────────────────────────
$studentId = request('id');
if (!$studentId) abort(400, 'Student ID required. Add ?id={id} to the URL.');
$student = \App\Models\User::find($studentId);
if (!$student) abort(404, 'Student not found.');

// ── Enterprise ────────────────────────────────────────────────────────────────
$ent = Enterprise::find($student->enterprise_id);
if (!$ent) abort(404, 'School configuration not found.');

// ── Branding ──────────────────────────────────────────────────────────────────
$primaryColor   = $ent->color    ?: '#225b4c';
$secondaryColor = $ent->sec_color ?: '#c0392b';

// Absolute server paths — required for DomPDF image loading
$logoFsPath  = !empty($ent->logo)
    ? public_path('storage/' . ltrim($ent->logo, '/'))
    : null;
$hmSigFsPath = !empty($ent->hm_signature)
    ? public_path('storage/' . ltrim($ent->hm_signature, '/'))
    : null;
$logoExists  = $logoFsPath  && file_exists($logoFsPath);
$hmSigExists = $hmSigFsPath && file_exists($hmSigFsPath);
$hmName      = $ent->hm_name ?: 'HEAD TEACHER';

// ── Document template ─────────────────────────────────────────────────────────
$template = Document::where('enterprise_id', $student->enterprise_id)
    ->where('name', 'Admission letter')
    ->first();
if (!$template) {
    $template = new Document();
    $template->name             = 'Admission letter';
    $template->enterprise_id    = $student->enterprise_id;
    $template->print_hearder    = 1;
    $template->print_water_mark = 1;
    $template->body             = file_get_contents(public_path('templates/admission-letter.html'));
    $template->save();
}

// ── Class & Term ──────────────────────────────────────────────────────────────
$class      = $student->current_class_id ? AcademicClass::find($student->current_class_id) : null;
$activeTerm = $ent->active_term();
$activeYear = $activeTerm ? ($activeTerm->academic_year ?? null) : null;
$termLabel  = $activeTerm
    ? ('Term ' . $activeTerm->name . ', ' . ($activeYear->name ?? ''))
    : '—';

// ── Fee / services table ──────────────────────────────────────────────────────
$reqRows  = '';
$rowCount = 0;
$reqTotal = 0;
if ($class && $activeTerm) {
    foreach ($class->academic_class_fees as $fee) {
        if ($fee->due_term_id != $activeTerm->id) continue;
        $rowCount++;
        $reqTotal += $fee->amount;
        $bg = ($rowCount % 2 === 0) ? '#f4f9f6' : '#ffffff';
        $reqRows .= "
        <tr style='background:{$bg};'>
            <td style='text-align:center; width:8%; border:1px solid #cfdfd4; padding:4px 6px; color:#777;'>{$rowCount}</td>
            <td style='border:1px solid #cfdfd4; padding:4px 8px;'>" . e($fee->name) . "</td>
            <td style='text-align:right; border:1px solid #cfdfd4; padding:4px 8px; white-space:nowrap; font-weight:600;'>UGX " . number_format($fee->amount) . "/=</td>
        </tr>";
    }
    foreach ($student->active_term_services() as $svc) {
        $rowCount++;
        $reqTotal += $svc->total;
        $bg = ($rowCount % 2 === 0) ? '#f4f9f6' : '#ffffff';
        $svcName = e(($svc->service->name ?? 'Service') . ' ×' . $svc->quantity);
        $reqRows .= "
        <tr style='background:{$bg};'>
            <td style='text-align:center; width:8%; border:1px solid #cfdfd4; padding:4px 6px; color:#777;'>{$rowCount}</td>
            <td style='border:1px solid #cfdfd4; padding:4px 8px;'>{$svcName}</td>
            <td style='text-align:right; border:1px solid #cfdfd4; padding:4px 8px; white-space:nowrap; font-weight:600;'>UGX " . number_format($svc->total) . "/=</td>
        </tr>";
    }
}

if ($rowCount > 0) {
    $reqTable = "
<p style='margin:12px 0 4px 0; font-size:10.5px; font-weight:700; color:{$primaryColor}; text-transform:uppercase; letter-spacing:0.5px;'>
    Fee Requirements &mdash; {$termLabel}
</p>
<table style='width:100%; border-collapse:collapse; font-size:11px; margin-bottom:10px;'>
    <thead>
        <tr style='background:{$primaryColor}; color:#fff;'>
            <th style='width:8%; padding:5px 6px; text-align:center; border:1px solid {$primaryColor};'>#</th>
            <th style='padding:5px 8px; text-align:left; border:1px solid {$primaryColor};'>Description</th>
            <th style='width:26%; padding:5px 8px; text-align:right; border:1px solid {$primaryColor};'>Amount</th>
        </tr>
    </thead>
    <tbody>{$reqRows}</tbody>
    <tfoot>
        <tr style='background:{$primaryColor}; color:#fff; font-weight:700;'>
            <td colspan='2' style='padding:5px 8px; border:1px solid {$primaryColor};'>TOTAL DUE &mdash; {$termLabel}</td>
            <td style='padding:5px 8px; text-align:right; border:1px solid {$primaryColor}; white-space:nowrap;'>UGX " . number_format($reqTotal) . "/=</td>
        </tr>
    </tfoot>
</table>";
} else {
    $reqTable = "<p style='font-style:italic; color:#888; font-size:10px; margin:8px 0;'>
        Fee structure for {$termLabel} is not yet configured. Please contact the school bursar.
    </p>";
}

// ── Parent info ───────────────────────────────────────────────────────────────
$parentName  = $student->father_name  ?: ($student->mother_name  ?: '—');
$parentPhone = $student->father_phone ?: ($student->mother_phone ?: '—');

// ── Admission no. & verification ─────────────────────────────────────────────
$admNo            = $student->user_number
    ?: (($ent->short_name ?: 'ADM') . '-' . ($activeYear->name ?? date('Y')) . '-' . str_pad($student->id, 4, '0', STR_PAD_LEFT));
$verificationCode = strtoupper(substr(md5($student->id . $student->school_pay_payment_code . $student->enterprise_id), 0, 10));

// ── Placeholder replacements ──────────────────────────────────────────────────
$body = $template->body;
$replacements = [
    '[STUDENT_NAME]'             => e($student->name),
    '[STUDENT_FULL_NAME]'        => e($student->name),
    '[STUDENT_FIRST_NAME]'       => e($student->first_name ?: explode(' ', $student->name)[0]),
    '[STUDENT_CLASS]'            => e($class->name ?? 'Not Assigned'),
    '[STUDENT_ADMISSION_NUMBER]' => e($admNo),
    '[STUDENT_GENDER]'           => e($student->sex ?: 'N/A'),
    '[STUDENT_DOB]'              => $student->date_of_birth ? date('d F Y', strtotime($student->date_of_birth)) : 'N/A',
    '[STUDENT_NATIONALITY]'      => e($student->nationality ?: 'Ugandan'),
    '[STUDENT_RELIGION]'         => e($student->religion ?: 'N/A'),
    '[STUDENT_PHONE]'            => e($student->phone_number_1 ?: 'N/A'),
    '[STUDENT_SCHOOL_PAY_CODE]'  => e($student->school_pay_payment_code ?: 'Not assigned'),
    '[FATHER_NAME]'              => e($student->father_name ?: 'N/A'),
    '[MOTHER_NAME]'              => e($student->mother_name ?: 'N/A'),
    '[PARENT_NAME]'              => e($parentName),
    '[PARENT_PHONE]'             => e($parentPhone),
    '[FATHER_PHONE]'             => e($student->father_phone ?: 'N/A'),
    '[MOTHER_PHONE]'             => e($student->mother_phone ?: 'N/A'),
    '[SCHOOL_NAME]'              => e($ent->name),
    '[SCHOOL_ADDRESS]'           => e(trim(($ent->address ?? '') . ($ent->p_o_box ? ', P.O. Box ' . $ent->p_o_box : ''), ', ')),
    '[SCHOOL_PHONE]'             => e($ent->phone_number ?? ''),
    '[SCHOOL_PHONE_2]'           => e($ent->phone_number_2 ?? ''),
    '[SCHOOL_EMAIL]'             => e($ent->email ?? ''),
    '[SCHOOL_MOTTO]'             => e($ent->motto ?? ''),
    '[HEADTEACHER_NAME]'         => e($hmName),
    '[CURRENT_DATE]'             => date('d F Y'),
    '[ACADEMIC_YEAR]'            => e($activeYear->name ?? ''),
    '[ACTIVE_TERM]'              => $activeTerm ? 'Term ' . $activeTerm->name : '',
    '[REQUIREMENTS_TABLE]'       => $reqTable,
    '[REQUIREMENTS_TOTAL]'       => 'UGX ' . number_format($reqTotal) . '/=',
    '[VERIFICATION_CODE]'        => $verificationCode,
    // Strip Quill editor inline colour markers
    'background-color: rgb(249, 242, 244);' => '',
    'color: rgb(199, 37, 78);'              => '',
];
foreach ($replacements as $find => $replace) {
    $body = str_replace($find, $replace, $body);
}
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admission Letter &mdash; {{ $student->name }}</title>
  <style>
    @page {
      size: A4;
      margin: 16mm 18mm 20mm 18mm;
    }
    * { box-sizing: border-box; }

    body {
      font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
      font-size: 11px;
      line-height: 1.65;
      color: #1a1a1a;
      margin: 0;
      padding: 0;
    }

    /* ── Header ── */
    .hdr { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
    .hdr td { vertical-align: middle; padding: 0 4px; }
    .hdr .logo-cell { width: 82px; text-align: center; }
    .hdr .logo-cell img { width: 78px; height: 78px; object-fit: contain; display: block; margin: 0 auto; }
    .hdr .ghost-cell { width: 82px; text-align: center; }
    .hdr .ghost-cell img { width: 64px; height: 64px; object-fit: contain; display: block; margin: 0 auto; opacity: 0.13; }
    .hdr .info-cell { text-align: center; padding: 0 8px; }

    .school-name {
      font-size: 20px; font-weight: 800; text-transform: uppercase;
      color: <?= $primaryColor ?>; margin: 0 0 1px 0; letter-spacing: 0.8px; line-height: 1.2;
    }
    .school-motto { font-size: 10px; font-style: italic; color: #555; margin: 0 0 3px; line-height: 1.3; }
    .school-contacts { font-size: 9.5px; color: #444; line-height: 1.5; }
    .school-contacts strong { color: #222; }

    /* ── Dividers (demand-notice pattern) ── */
    .bar-primary   { height: 4px; background: <?= $primaryColor ?>;   margin: 8px 0 2px; }
    .bar-secondary { height: 2px; background: <?= $secondaryColor ?>; margin: 0 0 10px; }

    /* ── Title block ── */
    .title-band {
      background: <?= $primaryColor ?>;
      color: #ffffff;
      text-align: center;
      padding: 7px 10px;
      font-size: 14px;
      font-weight: 700;
      letter-spacing: 2px;
      text-transform: uppercase;
      margin-bottom: 6px;
    }

    /* ── Ref row ── */
    .ref-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
    .ref-table td { font-size: 10px; color: #555; padding: 0; vertical-align: top; }

    /* ── Section header band ── */
    .section-band {
      background: <?= $primaryColor ?>;
      color: #ffffff;
      font-size: 9.5px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.8px;
      padding: 3px 8px;
      margin: 10px 0 0 0;
    }

    /* ── Student info table ── */
    .info-tbl { width: 100%; border-collapse: collapse; font-size: 11px; margin-bottom: 10px; }
    .info-tbl td { padding: 5px 8px; border: 1px solid #cfdfd4; vertical-align: middle; }
    .info-tbl .lbl {
      font-weight: 700; color: #3a3a3a;
      background: #f3f9f5;
      width: 22%; white-space: nowrap;
    }
    .info-tbl .val { color: #111; }

    /* ── Letter body ── */
    .body-text { font-size: 11.5px; line-height: 1.55; margin: 8px 0; }
    .body-text p { margin: 0 0 5px 0; text-align: justify; }
    .body-text p:empty { display: none; margin: 0; }
    .body-text br + br { display: none; }
    .body-text ol, .body-text ul { padding-left: 18px; margin: 2px 0 6px; }
    .body-text li { margin-bottom: 2px; }
    .body-text table { width: 100%; border-collapse: collapse; font-size: 11px; margin: 8px 0; }
    .body-text table th { background: <?= $primaryColor ?>; color: #fff; padding: 5px 8px; border: 1px solid <?= $primaryColor ?>; }
    .body-text table td { border: 1px solid #ccc; padding: 4px 8px; }



    /* ── Signature section ── */
    .sig-tbl { width: 100%; border-collapse: collapse; margin-top: 22px; }
    .sig-tbl td {
      width: 33.3%; vertical-align: bottom;
      padding: 0 8px; text-align: center;
    }
    .sig-img { max-height: 48px; max-width: 110px; display: block; margin: 0 auto; }
    .sig-line {
      border-top: 1.5px solid #333;
      padding-top: 4px;
      margin-top: 46px;
      font-size: 10px; font-weight: 700;
    }
    .sig-line-tight { margin-top: 4px; }
    .sig-role { font-size: 9px; color: #555; margin-top: 1px; }
    .stamp-circle {
      width: 66px; height: 66px;
      border-radius: 50%;
      border: 1.5px dashed <?= $primaryColor ?>aa;
      display: block; margin: 0 auto 4px;
    }

    /* ── Verification box ── */
    .verify-box {
      border: 1px dashed #999;
      padding: 7px 12px;
      font-size: 10px;
      color: #555;
      text-align: center;
      margin-top: 14px;
    }
    .verify-code {
      font-family: 'Courier New', monospace;
      font-size: 14px; font-weight: 700;
      color: #111; letter-spacing: 3px;
    }

    /* ── Tear-off ── */
    .tearoff { border-top: 2px dashed #999; margin-top: 14px; padding-top: 7px; }
    .tearoff-hint { font-size: 9px; color: #aaa; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 5px; }
    .tearoff-tbl { width: 100%; border-collapse: collapse; font-size: 11px; }
    .tearoff-tbl td { padding: 3px 5px; }
    .tearoff-tbl .lbl { font-weight: 700; color: #444; width: 27%; }
    .tearoff-sign { border-bottom: 1px solid #333; }

    /* ── Footer ── */
    .page-footer {
      margin-top: 16px; text-align: center;
      font-size: 8.5px; color: #aaa;
      border-top: 1px solid #ddd; padding-top: 5px;
    }
  </style>
</head>
<body>

{{-- ── Watermark: fixed position centred on A4 content area ── --}}
@if ($template->print_water_mark && $logoExists)
<div style="position:fixed; top:85mm; left:37mm; width:100mm; z-index:-9999; text-align:center;">
  <img src="{{ $logoFsPath }}" style="width:100mm; opacity:0.07;" alt="">
</div>
@endif

{{-- ═══════════════════════════════════════════════════════
     SCHOOL HEADER
════════════════════════════════════════════════════════ --}}
@if ($template->print_hearder)
<table class="hdr">
  <tr>
    {{-- Left: school logo --}}
    <td class="logo-cell">
      @if ($logoExists)
        <img src="{{ $logoFsPath }}" alt="{{ $ent->name }}">
      @endif
    </td>

    {{-- Centre: school name, motto, contacts — all centred --}}
    <td class="info-cell">
      <div class="school-name">{{ $ent->name }}</div>
      @if ($ent->motto)
        <div class="school-motto">&ldquo;{{ $ent->motto }}&rdquo;</div>
      @endif
      <div class="school-contacts">
        @if ($ent->address)
          {{ $ent->address }}@if($ent->p_o_box), P.O. Box {{ $ent->p_o_box }}@endif<br>
        @endif
        @if ($ent->phone_number)
          <strong>Tel:</strong> {{ $ent->phone_number }}@if($ent->phone_number_2) / {{ $ent->phone_number_2 }}@endif
          @if ($ent->email) &nbsp;|&nbsp; <strong>Email:</strong> {{ $ent->email }}@endif
          <br>
        @endif
        @if ($ent->website)<strong>Web:</strong> {{ $ent->website }}@endif
      </div>
    </td>

    {{-- Right: faint ghost logo for visual balance --}}
    <td class="ghost-cell">
      @if ($logoExists)
        <img src="{{ $logoFsPath }}" alt="">
      @endif
    </td>
  </tr>
</table>

<div class="bar-primary"></div>
<div class="bar-secondary"></div>
@endif

{{-- ═══════════════════════════════════════════════════════
     DOCUMENT TITLE
════════════════════════════════════════════════════════ --}}
<div class="title-band">ADMISSION LETTER</div>

<table class="ref-table">
  <tr>
    <td>Ref:&nbsp;<strong>{{ $admNo }}</strong></td>
    <td style="text-align:right;">Date:&nbsp;<strong>{{ date('d F Y') }}</strong></td>
  </tr>
</table>

{{-- ═══════════════════════════════════════════════════════
     STUDENT DETAILS
════════════════════════════════════════════════════════ --}}
<div class="section-band">Student Details</div>
<table class="info-tbl">
  <tr>
    <td class="lbl">Full Name</td>
    <td class="val" colspan="3"><strong>{{ strtoupper($student->name) }}</strong></td>
  </tr>
  <tr>
    <td class="lbl">Admission No.</td>
    <td class="val"><strong>{{ $admNo }}</strong></td>
    <td class="lbl">Class</td>
    <td class="val"><strong>{{ $class->name ?? '&mdash;' }}</strong></td>
  </tr>
  <tr>
    <td class="lbl">Gender</td>
    <td class="val">{{ $student->sex ?: '&mdash;' }}</td>
    <td class="lbl">Date of Birth</td>
    <td class="val">
      {{ $student->date_of_birth ? date('d F Y', strtotime($student->date_of_birth)) : '&mdash;' }}
    </td>
  </tr>
  <tr>
    <td class="lbl">Parent / Guardian</td>
    <td class="val">{{ $parentName }}</td>
    <td class="lbl">Phone</td>
    <td class="val">{{ $parentPhone }}</td>
  </tr>
  @if ($termLabel !== '—')
  <tr>
    <td class="lbl">Academic Period</td>
    <td class="val" colspan="3">{{ $termLabel }}</td>
  </tr>
  @endif
  @if ($student->school_pay_payment_code)
  <tr>
    <td class="lbl">SchoolPay Code</td>
    <td class="val" colspan="3">
      <strong style="font-family:'Courier New',monospace; font-size:12px; color:<?= $primaryColor ?>; letter-spacing:1px;">
        {{ $student->school_pay_payment_code }}
      </strong>
      &nbsp;&mdash;&nbsp;Pay via MTN / Airtel Money or bank
    </td>
  </tr>
  @endif
</table>

{{-- ═══════════════════════════════════════════════════════
     LETTER BODY (from Document template with placeholders replaced)
════════════════════════════════════════════════════════ --}}
<div class="body-text">
  {!! $body !!}
</div>

{{-- ═══════════════════════════════════════════════════════
     SIGNATURES
════════════════════════════════════════════════════════ --}}
<table class="sig-tbl">
  <tr>
    {{-- Head Teacher --}}
    <td>
      @if ($hmSigExists)
        <img class="sig-img" src="{{ $hmSigFsPath }}" alt="Signature">
        <div class="sig-line sig-line-tight">{{ $hmName }}</div>
      @else
        <div class="sig-line">{{ $hmName }}</div>
      @endif
      <div class="sig-role">Head Teacher / Principal</div>
      <div class="sig-role">{{ $ent->name }}</div>
    </td>

    {{-- Official stamp --}}
    <td>
      <div class="stamp-circle"></div>
      <div class="sig-line" style="margin-top:0;">Official School Stamp</div>
    </td>

    {{-- Parent / Guardian --}}
    <td>
      <div class="sig-line">Parent / Guardian</div>
      <div class="sig-role">Signature &amp; Date</div>
      <div class="sig-role" style="margin-top:6px; font-size:8.5px; color:#bbb;">
        I / We confirm receipt of this admission letter.
      </div>
    </td>
  </tr>
</table>

{{-- ═══════════════════════════════════════════════════════
     VERIFICATION CODE
════════════════════════════════════════════════════════ --}}
<div class="verify-box">
  <div>
    Document Verification Code: &nbsp;
    <span class="verify-code">{{ $verificationCode }}</span>
  </div>
  <div style="margin-top:3px; font-size:9px;">
    Issued: {{ date('d M Y') }} &nbsp;&bull;&nbsp; Valid for 30 days
    @if ($ent->phone_number)
      &nbsp;&bull;&nbsp; To verify, contact {{ $ent->name }} &mdash; {{ $ent->phone_number }}
    @endif
  </div>
</div>

{{-- ═══════════════════════════════════════════════════════
     TEAR-OFF ACKNOWLEDGEMENT SLIP
════════════════════════════════════════════════════════ --}}
<div class="tearoff">
  <div class="tearoff-hint">&#9988; Cut here &mdash; Parent / Guardian returns this portion to the school</div>
  <table class="tearoff-tbl">
    <tr>
      <td class="lbl">Student Name:</td>
      <td>{{ strtoupper($student->name) }}</td>
      <td class="lbl">Admission No.:</td>
      <td>{{ $admNo }}</td>
    </tr>
    <tr>
      <td class="lbl">Class:</td>
      <td>{{ $class->name ?? '&mdash;' }}</td>
      <td class="lbl">SchoolPay Code:</td>
      <td style="font-family:'Courier New',monospace; font-weight:700; font-size:11px;">
        {{ $student->school_pay_payment_code ?? '&mdash;' }}
      </td>
    </tr>
    <tr>
      <td class="lbl" style="padding-top:14px;">Parent Signature:</td>
      <td class="tearoff-sign" style="padding-top:14px;">&nbsp;</td>
      <td class="lbl" style="padding-top:14px;">Date:</td>
      <td class="tearoff-sign" style="padding-top:14px;">&nbsp;</td>
    </tr>
  </table>
</div>

{{-- ── Footer ── --}}
<div class="page-footer">
  Generated by School Management System on {{ date('d F Y, h:i A') }}
  &nbsp;&bull;&nbsp; Ref: {{ $admNo }}
</div>

</body>
</html>
