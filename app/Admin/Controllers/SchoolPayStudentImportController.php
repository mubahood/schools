<?php

namespace App\Admin\Controllers;

use App\Models\AcademicClass;
use App\Models\Term;
use App\Models\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SchoolPayStudentImportController extends AdminController
{
    protected $title = 'SchoolPay Student Import';

    // ─── INDEX ───────────────────────────────────────────────────────────────

    public function index(Content $content)
    {
        $u = Admin::user();

        $spStudents = DB::table('school_pay_transactions')
            ->where('enterprise_id', $u->enterprise_id)
            ->whereNotNull('studentPaymentCode')
            ->where('studentPaymentCode', '!=', '')
            ->select(
                'studentPaymentCode',
                DB::raw('MAX(studentName) as studentName'),
                DB::raw('MAX(studentClass) as studentClass'),
                DB::raw('MAX(studentRegistrationNumber) as studentRegistrationNumber'),
                DB::raw('COUNT(*) as transaction_count'),
                DB::raw('SUM(amount) as total_paid'),
                DB::raw('MAX(payment_date) as last_transaction')
            )
            ->groupBy('studentPaymentCode')
            ->orderByDesc('last_transaction')
            ->get();

        $localStudents = User::where('enterprise_id', $u->enterprise_id)
            ->where('user_type', 'student')
            ->whereNotNull('school_pay_payment_code')
            ->where('school_pay_payment_code', '!=', '')
            ->get(['id', 'name', 'school_pay_payment_code', 'current_class_id', 'status'])
            ->keyBy('school_pay_payment_code');

        $unmatchedCount = 0;
        foreach ($spStudents as $s) {
            $s->local = $localStudents->get($s->studentPaymentCode);
            if (!$s->local) $unmatchedCount++;
        }

        $filterStatus = request('filter_status', 'all');
        $filterName   = trim(request('filter_name', ''));
        $filtered = $spStudents->filter(function ($s) use ($filterStatus, $filterName) {
            if ($filterStatus === 'unmatched' && $s->local) return false;
            if ($filterStatus === 'matched'   && !$s->local) return false;
            if ($filterName &&
                stripos($s->studentName ?? '', $filterName) === false &&
                stripos($s->studentPaymentCode, $filterName) === false) {
                return false;
            }
            return true;
        });

        $importUrl   = admin_url('school-pay-student-import');
        $previewBase = admin_url('school-pay-student-import/preview');

        return $content
            ->title($this->title)
            ->description('Students found in SchoolPay transaction history')
            ->body($this->buildIndexHtml(
                $filtered, $spStudents->count(), $unmatchedCount,
                $filterStatus, $filterName, $importUrl, $previewBase
            ));
    }

    private function buildIndexHtml($students, $total, $unmatchedCount, $filterStatus, $filterName, $importUrl, $previewBase)
    {
        $matchedCount = $total - $unmatchedCount;
        ob_start(); ?>
<!-- Quick import by code -->
<div class="box box-success">
  <div class="box-header with-border">
    <h3 class="box-title"><i class="fa fa-plus-circle"></i> Import / Look Up by SchoolPay Code</h3>
  </div>
  <div class="box-body">
    <p class="text-muted" style="margin-bottom:12px">
      Enter any SchoolPay payment code — whether the student has paid before or not — to look them up and register them.
    </p>
    <div class="input-group" style="max-width:520px">
      <span class="input-group-addon"><i class="fa fa-barcode"></i></span>
      <input type="text" id="sp-code-input" class="form-control input-lg"
             placeholder="e.g. 1009929753" maxlength="40"
             style="font-family:monospace;letter-spacing:1px">
      <span class="input-group-btn">
        <button class="btn btn-success btn-lg" type="button" onclick="spGoPreview()">
          <i class="fa fa-search"></i> Look Up &amp; Preview
        </button>
      </span>
    </div>
    <div id="sp-code-err" class="text-danger" style="margin-top:6px;display:none">
      <i class="fa fa-exclamation-circle"></i> Please enter a payment code.
    </div>
  </div>
</div>
<script>
function spGoPreview() {
  var v = document.getElementById('sp-code-input').value.trim();
  if (!v) { document.getElementById('sp-code-err').style.display='block'; return; }
  document.getElementById('sp-code-err').style.display='none';
  window.location.href = '<?= rtrim($previewBase, '/') ?>/' + encodeURIComponent(v);
}
document.getElementById('sp-code-input').addEventListener('keydown', function(e){ if(e.key==='Enter') spGoPreview(); });
</script>

<!-- Transaction-based list -->
<div class="box box-primary">
  <div class="box-header with-border">
    <h3 class="box-title"><i class="fa fa-list"></i> Students from SchoolPay Transactions</h3>
    <div class="box-tools pull-right">
      <span class="label label-default"><?= $total ?> Total</span> &nbsp;
      <span class="label label-success"><?= $matchedCount ?> Registered</span> &nbsp;
      <span class="label label-warning"><?= $unmatchedCount ?> Not Yet Registered</span>
    </div>
  </div>
  <div class="box-body">
    <form method="GET" action="<?= $importUrl ?>" class="form-inline" style="margin-bottom:15px">
      <div class="input-group" style="margin-right:8px">
        <span class="input-group-addon"><i class="fa fa-search"></i></span>
        <input type="text" name="filter_name" class="form-control" placeholder="Search name or code..."
               value="<?= htmlspecialchars($filterName) ?>">
      </div>
      <select name="filter_status" class="form-control" style="margin-right:8px">
        <option value="all"<?= $filterStatus==='all'?' selected':'' ?>>All</option>
        <option value="unmatched"<?= $filterStatus==='unmatched'?' selected':'' ?>>Not Yet Registered</option>
        <option value="matched"<?= $filterStatus==='matched'?' selected':'' ?>>Already Registered</option>
      </select>
      <button type="submit" class="btn btn-primary"><i class="fa fa-filter"></i> Filter</button>
      <a href="<?= $importUrl ?>" class="btn btn-default"><i class="fa fa-times"></i> Clear</a>
    </form>
    <div class="table-responsive">
      <table class="table table-bordered table-striped table-hover" style="font-size:13px">
        <thead><tr>
          <th>#</th><th>Payment Code</th><th>Name (SchoolPay)</th><th>Class</th>
          <th>Txns</th><th>Total Paid</th><th>Last Payment</th><th>Status</th><th>Actions</th>
        </tr></thead>
        <tbody>
<?php $i = 1; foreach ($students as $s): $matched = (bool)$s->local; ?>
          <tr class="<?= $matched ? '' : 'warning' ?>">
            <td><?= $i++ ?></td>
            <td><code style="font-size:12px"><?= htmlspecialchars($s->studentPaymentCode) ?></code></td>
            <td><?= htmlspecialchars($s->studentName ?? '—') ?></td>
            <td><small><?= htmlspecialchars($s->studentClass ?? '—') ?></small></td>
            <td><span class="badge"><?= (int)$s->transaction_count ?></span></td>
            <td><small>UGX <?= number_format((int)$s->total_paid) ?></small></td>
            <td><small><?= $s->last_transaction ? date('d M Y', strtotime($s->last_transaction)) : '—' ?></small></td>
            <td>
<?php if ($matched): ?>
              <span class="label label-success"><i class="fa fa-check"></i> Registered</span>
              <br><small class="text-muted"><?= htmlspecialchars($s->local->name) ?></small>
<?php else: ?>
              <span class="label label-warning"><i class="fa fa-clock-o"></i> Not Registered</span>
<?php endif; ?>
            </td>
            <td nowrap>
              <a href="<?= $previewBase ?>/<?= urlencode($s->studentPaymentCode) ?>"
                 class="btn btn-xs <?= $matched ? 'btn-default' : 'btn-warning' ?>">
                <i class="fa fa-<?= $matched ? 'eye' : 'user-plus' ?>"></i> <?= $matched ? 'View' : 'Register' ?>
              </a>
<?php if ($matched): ?>
              <a href="<?= admin_url('students/'.$s->local->id.'/edit') ?>" class="btn btn-xs btn-info" title="Edit Profile">
                <i class="fa fa-edit"></i>
              </a>
              <a href="<?= url('print-admission-letter?id='.$s->local->id) ?>" target="_blank" class="btn btn-xs btn-success" title="Print Admission Letter">
                <i class="fa fa-file-text-o"></i> Letter
              </a>
<?php endif; ?>
            </td>
          </tr>
<?php endforeach; ?>
<?php if ($students->isEmpty()): ?>
          <tr><td colspan="9" class="text-center text-muted" style="padding:20px">
            No students found<?= $filterName || $filterStatus !== 'all' ? ' matching the filter' : ' — no SchoolPay transaction history yet' ?>.
          </td></tr>
<?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php
        return ob_get_clean();
    }

    // ─── PREVIEW ─────────────────────────────────────────────────────────────

    public function preview($code, Content $content)
    {
        $u   = Admin::user();
        $code = trim($code);

        // Transaction history for this code
        $transactions = DB::table('school_pay_transactions')
            ->where('enterprise_id', $u->enterprise_id)
            ->where('studentPaymentCode', $code)
            ->orderByDesc('payment_date')
            ->get();

        $hasTransactions = $transactions->isNotEmpty();
        $latest = $hasTransactions ? $transactions->first() : null;

        // Check if already registered in THIS school
        $existing = User::where('enterprise_id', $u->enterprise_id)
            ->where('user_type', 'student')
            ->where('school_pay_payment_code', $code)
            ->first();

        // Check if code is used by a student in ANOTHER school (global conflict)
        $codeConflict = null;
        if (!$existing) {
            $codeConflict = User::where('user_type', 'student')
                ->where('school_pay_payment_code', $code)
                ->where('enterprise_id', '!=', $u->enterprise_id)
                ->first();
        }

        // Name parsing — from SchoolPay or from existing student
        $spName = $latest->studentName ?? '';
        if ($existing) {
            // Pre-fill from existing student data
            $firstName  = request()->old('first_name',  $existing->first_name  ?? '');
            $middleName = request()->old('given_name',  $existing->given_name  ?? '');
            $lastName   = request()->old('last_name',   $existing->last_name   ?? '');
        } else {
            // Parse SchoolPay name
            [$firstName, $middleName, $lastName] = $this->parseName($spName);
            $firstName  = request()->old('first_name',  $firstName);
            $middleName = request()->old('given_name',  $middleName);
            $lastName   = request()->old('last_name',   $lastName);
        }

        // Duplicate name check (other students with same name)
        $nameDuplicates = [];
        $searchName = trim(($firstName ?? '') . ' ' . ($lastName ?? ''));
        if (strlen($searchName) > 3) {
            $nameDuplicates = User::where('enterprise_id', $u->enterprise_id)
                ->where('user_type', 'student')
                ->where(function ($q) use ($firstName, $lastName) {
                    $q->where(function ($q2) use ($firstName, $lastName) {
                        $q2->where('first_name', 'like', "%{$firstName}%")
                           ->where('last_name',  'like', "%{$lastName}%");
                    })->orWhere('name', 'like', "%{$firstName}%{$lastName}%");
                })
                ->where('school_pay_payment_code', '!=', $code)
                ->get(['id', 'name', 'school_pay_payment_code', 'current_class_id']);
        }

        // Classes (with academic year) & terms
        $classes    = AcademicClass::where('enterprise_id', $u->enterprise_id)
            ->with('academic_year')
            ->orderByDesc('academic_year_id')
            ->orderBy('name')
            ->get(['id', 'name', 'academic_year_id']);
        $terms      = Term::where('enterprise_id', $u->enterprise_id)
            ->with('academic_year')
            ->orderByDesc('id')
            ->get();
        $activeTerm = $u->ent->active_term();

        // Auto-suggest matching class from SchoolPay class name
        $spClass          = $latest->studentClass ?? '';
        $suggestedClassId = $this->guessClassId($spClass, $classes);

        // If editing existing, prefer their current class
        if ($existing && $existing->current_class_id) {
            $suggestedClassId = $existing->current_class_id;
        }
        $suggestedClassId = (int) request()->old('current_class_id', $suggestedClassId);

        // Derive suggested term: pick active term if it shares the same year as the suggested class,
        // otherwise pick the most recent term in that class's year
        $suggestedClass   = $classes->firstWhere('id', $suggestedClassId);
        $suggestedTermId  = null;
        if ($suggestedClass) {
            $classYearId = $suggestedClass->academic_year_id;
            if ($activeTerm && $activeTerm->academic_year_id == $classYearId) {
                $suggestedTermId = $activeTerm->id;
            } else {
                $termInYear = $terms->firstWhere('academic_year_id', $classYearId);
                $suggestedTermId = $termInYear ? $termInYear->id : ($activeTerm ? $activeTerm->id : null);
            }
        } elseif ($activeTerm) {
            $suggestedTermId = $activeTerm->id;
        }
        $suggestedTermId = (int) request()->old('term_id', $suggestedTermId);

        // Build class_id → academic_year_id map for JS linking
        $classYearMap = $classes->pluck('academic_year_id', 'id')->toArray();
        // Build academic_year_id → best term_id map for JS linking
        $yearTermMap  = [];
        foreach ($terms as $t) {
            if (!isset($yearTermMap[$t->academic_year_id])) {
                $yearTermMap[$t->academic_year_id] = $t->id; // first = most recent (ordered desc)
            }
        }
        if ($activeTerm) {
            $yearTermMap[$activeTerm->academic_year_id] = $activeTerm->id; // always prefer active
        }

        $registerUrl = admin_url('school-pay-student-import/register');
        $backUrl     = admin_url('school-pay-student-import');

        // Flash messages from previous (failed) submission
        $flashError   = session('import_error');
        $flashSuccess = null; // success always redirects away; only errors return here

        return $content
            ->title($existing
                ? 'Update Student: ' . e($existing->name)
                : 'Register Student — Code ' . e($code))
            ->description($hasTransactions
                ? number_format($transactions->count()) . ' transaction(s) found — UGX ' . number_format($transactions->sum('amount')) . ' total paid'
                : 'No transaction history — manual registration')
            ->body($this->buildPreviewHtml(
                $code, $latest, $transactions, $hasTransactions,
                $existing, $codeConflict, $nameDuplicates,
                $firstName, $middleName, $lastName,
                $classes, $terms, $activeTerm, $suggestedClassId, $suggestedTermId,
                $classYearMap, $yearTermMap,
                $registerUrl, $backUrl, $flashError, $flashSuccess
            ));
    }

    private function buildPreviewHtml(
        $code, $latest, $transactions, $hasTransactions,
        $existing, $codeConflict, $nameDuplicates,
        $firstName, $middleName, $lastName,
        $classes, $terms, $_activeTerm, $suggestedClassId, $suggestedTermId,
        $_classYearMap, $yearTermMap,
        $registerUrl, $backUrl, $flashError, $flashSuccess
    ) {
        $old = fn($key, $default = '') => htmlspecialchars(request()->old($key, $default) ?? $default);

        $letterUrl = $existing ? url('print-admission-letter?id=' . $existing->id) : null;

        ob_start();
?>
<style>
.sp-alert { border-left-width: 5px; border-radius: 4px; padding: 14px 18px; margin-bottom: 14px; }
.sp-alert .sp-alert-title { font-size: 15px; font-weight: 700; margin-bottom: 4px; display: flex; align-items: center; gap: 8px; }
.sp-alert .sp-alert-body  { font-size: 13px; margin: 0; }
.sp-alert .sp-alert-actions { margin-top: 12px; display: flex; gap: 8px; flex-wrap: wrap; }
.sp-alert-success { background: #f0fdf4; border-color: #22c55e; color: #14532d; }
.sp-alert-info    { background: #eff6ff; border-color: #3b82f6; color: #1e3a5f; }
.sp-alert-warning { background: #fffbeb; border-color: #f59e0b; color: #78350f; }
.sp-alert-danger  { background: #fef2f2; border-color: #ef4444; color: #7f1d1d; }
.sp-alert-success .fa, .sp-alert-info .fa, .sp-alert-warning .fa, .sp-alert-danger .fa { font-size: 16px; }
</style>

<a href="<?= $backUrl ?>" class="btn btn-default btn-sm" style="margin-bottom:14px">
  <i class="fa fa-arrow-left"></i> Back to List
</a>

<?php if ($flashError): ?>
<div class="sp-alert sp-alert-danger alert-dismissible">
  <button type="button" class="close" data-dismiss="alert" style="color:inherit">&times;</button>
  <div class="sp-alert-title"><i class="fa fa-times-circle"></i> Error</div>
  <div class="sp-alert-body"><?= $flashError /* already safe HTML */ ?></div>
</div>
<?php endif; ?>

<?php if ($flashSuccess): ?>
<div class="sp-alert sp-alert-success alert-dismissible">
  <button type="button" class="close" data-dismiss="alert" style="color:inherit">&times;</button>
  <div class="sp-alert-title"><i class="fa fa-check-circle"></i> Success</div>
  <div class="sp-alert-body"><?= htmlspecialchars($flashSuccess) ?></div>
</div>
<?php endif; ?>

<?php /* ── Student already registered ── high-priority banner */ ?>
<?php if ($existing): ?>
<div class="sp-alert sp-alert-info">
  <div class="sp-alert-title">
    <i class="fa fa-user-circle"></i>
    Student Already Registered in This School
    <span style="margin-left:auto;font-size:12px;font-weight:400;opacity:.8">ID #<?= $existing->id ?></span>
  </div>
  <div class="sp-alert-body">
    <strong style="font-size:14px;"><?= htmlspecialchars($existing->name) ?></strong>
    <?php $cls = $existing->current_class ?? null; ?>
    <?php if ($cls): ?>&nbsp;&mdash;&nbsp;<span><?= htmlspecialchars($cls->name ?? '') ?></span><?php endif; ?>
    <br>
    The form below is pre-filled with their current data. Edit any field and click <strong>Save Changes</strong>.
  </div>
  <div class="sp-alert-actions">
    <a href="<?= admin_url('students/'.$existing->id.'/edit') ?>" class="btn btn-sm btn-primary">
      <i class="fa fa-pencil"></i> Edit Full Profile
    </a>
    <a href="<?= $letterUrl ?>" target="_blank" class="btn btn-sm btn-success">
      <i class="fa fa-file-text-o"></i> Print Admission Letter
    </a>
    <a href="<?= admin_url('accounts?administrator_id='.$existing->id) ?>" class="btn btn-sm btn-default">
      <i class="fa fa-usd"></i> Fee Account
    </a>
    <a href="<?= admin_url('student-report-cards?student_id='.$existing->id) ?>" class="btn btn-sm btn-default">
      <i class="fa fa-file-text"></i> Report Cards
    </a>
  </div>
</div>
<?php endif; ?>

<?php if ($codeConflict): ?>
<div class="sp-alert sp-alert-warning">
  <div class="sp-alert-title"><i class="fa fa-exclamation-triangle"></i> Payment Code Registered at Another School</div>
  <div class="sp-alert-body">
    Code <code style="background:rgba(0,0,0,.08);padding:1px 5px;border-radius:3px"><?= htmlspecialchars($code) ?></code>
    is assigned to <strong><?= htmlspecialchars($codeConflict->name) ?></strong> in a different school.
    You can still register a student with this code here — both records can co-exist.
  </div>
</div>
<?php endif; ?>

<?php if (!$hasTransactions): ?>
<div class="sp-alert sp-alert-warning">
  <div class="sp-alert-title"><i class="fa fa-clock-o"></i> No Transaction History Found</div>
  <div class="sp-alert-body">
    Code <code style="background:rgba(0,0,0,.08);padding:1px 5px;border-radius:3px"><?= htmlspecialchars($code) ?></code>
    has no SchoolPay payments on record yet.
    You can still <?= $existing ? 'update' : 'register' ?> the student — the code will be saved to their profile and future payments will link automatically.
  </div>
</div>
<?php endif; ?>

<?php if ($nameDuplicates->isNotEmpty()): ?>
<div class="sp-alert sp-alert-warning">
  <div class="sp-alert-title"><i class="fa fa-copy"></i> Possible Duplicate — Similar Name Found</div>
  <div class="sp-alert-body">
    <ul style="margin: 6px 0 0 0; padding-left: 20px;">
      <?php foreach ($nameDuplicates as $d): ?>
      <li style="margin-bottom:4px">
        <strong><?= htmlspecialchars($d->name) ?></strong>
        <?= $d->school_pay_payment_code ? '&nbsp;— Code: <code>'.htmlspecialchars($d->school_pay_payment_code).'</code>' : '&nbsp;— No SchoolPay code' ?>
        &nbsp;
        <a href="<?= admin_url('students/'.$d->id.'/edit') ?>" target="_blank"
           style="font-size:12px;text-decoration:underline;color:inherit;">
          View Profile <i class="fa fa-external-link" style="font-size:10px"></i>
        </a>
      </li>
      <?php endforeach; ?>
    </ul>
    <small style="opacity:.8">Verify before saving to avoid creating duplicates. Use the checkbox below if you are sure this is a different student.</small>
  </div>
</div>
<?php endif; ?>

<div class="row">

  <?php /* ── LEFT: SchoolPay data panel ── */ ?>
  <div class="col-md-4">

    <?php if ($hasTransactions): ?>
    <div class="box box-info">
      <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-university"></i> SchoolPay Data</h3>
      </div>
      <div class="box-body" style="padding:10px">
        <table class="table table-condensed" style="margin-bottom:0;font-size:13px">
          <tr><th style="width:45%">Code</th><td><code><?= htmlspecialchars($code) ?></code></td></tr>
          <tr><th>Name</th><td><strong><?= htmlspecialchars($latest->studentName ?? '—') ?></strong></td></tr>
          <tr><th>Class</th><td><?= htmlspecialchars($latest->studentClass ?? '—') ?></td></tr>
          <tr><th>Reg. No.</th><td><?= htmlspecialchars($latest->studentRegistrationNumber ?? '—') ?></td></tr>
          <tr><th>Transactions</th><td><span class="badge"><?= $transactions->count() ?></span></td></tr>
          <tr><th>Total Paid</th>
            <td><strong class="text-success">UGX <?= number_format($transactions->sum('amount')) ?></strong></td></tr>
          <tr><th>Last Payment</th>
            <td><small><?= $latest->payment_date ? date('d M Y', strtotime($latest->payment_date)) : '—' ?></small></td></tr>
        </table>
      </div>
    </div>

    <div class="box box-default box-solid" style="max-height:320px;overflow:hidden">
      <div class="box-header with-border" style="padding:8px 12px">
        <h3 class="box-title" style="font-size:13px"><i class="fa fa-list"></i> Payment History</h3>
        <div class="box-tools pull-right">
          <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
        </div>
      </div>
      <div class="box-body" style="padding:0;overflow-y:auto;max-height:260px">
        <table class="table table-condensed table-striped" style="font-size:12px;margin:0">
          <thead><tr>
            <th style="padding:4px 8px">Date</th>
            <th style="padding:4px 8px">Amount</th>
            <th style="padding:4px 8px">Channel</th>
          </tr></thead>
          <tbody>
<?php foreach ($transactions as $t): ?>
            <tr>
              <td style="padding:3px 8px"><?= $t->payment_date ? date('d M Y', strtotime($t->payment_date)) : '—' ?></td>
              <td style="padding:3px 8px">UGX <?= number_format($t->amount) ?></td>
              <td style="padding:3px 8px"><small><?= htmlspecialchars($t->sourcePaymentChannel ?? '—') ?></small></td>
            </tr>
<?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <?php else: ?>
    <div class="box box-warning">
      <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-barcode"></i> Payment Code</h3>
      </div>
      <div class="box-body text-center" style="padding:25px">
        <p style="font-size:22px;font-family:monospace;letter-spacing:2px;margin-bottom:8px">
          <code><?= htmlspecialchars($code) ?></code>
        </p>
        <span class="label label-warning">No transaction history</span>
        <p class="text-muted" style="margin-top:12px;font-size:12px">
          This code will be assigned to the student once registered.
          Future SchoolPay payments with this code will automatically link.
        </p>
      </div>
    </div>
    <?php endif; ?>

  </div><!-- /col-md-4 -->

  <?php /* ── RIGHT: Registration form ── */ ?>
  <div class="col-md-8">
    <div class="box <?= $existing ? 'box-warning' : 'box-success' ?>">
      <div class="box-header with-border">
        <h3 class="box-title">
          <i class="fa fa-<?= $existing ? 'pencil' : 'user-plus' ?>"></i>
          <?= $existing ? 'Update Student Profile' : 'Register New Student' ?>
        </h3>
        <?php if ($existing): ?>
        <div class="box-tools pull-right">
          <span class="label label-default">Updating ID #<?= $existing->id ?></span>
        </div>
        <?php endif; ?>
      </div>
      <div class="box-body">

        <?php /* Show validation errors if any */ ?>
        <?php if ($errors = session('_errors') ?? null): ?>
        <div class="alert alert-danger">
          <ul style="margin:0;padding-left:18px">
            <?php foreach ($errors->all() as $e): ?>
            <li><?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
        <?php endif; ?>

        <form action="<?= $registerUrl ?>" method="POST" id="sp-register-form">
          <?= csrf_field() ?>
          <input type="hidden" name="school_pay_payment_code" value="<?= htmlspecialchars($code) ?>">
          <?php if ($existing): ?>
          <input type="hidden" name="existing_id" value="<?= $existing->id ?>">
          <?php endif; ?>

          <?php /* ── Name ── */ ?>
          <div class="row">
            <div class="col-sm-4">
              <div class="form-group">
                <label>First Name <span class="text-danger">*</span></label>
                <input type="text" name="first_name" class="form-control"
                       value="<?= htmlspecialchars($firstName) ?>" required autofocus>
              </div>
            </div>
            <div class="col-sm-4">
              <div class="form-group">
                <label>Middle Name</label>
                <input type="text" name="given_name" class="form-control"
                       value="<?= htmlspecialchars($middleName) ?>">
              </div>
            </div>
            <div class="col-sm-4">
              <div class="form-group">
                <label>Last Name <span class="text-danger">*</span></label>
                <input type="text" name="last_name" class="form-control"
                       value="<?= htmlspecialchars($lastName) ?>" required>
              </div>
            </div>
          </div>

          <?php /* ── Gender + DOB ── */ ?>
          <div class="row">
            <div class="col-sm-4">
              <div class="form-group">
                <label>Gender</label>
                <select name="sex" class="form-control">
                  <option value="">— Select —</option>
                  <?php foreach (['Male','Female'] as $g): ?>
                  <option value="<?= $g ?>"<?= ($old('sex', $existing->sex ?? '') === $g) ? ' selected' : '' ?>><?= $g ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="col-sm-4">
              <div class="form-group">
                <label>Date of Birth</label>
                <input type="date" name="date_of_birth" class="form-control"
                       value="<?= $old('date_of_birth', $existing->date_of_birth ?? '') ?>">
              </div>
            </div>
            <div class="col-sm-4">
              <div class="form-group">
                <label>Status</label>
                <select name="status" class="form-control">
                  <?php $statusVal = (int)($old('status', $existing->status ?? 1)); ?>
                  <option value="1"<?= $statusVal===1?' selected':'' ?>>Active</option>
                  <option value="2"<?= $statusVal===2?' selected':'' ?>>Pending / Not Enrolled</option>
                  <option value="0"<?= $statusVal===0?' selected':'' ?>>Inactive</option>
                </select>
              </div>
            </div>
          </div>

          <?php
            // Group classes by academic year for optgroups
            $classesByYear = [];
            foreach ($classes as $cls) {
                $yearLabel = $cls->academic_year->name ?? 'Unknown Year';
                $classesByYear[$yearLabel][] = $cls;
            }
            // Group terms by academic year
            $termsByYear = [];
            foreach ($terms as $t) {
                $yearLabel = $t->academic_year->name ?? 'Unknown Year';
                $termsByYear[$yearLabel][] = $t;
            }
          ?>
          <?php /* ── Class + Term ── */ ?>
          <div class="row">
            <div class="col-sm-6">
              <div class="form-group">
                <label>Class</label>
                <?php if ($hasTransactions && ($latest->studentClass ?? '')): ?>
                <small class="text-muted" style="float:right">
                  SchoolPay: <strong><?= htmlspecialchars($latest->studentClass) ?></strong>
                </small>
                <?php endif; ?>
                <select name="current_class_id" id="sp-class-select" class="form-control">
                  <option value="">— Select class —</option>
                  <?php foreach ($classesByYear as $yearLabel => $yearClasses): ?>
                  <optgroup label="<?= htmlspecialchars($yearLabel) ?>">
                    <?php foreach ($yearClasses as $cls): ?>
                    <option value="<?= $cls->id ?>"
                            data-year="<?= $cls->academic_year_id ?>"
                            <?= ($suggestedClassId && $suggestedClassId == $cls->id) ? ' selected' : '' ?>>
                      <?= htmlspecialchars($cls->name) ?> — <?= htmlspecialchars($cls->academic_year->name ?? '') ?>
                    </option>
                    <?php endforeach; ?>
                  </optgroup>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group">
                <label>Enroll in Term</label>
                <select name="term_id" id="sp-term-select" class="form-control">
                  <option value="">— No term enrollment —</option>
                  <?php foreach ($termsByYear as $yearLabel => $yearTerms): ?>
                  <optgroup label="<?= htmlspecialchars($yearLabel) ?>">
                    <?php foreach ($yearTerms as $t): ?>
                    <option value="<?= $t->id ?>"
                            data-year="<?= $t->academic_year_id ?>"
                            <?= ($suggestedTermId && $suggestedTermId == $t->id) ? ' selected' : '' ?>>
                      Term <?= htmlspecialchars($t->name) ?> — <?= htmlspecialchars($t->academic_year->name ?? '') ?>
                    </option>
                    <?php endforeach; ?>
                  </optgroup>
                  <?php endforeach; ?>
                </select>
                <span class="help-block" style="font-size:11px">Creates a class enrollment record for this term.</span>
              </div>
            </div>
          </div>
          <script>
          // When class changes, auto-select the best matching term for that year
          (function() {
            var yearTermMap = <?= json_encode($yearTermMap) ?>;
            document.getElementById('sp-class-select').addEventListener('change', function() {
              var opt = this.options[this.selectedIndex];
              var yearId = opt ? opt.getAttribute('data-year') : null;
              if (!yearId) return;
              var bestTerm = yearTermMap[yearId];
              if (!bestTerm) return;
              var termSel = document.getElementById('sp-term-select');
              for (var i = 0; i < termSel.options.length; i++) {
                if (parseInt(termSel.options[i].value) === parseInt(bestTerm)) {
                  termSel.selectedIndex = i;
                  break;
                }
              }
            });
          })();
          </script>

          <?php /* ── Student number ── */ ?>
          <div class="row">
            <div class="col-sm-6">
              <div class="form-group">
                <label>Admission / Reg. Number</label>
                <input type="text" name="user_number" class="form-control"
                       value="<?= $old('user_number', $existing->user_number ?? ($latest->studentRegistrationNumber ?? '')) ?>"
                       placeholder="e.g. KJS/2025/001">
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group">
                <label>Nationality</label>
                <input type="text" name="nationality" class="form-control"
                       value="<?= $old('nationality', $existing->nationality ?? 'Ugandan') ?>"
                       placeholder="e.g. Ugandan">
              </div>
            </div>
          </div>

          <?php /* ── Contact ── */ ?>
          <div class="row">
            <div class="col-sm-4">
              <div class="form-group">
                <label>Student Phone</label>
                <input type="text" name="phone_number_1" class="form-control"
                       value="<?= $old('phone_number_1', $existing->phone_number_1 ?? '') ?>"
                       placeholder="07XXXXXXXX">
              </div>
            </div>
            <div class="col-sm-4">
              <div class="form-group">
                <label>Father's Name</label>
                <input type="text" name="father_name" class="form-control"
                       value="<?= $old('father_name', $existing->father_name ?? '') ?>">
              </div>
            </div>
            <div class="col-sm-4">
              <div class="form-group">
                <label>Father's Phone</label>
                <input type="text" name="father_phone" class="form-control"
                       value="<?= $old('father_phone', $existing->father_phone ?? '') ?>"
                       placeholder="07XXXXXXXX">
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-sm-4">
              <div class="form-group">
                <label>Mother's Name</label>
                <input type="text" name="mother_name" class="form-control"
                       value="<?= $old('mother_name', $existing->mother_name ?? '') ?>">
              </div>
            </div>
            <div class="col-sm-4">
              <div class="form-group">
                <label>Mother's Phone</label>
                <input type="text" name="mother_phone" class="form-control"
                       value="<?= $old('mother_phone', $existing->mother_phone ?? '') ?>"
                       placeholder="07XXXXXXXX">
              </div>
            </div>
            <div class="col-sm-4">
              <div class="form-group">
                <label>Religion</label>
                <select name="religion" class="form-control">
                  <?php $rel = $old('religion', $existing->religion ?? ''); ?>
                  <option value="">— Select —</option>
                  <?php foreach (['Islam','Catholic','Protestant','Seventh Day Adventist','Other'] as $r): ?>
                  <option value="<?= $r ?>"<?= $rel===$r?' selected':'' ?>><?= $r ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
          </div>

          <?php /* ── Confirm duplicate override (new students only) ── */ ?>
          <?php if (!$existing): ?>
          <div class="form-group" id="sp-confirm-dup-wrap" style="display:none">
            <div class="checkbox">
              <label>
                <input type="checkbox" name="confirm_duplicate" value="1">
                <strong>Yes, register anyway</strong> — I have verified this is a different student, not a duplicate.
              </label>
            </div>
          </div>
          <?php endif; ?>

          <?php /* ── Password (new students only) ── */ ?>
          <?php if (!$existing): ?>
          <div class="row">
            <div class="col-sm-12">
              <div class="form-group">
                <label>Login Password</label>
                <div class="input-group">
                  <input type="text" name="password" id="sp-pwd" class="form-control"
                         value="<?= $old('password', $latest->studentRegistrationNumber ?? $code) ?>"
                         placeholder="Auto-generated from reg. number or payment code">
                  <span class="input-group-btn">
                    <button type="button" class="btn btn-default" onclick="document.getElementById('sp-pwd').value='<?= addslashes($code) ?>'">
                      Use Code
                    </button>
                  </span>
                </div>
                <span class="help-block" style="font-size:11px">
                  Student uses this to log in. Defaults to the admission number or SchoolPay code.
                </span>
              </div>
            </div>
          </div>
          <?php endif; ?>

          <hr style="margin:15px 0">
          <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
            <button type="submit" class="btn btn-<?= $existing ? 'warning' : 'success' ?> btn-lg" id="sp-submit-btn">
              <i class="fa fa-<?= $existing ? 'save' : 'user-plus' ?>"></i>
              <?= $existing ? ' Save Changes' : ' Register Student' ?>
            </button>
            <a href="<?= $backUrl ?>" class="btn btn-default btn-lg">
              <i class="fa fa-times"></i> Cancel
            </a>
            <?php if ($existing): ?>
            <a href="<?= admin_url('students/'.$existing->id.'/edit') ?>" class="btn btn-info btn-lg">
              <i class="fa fa-external-link"></i> Full Profile
            </a>
            <a href="<?= $letterUrl ?>" target="_blank" class="btn btn-success btn-lg">
              <i class="fa fa-file-text-o"></i> Admission Letter
            </a>
            <?php endif; ?>
          </div>

        </form>
      </div>
    </div>
  </div><!-- /col-md-8 -->

</div><!-- /row -->

<script>
document.getElementById('sp-register-form').addEventListener('submit', function() {
  var btn = document.getElementById('sp-submit-btn');
  btn.disabled = true;
  btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Saving...';
});
// If a duplicate-name error is in the flash, reveal the confirm checkbox
<?php if (str_contains(session('import_error', ''), 'already exists in the same class')): ?>
document.getElementById('sp-confirm-dup-wrap').style.display = 'block';
<?php endif; ?>
</script>
<?php
        return ob_get_clean();
    }

    // ─── REGISTER ────────────────────────────────────────────────────────────

    public function register(Request $request)
    {
        $u    = Admin::user();
        $code = trim($request->input('school_pay_payment_code', ''));

        // Basic validation
        $errors = [];
        if (!$code)                                   $errors[] = 'Payment code is missing.';
        if (!trim($request->input('first_name', ''))) $errors[] = 'First name is required.';
        if (!trim($request->input('last_name', '')))  $errors[] = 'Last name is required.';

        if ($errors) {
            return back()->withInput()->with('import_error',implode(' ', $errors));
        }

        $firstName  = trim($request->input('first_name'));
        $middleName = trim($request->input('given_name', ''));
        $lastName   = trim($request->input('last_name'));
        $fullName   = trim(preg_replace('/\s+/', ' ', "$firstName $middleName $lastName"));
        $existingId = $request->input('existing_id');

        DB::beginTransaction();
        try {
            if ($existingId) {
                // ── UPDATE existing student ──
                $student = User::find($existingId);
                if (!$student || $student->enterprise_id != $u->enterprise_id) {
                    return back()->with('import_error','Student record not found or access denied.');
                }

                $student->first_name              = $firstName;
                $student->given_name              = $middleName;
                $student->last_name               = $lastName;
                $student->name                    = $fullName;
                $student->sex                     = $request->input('sex', '');
                $student->date_of_birth           = $request->input('date_of_birth') ?: null;
                $student->phone_number_1          = $request->input('phone_number_1', '');
                $student->father_name             = $request->input('father_name', '');
                $student->father_phone            = $request->input('father_phone', '');
                $student->mother_name             = $request->input('mother_name', '');
                $student->mother_phone            = $request->input('mother_phone', '');
                $student->nationality             = $request->input('nationality', '');
                $student->religion                = $request->input('religion', '');
                $student->user_number             = $request->input('user_number', '');
                $student->school_pay_payment_code = $code;
                $student->status                  = (int)$request->input('status', 1);

                if ($classId = $request->input('current_class_id')) {
                    $student->current_class_id = $classId;
                }
                $student->save();

                if ($student->current_class_id && $termId = $request->input('term_id')) {
                    $term = Term::find($termId);
                    \App\Models\StudentHasClass::firstOrCreate(
                        ['administrator_id' => $student->id, 'academic_class_id' => $student->current_class_id],
                        ['enterprise_id' => $u->enterprise_id, 'academic_year_id' => $term?->academic_year_id]
                    );
                }

                DB::commit();
                return redirect(admin_url('school-pay-student-import/success/' . $student->id . '?action=updated'));

            } else {
                // ── CREATE new student ──

                // Guard: payment code already in use for this school
                $codeInUse = User::where('enterprise_id', $u->enterprise_id)
                    ->where('user_type', 'student')
                    ->where('school_pay_payment_code', $code)
                    ->first();
                if ($codeInUse) {
                    DB::rollBack();
                    return back()->withInput()
                        ->with('import_error',"Payment code {$code} is already assigned to <strong>{$codeInUse->name}</strong> (ID #{$codeInUse->id}). "
                            . "<a href='" . admin_url('school-pay-student-import/preview/' . urlencode($code)) . "'>Click here to update that student instead.</a>");
                }

                // Guard: near-duplicate by name in same class
                $classId       = $request->input('current_class_id') ?: null;
                $nameDuplicate = User::where('enterprise_id', $u->enterprise_id)
                    ->where('user_type', 'student')
                    ->where('first_name', $firstName)
                    ->where('last_name', $lastName)
                    ->when($classId, fn($q) => $q->where('current_class_id', $classId))
                    ->first();
                if ($nameDuplicate && !$request->boolean('confirm_duplicate')) {
                    DB::rollBack();
                    $viewUrl = admin_url('students/' . $nameDuplicate->id . '/edit');
                    return back()->withInput()
                        ->with('import_error',"A student named <strong>{$nameDuplicate->name}</strong> already exists in the same class (ID #{$nameDuplicate->id}). "
                            . "<a href='{$viewUrl}' target='_blank'>View existing student</a> — or check <em>\"Yes, register anyway\"</em> below to create a new record.");
                }

                // Build a unique username
                $baseUsername = strtolower(preg_replace('/[^a-z0-9._]/', '',
                    str_replace([' ', "'"], ['.', ''], "$firstName.$lastName")));
                $username = $baseUsername;
                $suffix   = 1;
                while (\Encore\Admin\Auth\Database\Administrator::where('username', $username)
                    ->where('enterprise_id', $u->enterprise_id)->exists()) {
                    $username = $baseUsername . $suffix++;
                }

                $password = trim($request->input('password', '')) ?: $code;

                $student = new User();
                $student->enterprise_id           = $u->enterprise_id;
                $student->username                = $username;
                $student->password                = Hash::make($password);
                $student->plain_password          = $password;
                $student->name                    = $fullName;
                $student->first_name              = $firstName;
                $student->given_name              = $middleName;
                $student->last_name               = $lastName;
                $student->user_type               = 'student';
                $student->status                  = (int)$request->input('status', 1);
                $student->sex                     = $request->input('sex', '');
                $student->date_of_birth           = $request->input('date_of_birth') ?: null;
                $student->phone_number_1          = $request->input('phone_number_1', '');
                $student->father_name             = $request->input('father_name', '');
                $student->father_phone            = $request->input('father_phone', '');
                $student->mother_name             = $request->input('mother_name', '');
                $student->mother_phone            = $request->input('mother_phone', '');
                $student->nationality             = $request->input('nationality', '');
                $student->religion                = $request->input('religion', '');
                $student->user_number             = $request->input('user_number', '');
                $student->school_pay_payment_code = $code;
                $student->current_class_id        = $classId;
                $student->save();

                if ($student->current_class_id && $termId = $request->input('term_id')) {
                    $term = Term::find($termId);
                    \App\Models\StudentHasClass::firstOrCreate(
                        ['administrator_id' => $student->id, 'academic_class_id' => $student->current_class_id],
                        ['enterprise_id' => $u->enterprise_id, 'academic_year_id' => $term?->academic_year_id]
                    );
                }

                DB::commit();
                return redirect(admin_url('school-pay-student-import/success/' . $student->id . '?action=created'));
            }

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('import_error','Registration failed: ' . $e->getMessage());
        }
    }

    // ─── SUCCESS PAGE ─────────────────────────────────────────────────────────

    public function success($id, Content $content)
    {
        $u       = Admin::user();
        $student = User::where('enterprise_id', $u->enterprise_id)
            ->where('user_type', 'student')
            ->with(['current_class'])
            ->find($id);

        if (!$student) {
            admin_toastr('Student record not found.', 'error');
            return redirect(admin_url('school-pay-student-import'));
        }

        $action     = request('action', 'created'); // 'created' or 'updated'
        $editUrl    = admin_url("students/{$id}/edit");
        $letterUrl  = url("print-admission-letter?id={$id}");
        $importUrl  = admin_url('school-pay-student-import');
        $listUrl    = admin_url('students');

        ob_start(); ?>
<div class="row">
  <div class="col-md-8 col-md-offset-2">

    <!-- Success banner -->
    <div class="alert alert-success" style="border-radius:8px;padding:20px 24px;margin-bottom:20px">
      <h4 style="margin:0 0 6px">
        <i class="fa fa-check-circle fa-lg"></i>
        &nbsp; Student <?= $action === 'created' ? 'Registered' : 'Updated' ?> Successfully
      </h4>
      <p style="margin:0;opacity:.85">
        <?= e($student->name) ?> has been <?= $action === 'created' ? 'registered in the system' : 'updated' ?>.
        What would you like to do next?
      </p>
    </div>

    <!-- Student summary card -->
    <div class="box box-default">
      <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-user-circle-o"></i> Student Summary</h3>
      </div>
      <div class="box-body">
        <table class="table table-condensed" style="margin:0;font-size:14px">
          <tr>
            <th style="width:38%;color:#777;font-weight:500">Full Name</th>
            <td><strong><?= e($student->name) ?></strong></td>
          </tr>
          <tr>
            <th style="color:#777;font-weight:500">Admission No.</th>
            <td><code><?= e($student->user_number ?? '—') ?></code></td>
          </tr>
          <tr>
            <th style="color:#777;font-weight:500">Class</th>
            <td><?= e($student->current_class->name ?? '— Not assigned') ?></td>
          </tr>
          <tr>
            <th style="color:#777;font-weight:500">SchoolPay Code</th>
            <td><code><?= e($student->school_pay_payment_code ?? '—') ?></code></td>
          </tr>
          <tr>
            <th style="color:#777;font-weight:500">Gender</th>
            <td><?= e($student->sex ?? '—') ?></td>
          </tr>
          <?php if ($student->father_name || $student->mother_name): ?>
          <tr>
            <th style="color:#777;font-weight:500">Parent</th>
            <td><?= e($student->father_name ?: $student->mother_name) ?></td>
          </tr>
          <?php endif; ?>
          <tr>
            <th style="color:#777;font-weight:500">Status</th>
            <td>
              <?php
                $badge = match((int)$student->status) {1=>'success',2=>'warning',default=>'danger'};
                $label = match((int)$student->status) {1=>'Active',2=>'Pending',default=>'Inactive'};
              ?>
              <span class="label label-<?= $badge ?>"><?= $label ?></span>
            </td>
          </tr>
        </table>
      </div>
    </div>

    <!-- Action buttons -->
    <div class="box box-primary">
      <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-bolt"></i> Quick Actions</h3>
      </div>
      <div class="box-body" style="padding:20px">
        <div class="row text-center">

          <div class="col-sm-3" style="margin-bottom:12px">
            <a href="<?= $editUrl ?>" class="btn btn-info btn-block" style="padding:14px 8px">
              <i class="fa fa-pencil fa-2x" style="display:block;margin-bottom:6px"></i>
              Edit Full Profile
            </a>
          </div>

          <div class="col-sm-3" style="margin-bottom:12px">
            <a href="<?= $letterUrl ?>" target="_blank" class="btn btn-success btn-block" style="padding:14px 8px">
              <i class="fa fa-file-text-o fa-2x" style="display:block;margin-bottom:6px"></i>
              Print Admission Letter
            </a>
          </div>

          <?php if ($student->school_pay_payment_code): ?>
          <div class="col-sm-3" style="margin-bottom:12px">
            <a href="<?= admin_url('school-pay-student-import/preview/' . urlencode($student->school_pay_payment_code)) ?>"
               class="btn btn-default btn-block" style="padding:14px 8px">
              <i class="fa fa-history fa-2x" style="display:block;margin-bottom:6px"></i>
              Payment History
            </a>
          </div>
          <?php endif; ?>

          <div class="col-sm-3" style="margin-bottom:12px">
            <a href="<?= $importUrl ?>" class="btn btn-warning btn-block" style="padding:14px 8px">
              <i class="fa fa-user-plus fa-2x" style="display:block;margin-bottom:6px"></i>
              Register Another
            </a>
          </div>

        </div>

        <hr style="margin:15px 0 12px">

        <div class="text-center">
          <a href="<?= $listUrl ?>" class="btn btn-default btn-sm">
            <i class="fa fa-list"></i> All Students
          </a>
          &nbsp;
          <a href="<?= $importUrl ?>" class="btn btn-default btn-sm">
            <i class="fa fa-arrow-left"></i> Back to Import List
          </a>
        </div>
      </div>
    </div>

  </div>
</div>
<?php
        return $content
            ->title($action === 'created' ? 'Student Registered' : 'Student Updated')
            ->description(e($student->name))
            ->body(ob_get_clean());
    }

    // ─── HELPERS ─────────────────────────────────────────────────────────────

    private function parseName(string $fullName): array
    {
        $parts = preg_split('/\s+/', trim($fullName), 3);
        $first  = $parts[0] ?? '';
        if (count($parts) === 1) {
            return [$first, '', ''];
        }
        if (count($parts) === 2) {
            return [$first, '', $parts[1]];
        }
        return [$first, $parts[1], $parts[2]];
    }

    private function guessClassId(string $spClass, $classes): ?int
    {
        if (!$spClass || $classes->isEmpty()) return null;

        $norm     = strtolower(trim(preg_replace('/\s+/', ' ', $spClass)));
        $stripped = trim(preg_replace('/\bclass\b/i', '', $norm)); // "middle class" → "middle"

        // Classes are already ordered by academic_year_id DESC — most recent year first.
        // That means the first match wins and is always the most current year's version.

        // 1. Exact name match (case-insensitive): "middle class" == "Middle class"
        foreach ($classes as $cls) {
            $clsNorm = strtolower(trim($cls->name));
            if ($clsNorm === $norm) {
                return $cls->id;
            }
        }

        // 2. Match after stripping "class" suffix: "middle" == "Middle class" stripped → "middle"
        //    But only accept if stripped is at least 3 chars to avoid false positives
        if (strlen($stripped) >= 3) {
            foreach ($classes as $cls) {
                $clsStripped = trim(preg_replace('/\bclass\b/i', '', strtolower(trim($cls->name))));
                if ($clsStripped === $stripped) {
                    return $cls->id;
                }
            }
        }

        // 3. Keyword alias match — for short/abbreviated SchoolPay class names
        $aliases = [
            'primary one'   => 'primary one',   'p.1' => 'primary one',   'p1' => 'primary one',
            'primary two'   => 'primary two',   'p.2' => 'primary two',   'p2' => 'primary two',
            'primary three' => 'primary three', 'p.3' => 'primary three', 'p3' => 'primary three',
            'primary four'  => 'primary four',  'p.4' => 'primary four',  'p4' => 'primary four',
            'primary five'  => 'primary five',  'p.5' => 'primary five',  'p5' => 'primary five',
            'primary six'   => 'primary six',   'p.6' => 'primary six',   'p6' => 'primary six',
            'primary seven' => 'primary seven', 'p.7' => 'primary seven', 'p7' => 'primary seven',
            'baby'   => 'baby',
            'kinder' => 'kinder',
            'middle' => 'middle',
            'top'    => 'top',
            'pre'    => 'pre',
            'tahfidh'=> 'tahfidh',
        ];

        foreach ($aliases as $keyword => $match) {
            if (strpos($norm, $keyword) !== false) {
                foreach ($classes as $cls) {
                    if (strpos(strtolower(trim($cls->name)), $match) !== false) {
                        return $cls->id;
                    }
                }
            }
        }

        return null;
    }
}
