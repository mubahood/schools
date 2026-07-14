# System Performance & Issue Audit
**Date:** 2026-07-14  
**Scope:** Full-stack — Database, PHP Models, Admin Controllers, Bootstrap, Config (Local + Production)  
**Author:** Deep automated + manual analysis

---

## Fix Status (as of 2026-07-14)

| Ref | Issue | Status | Commit / Notes |
|---|---|---|---|
| A1 | DB indexes on 12 tables (30 indexes) | ✅ FIXED | Migration `2026_07_14_091825_add_performance_indexes_to_all_tables` — ran locally (1,079ms) and production (4,841ms) |
| B1 | `bootstrap.php` — heavy work every request | ✅ FIXED | 5-min cache guard via `Cache::put('system_boot_'.$eid, true, 300)` + removed `Log::info` + change-guarded `$ent->save()` |
| B1a | `Utils::system_boot()` — `Term::all()` full scan | ✅ FIXED | Scoped to `Term::where('enterprise_id', $u->enterprise_id)->get()` |
| B1b | `Enterprise::updateWalletBalance()` — full SUM + model save | ✅ FIXED | Replaced with direct `DB::table('enterprises')->update()` |
| C1a | `Subject.$appends` — 4× `Administrator::find()` | ✅ FIXED | Added `teacher1Rel/teacher2Rel/teacher3Rel` BelongsTo; all getters use relations |
| C1b | `StudentHasClass.$appends` — 4× `::find()` | ✅ FIXED | All 4 getters now use `$this->class`, `$this->student`, `$this->stream` relations |
| C1c | `ServiceSubscription.$appends` — 3× `::find()` | ✅ FIXED | All 3 getters use `$this->service`, `$this->due_term`, `$this->administrator` relations |
| C1d | `Account.$appends` — 2× SUM queries per row | ✅ FIXED | Removed `debit`/`credit` from `$appends` (set to `[]`) |
| C3 | Duplicate `boot()` hooks — Account | ✅ FIXED | Removed duplicate `self::updated()` registration |
| C5 | JSON `$casts` — ProgressiveAssessment | ⏭ SKIPPED | Has custom accessors/mutators that would conflict with `$casts`; existing accessors functionally equivalent |
| C6 | `TermlyReportCard::do_reports_generate()` — `Subject::all()` | ✅ FIXED | Scoped to `Subject::where('enterprise_id', $m->enterprise_id)->get()` |
| D1 | `TransactionController` — no `->with()` | ✅ FIXED | Added `->with(['account', 'term', 'by'])` |
| D1b | `DeletedTransactionController` — Term filter N+1 | ✅ FIXED | Added `->with('academic_year')` to Term loop |
| D2 | `TermlyReportCardController` — collection loads | ✅ FIXED | Replaced with `withCount(['mark_records', 'mark_records as bot_submitted_count', ...])` |
| D3 | `StudentsController` — 5 relations, no eager load | ✅ FIXED | Added `->with(['current_class', 'stream', 'account', 'parent'])` |
| D4 | `StudentHasClassController` — streams filter N+1 | ✅ FIXED | Added `AcademicClassSctream::with(['academic_class.academic_year'])` + `->with(['student','class','stream','year'])` on grid |
| D5a | `FinancialBudgetRecordController` — no `->with()` | ✅ FIXED | Added `->with(['account', 'par', 'term', 'created_by'])` |
| D5b | `FinancialExpenditureRecordController` — no `->with()` | ✅ FIXED | Added `->with(['account', 'par', 'term', 'created_by', 'supplier'])` + Term filter fixed |
| D6 | `ServiceSubscriptionController` — no `->with()` | ✅ FIXED | Added `->with(['due_term', 'sub.account', 'service'])` (both grid model calls) + Term filter fixed |
| D7 | `TheologyMarkRecordController` — 6 relations | ✅ FIXED | Added `->with(['term', 'theologyClass', 'stream', 'subject', 'student'])` |
| D8 | `SubjectController` — double-depth N+1 | ✅ FIXED | Added `->with(['academic_class.academic_year', 'teacher'])` |
| D9 | `AcademicClassController` — collection loads | ✅ FIXED | Pre-aggregated `DB::table('admin_users') GROUP BY current_class_id` + `withCount(['academic_class_sctreams', 'subjects'])` |
| D10 | `SuppliersController` — SUM per row | ✅ FIXED | Pre-aggregated all supplier totals with single `GROUP BY supplier_id` query |
| D11 | `ProgressiveAssessmentController` — 3 COUNTs per row | ✅ FIXED | `withCount(['test_records', 'reports', 'reports as pdf_reports_count' => ...])` + pre-loaded class names map |
| D12 | `HomeController` — 4 COUNT queries | ✅ FIXED | Merged into 1 `SUM(CASE WHEN)` query; per-class loop (24 queries) → 1 `GROUP BY` |
| D13 | Term filter dropdown N+1 (8+ controllers) | ✅ FIXED | Added `->with('academic_year')` to Term loops in all affected controllers |
| D15 | `MedicalRecordController`, `AcademicClassFeeController` — Term loops | ✅ FIXED | Added `->with('academic_year')` in all |
| E3 | Route cache not working — duplicate name | ✅ FIXED | Renamed `->name('home')` → `->name('reports-finance')` in `app/Admin/routes.php:202` — `route:cache` now succeeds |
| F3 | 1.6 GB production log | ✅ FIXED | Truncated to 0 bytes; `LOG_CHANNEL=daily` set in production `.env` (2026-07-14) |
| F5 | Missing `employee_monitoring_records` migration | ✅ ALREADY RAN | Confirmed via `migrate:status` — all 231 migration batches are applied |
| F7 | SchoolPay webhook duplicate inserts | ✅ FIXED | Replaced `exists()+create` with `firstOrCreate()` + try/catch for race condition safety |
| F8 | `view('test')` route — 485 production errors | ✅ FIXED | Removed dead `Route::get('test', ...)` from `routes/web.php` |
| F4 | DB account lockout (`schooics_main`) | ⚠ PENDING | Run: `ALTER USER 'schooics_main'@'localhost' ACCOUNT UNLOCK; FLUSH HOSTS;` via cPanel MySQL |
| F1 | `DB_PASSWORD=schooics_main` (weak) | ⚠ PENDING | Change via cPanel MySQL management — security issue |
| F2 | `EUROSATGROUP_PASSWORD=123456` | ⚠ PENDING | Change via EUROSATGROUP portal — security issue |
| E1 | `CACHE_DRIVER=file` | ⚠ PENDING | Requires Redis installation on hosting |
| E2 | `QUEUE_CONNECTION=sync` | ⚠ PENDING | Requires queue worker setup (cPanel cron or daemon) |
| E5 | OPcache suboptimal | ⚠ PENDING | Requires `php.ini` edit via cPanel PHP settings |

**Commit:** `a889160f` — `perf: comprehensive performance overhaul — indexes, N+1 fixes, boot guards` (28 files, 1916 insertions)  
**Production deploy:** 2026-07-14 via `git fetch --all && git reset --hard origin/master` + `php artisan migrate --force` + caches rebuilt

---

## Executive Summary

The system has **three catastrophic layers of overhead** that compound on every request:

1. `bootstrap.php` executes 15–20 database queries and a DB write on every admin page load before the actual page even begins to render.
2. Every major database table (80K–227K rows) has **no indexes** — every query is a full table scan.
3. Dozens of controllers and models fire **N+1 queries** (1 query per row) because relations are accessed without eager loading.

These three issues together can easily produce 500–5,000 database queries for a single page load that should need 10–20. Fixing them in order will yield dramatic, measurable improvements.

---

## PART A — DATABASE INDEXES (CATASTROPHIC)

### A1. Full Table Scans on All Major Tables

Every query against these tables reads every row, regardless of the filter. With 54K–227K rows, this means every page that touches these tables does seconds of unnecessary work.

| Table | Rows | Only Index | Missing Critical Indexes | Measured Scan Time |
|---|---|---|---|---|
| `reconcilers` | 227,648 | `id` (PRIMARY) | `enterprise_id`, `back_day`, `last_update` | ~35ms per query |
| `mark_records` | 80,847 | `id` (PRIMARY) | `term_id`, `academic_class_id`, `administrator_id`, `enterprise_id`, `subject_id`, `termly_report_card_id` | ~35ms per query |
| `theology_mark_records` | 55,565 | `id` (PRIMARY) | `term_id`, `administrator_id`, `academic_class_id`, `enterprise_id` | ~20ms per query |
| `transactions` | 54,131 | `id`, `uq_txn_school_pay_id` | `account_id`, `enterprise_id`, `created_at` | ~9ms per query |
| `financial_records` | 32,568 | `id` (PRIMARY) | `enterprise_id`, `account_id`, `term_id`, `type` | ~12ms per query |
| `student_report_card_items` | 24,730 | `id` (PRIMARY) | `student_report_card_id` | ~8ms per query |
| `fee_deposit_confirmations` | 17,526 | `id` (PRIMARY) | `enterprise_id` | ~5ms per query |
| `student_has_classes` | 16,611 | `id` (PRIMARY) | `academic_class_id`, `administrator_id`, `enterprise_id` | ~5ms per query |
| `accounts` | 16,527 | `id` (PRIMARY) | `administrator_id`, `enterprise_id` | ~2ms per query |
| `wallet_records` | 14,256 | `id` (PRIMARY) | `enterprise_id` | ~4ms per query |
| `student_report_cards` | 12,637 | `id` (PRIMARY) | `termly_report_card_id`, `student_id`, `term_id`, `enterprise_id` | ~4ms per query |
| `service_subscriptions` | 9,178 | `id` (PRIMARY) | `administrator_id`, `enterprise_id` | ~3ms per query |

**Impact:** A single mark-records page load with `per_page=700` and `term_id+class_id` filters scans all 80,847 rows. After proper composite indexing, this becomes a sub-millisecond index lookup.

**How to Fix — Safe Migration (Run on Non-Peak Hours):**

```sql
-- mark_records (most critical — used constantly by teachers)
ALTER TABLE mark_records
  ADD INDEX idx_mr_term_class (term_id, academic_class_id),
  ADD INDEX idx_mr_trc (termly_report_card_id),
  ADD INDEX idx_mr_student (administrator_id),
  ADD INDEX idx_mr_enterprise (enterprise_id),
  ADD INDEX idx_mr_subject (subject_id);

-- transactions (used in all financial pages)
ALTER TABLE transactions
  ADD INDEX idx_txn_account (account_id),
  ADD INDEX idx_txn_enterprise_date (enterprise_id, created_at);

-- accounts
ALTER TABLE accounts
  ADD INDEX idx_acc_admin (administrator_id),
  ADD INDEX idx_acc_enterprise (enterprise_id);

-- student_has_classes
ALTER TABLE student_has_classes
  ADD INDEX idx_shc_class (academic_class_id),
  ADD INDEX idx_shc_admin (administrator_id),
  ADD INDEX idx_shc_enterprise (enterprise_id);

-- financial_records
ALTER TABLE financial_records
  ADD INDEX idx_fr_enterprise (enterprise_id),
  ADD INDEX idx_fr_account (account_id),
  ADD INDEX idx_fr_term (term_id);

-- student_report_cards
ALTER TABLE student_report_cards
  ADD INDEX idx_src_trc (termly_report_card_id),
  ADD INDEX idx_src_student (student_id),
  ADD INDEX idx_src_term (term_id);

-- student_report_card_items
ALTER TABLE student_report_card_items
  ADD INDEX idx_srci_src (student_report_card_id);

-- service_subscriptions
ALTER TABLE service_subscriptions
  ADD INDEX idx_ss_admin (administrator_id),
  ADD INDEX idx_ss_enterprise (enterprise_id);

-- wallet_records
ALTER TABLE wallet_records
  ADD INDEX idx_wr_enterprise (enterprise_id);

-- fee_deposit_confirmations
ALTER TABLE fee_deposit_confirmations
  ADD INDEX idx_fdc_enterprise (enterprise_id);

-- reconcilers
ALTER TABLE reconcilers
  ADD INDEX idx_rec_enterprise (enterprise_id);

-- theology_mark_records
ALTER TABLE theology_mark_records
  ADD INDEX idx_tmr_term_class (term_id, theology_class_id),
  ADD INDEX idx_tmr_student (administrator_id),
  ADD INDEX idx_tmr_enterprise (enterprise_id);
```

**Safe to run live:** MySQL `ALTER TABLE ... ADD INDEX` on InnoDB uses an online DDL that does not block reads or writes. Run each `ALTER` statement individually and verify with `SHOW PROCESSLIST` that it is not blocking. On the production server, run them one at a time during low-traffic hours.

---

## PART B — BOOTSTRAP.PH (CRITICAL — FIRES ON EVERY ADMIN REQUEST)

### B1. `bootstrap.php` — Heavy Work Per Request

`app/Admin/bootstrap.php` is loaded by `Encore\Admin\Middleware\Bootstrap` on **every single admin page request** including AJAX calls, filter reloads, and PJAX navigations. It currently performs:

| Operation | Queries / Side-Effect | Should It Run Per-Request? |
|---|---|---|
| `Log::info('Admin bootstrap started')` | 1 log write (crashes logger on production if LOG_LEVEL=local) | NO — remove or move to startup event |
| `Utils::generate_termly_report_cards($u)` | 2–4 SELECT + potential INSERT per term | NO — move to scheduled command |
| `Utils::copy_default_grading($u)` | 1–2 SELECT + potential INSERT | NO — one-time setup; move to provisioning |
| `Utils::process_pending_subscriptions($u)` | `ServiceSubscription::where('is_processed','No')->get()` then loops and calls `do_process()` on each | **CRITICAL** — runs on every request; move to queue/cron |
| `foreach (Term::all() as $term)` | Loads **all** terms to check ReportFinanceModel | NO — move to cron/event |
| `$active_term = Admin::user()->ent->active_term()` | 2 SELECT (Enterprise + Term) | Maybe — cache result for session |
| `$ent->save()` | 1 UPDATE to enterprises table | **NO** — a DB write on every page load |
| `$u->getDefaultRole()` | 1 SELECT | Once per session is fine |
| `$u->ent->wallet_balance` in navbar | `SUM(amount) FROM wallet_records` + Enterprise save | **NO** — expensive aggregation on every navbar render |
| `Utils::system_checklist($u)` | Multiple SELECTs including nested SQL | Move to cron or lazy-load |
| `Term::where(['enterprise_id'=>...])->get()` | Loads all terms for navbar dropdown | Cache per session |

**Recommended Fix:**

```php
// bootstrap.php — BEFORE (runs all of this every request)
Utils::system_boot(Admin::user());

// bootstrap.php — AFTER (only what must happen per-request)
// Move all initialisation to a queued job or scheduled command.
// Keep ONLY the license check and role default here.
$u = Auth::user();
if ($u) {
    // License check is fast: $u->ent is already loaded by guard
    if ($u->ent && $u->ent->has_valid_lisence != 'Yes') {
        die('License expired...');
    }
}
```

Move all the `Utils::system_boot()` sub-operations to:
- **Scheduled command** (`php artisan schedule:run` via cron): `generate_termly_report_cards`, `create_documents`, `process_pending_subscriptions`
- **One-time provisioning** (run on enterprise creation): `copy_default_grading`
- **Event listener on Term activation**: `ReportFinanceModel` creation

For the **navbar wallet balance** (`$u->ent->wallet_balance`): cache the result in the session with a 5-minute TTL. The current implementation fires a `SUM(wallet_records)` on the 14K-row `wallet_records` table on every page load.

---

## PART C — MODEL ANTI-PATTERNS

### C1. `$appends` Using `Model::find()` Instead of Relations

The most widespread single issue. Models declare `protected $appends = [...]` whose getter methods call `SomeModel::find($this->foreign_key)` instead of `$this->relation`. This **bypasses Eloquent's eager-loading entirely** — even if the controller uses `->with(...)`, these appends will still fire individual queries.

| Model | File | Appended Attributes | Extra Queries per Serialised Row |
|---|---|---|---|
| `Subject` | `Models/Subject.php:172` | 4 teacher-name attributes (`teacher_name`, `teacher1_name`, etc.) | 4 × `Administrator::find()` per Subject |
| `StudentHasClass` | `Models/StudentHasClass.php:282` | 4 attributes (class_name, student_name, stream_name, avatar) | 4 × `::find()` per row |
| `ServiceSubscription` | `Models/ServiceSubscription.php:202` | service_text, term_text, student_text | 3 × `::find()` per row |
| `Account` | `Models/Account.php:329` | `debit`, `credit` | 2 × `SUM()` queries per Account |
| `TransportSubscription` | `Models/TransportSubscription.php:84` | `service_subscription_text` | 1 × wrong-key `::find()` per row |

**Fix pattern (same for all):**

```php
// BEFORE — fires a new DB query every time, even with eager loading
public function getTeacherNameAttribute()
{
    $u = Administrator::find($this->subject_teacher);
    return $u ? $u->name : '';
}

// AFTER — uses the already-loaded relation; zero extra queries when eager-loaded
public function getTeacherNameAttribute()
{
    return $this->teacher ? $this->teacher->name : '';
}
// Also add the belongsTo relation if not present:
public function teacher()
{
    return $this->belongsTo(Administrator::class, 'subject_teacher');
}
```

### C2. Missing `$with` on Models with `$appends` Accessing Relations

Even after fixing the `::find()` issue above, these models will still lazy-load if the callsite doesn't use `->with(...)`. Add `protected $with` only for relations that are **always** needed:

| Model | Suggested `$with` addition |
|---|---|
| `Term` | `['academic_year']` (always needed for `name_text` appended attribute) |
| `TermlyReportCard` | `['term']` (always needed for `term_text` appended attribute) |

For models where `$with` would over-fetch (e.g., `Subject` with 4 teacher relations — not always needed), instead ensure every controller that lists Subjects uses `->with(['teacher', 'teacher1', 'teacher2', 'teacher3'])`.

### C3. `boot()` Hooks Registered Twice

| Model | Hook | Problem |
|---|---|---|
| `Account` | `self::updated()` — two registrations at lines 94 and 99 | `doTransfer()` fires twice per update |
| `Transaction` | `self::deleted()` — two registrations at lines 198 and 205 | `my_update()` fires twice per deletion |
| `ServiceSubscription` | `self::deleting()` — two registrations at lines 106 and 143 | Transaction creation + model save happen twice |

**Fix:** Merge duplicate `self::updated()` / `self::deleted()` / `self::deleting()` registrations into a single closure per event.

### C4. `WalletRecord` — Full-Table `SUM` on Every Save

```php
// Models/WalletRecord.php — boot() hooks
self::created(function ($m) {
    Enterprise::find($m->enterprise_id)->updateWalletBalance(); // SUM(wallet_records)
});
self::updated(function ($m) {
    Enterprise::find($m->enterprise_id)->updateWalletBalance(); // SUM(wallet_records)
});
```

**Impact:** `updateWalletBalance()` runs `SELECT SUM(amount) FROM wallet_records WHERE enterprise_id = ?` on a 14K-row unindexed table, then saves the Enterprise. This fires on every SMS send, every bulk message, and every wallet top-up.

**Fix:** Replace `updateWalletBalance()` with an incremental approach:

```php
// In WalletRecord::boot()
self::created(function ($m) {
    \DB::table('enterprises')
        ->where('id', $m->enterprise_id)
        ->increment('wallet_balance', $m->amount);
});
self::updated(function ($m) {
    $diff = $m->amount - $m->getOriginal('amount');
    \DB::table('enterprises')
        ->where('id', $m->enterprise_id)
        ->increment('wallet_balance', $diff);
});
self::deleted(function ($m) {
    \DB::table('enterprises')
        ->where('id', $m->enterprise_id)
        ->decrement('wallet_balance', $m->amount);
});
```

### C5. JSON Columns Decoded Manually Instead of `$casts`

These models decode JSON columns with `json_decode()` in accessors/mutators, bypassing Eloquent's built-in dirty tracking:

| Model | Columns |
|---|---|
| `ProgressiveAssessment` | `classes`, `allowed_tests`, `excluded_subjects` |
| `StudentHasClass` | `new_curriculum_optional_subjects` |

**Fix:**
```php
protected $casts = [
    'classes'              => 'array',
    'allowed_tests'        => 'array',
    'excluded_subjects'    => 'array',
];
```
Remove the manual accessor/mutator pairs after adding `$casts`.

### C6. `TermlyReportCard::do_reports_generate()` — `Subject::all()` Full Table Scan

```php
// Line 325 — loads the ENTIRE subjects table into memory
$subjectCache = Subject::all()->keyBy('id');
```

**Fix:** Scope to the relevant enterprise and academic year:

```php
$subjectCache = Subject::where('enterprise_id', $m->enterprise_id)
    ->get()
    ->keyBy('id');
```

### C7. `AcademicClass` — Raw SQL in Column Accessors

```php
// Models/AcademicClass.php:747
public function getOptionalSubjectsAttribute() {
    // Fires: SELECT * FROM subjects WHERE academic_class_id = $this->id
    return count(array_filter($this->main_subjects(), fn($s) => $s->is_optional));
}
```

**Fix:** Use a subquery or store the count as a denormalised column refreshed by the `Subject` `saved` event:

```php
public function getOptionalSubjectsAttribute()
{
    return $this->subjects()->where('is_optional', true)->count();
}
```

---

## PART D — CONTROLLER N+1 QUERIES

The following was found by scanning all 50+ admin controllers. N+1 is the dominant pattern — relations accessed inside `display()` callbacks with no `->with()` on the grid model.

### D1. `TransactionController` + `DeletedTransactionController`

```php
// TransactionController.php:297 — 1 query per row
$grid->column('term_id')->display(function ($x) {
    $t = Term::find($x);  // N+1: fires 1 query per row
    ...
});
// Also: $this->account (line 229), $this->by (line 306) — no eager loading
// Filter: Term loop accesses $term->academic_year->name — N+1 in filter builder
```

**Fix:** Add to the grid model:
```php
$grid->model()->with(['account', 'by', 'term']);
```
Replace `Term::find($x)` with `$this->term->name_text` in the display callback. Add `->with('academic_year')` to the Term filter query.

Same applies to `DeletedTransactionController` — identical patterns, up to 90 extra queries per page.

### D2. `TermlyReportCardController` + `TheologyTermlyReportCardController`

```php
// TermlyReportCardController.php:63–102
// $this->mark_records accessed in 4 display() callbacks:
//   -> loads the FULL mark_records collection per TRC row (potentially thousands of rows!)
// Raw DB::select("SELECT COUNT(*)...") fires per row for report_cards_count
// $this->academic_year, $this->term — not in ->with()
```

**Worst offender:** `$this->mark_records` in four display callbacks loads all mark records for that TRC (up to 5,968 rows per load, per grid row on screen).

**Fix:**
```php
$grid->model()->with(['term', 'academicYear'])
    ->withCount([
        'markRecords as total_marks',
        'markRecords as bot_submitted' => fn($q) => $q->where('bot_is_submitted', 'Yes'),
        'markRecords as mot_submitted' => fn($q) => $q->where('mot_is_submitted', 'Yes'),
        'markRecords as eot_submitted' => fn($q) => $q->where('eot_is_submitted', 'Yes'),
        'studentReportCards as report_cards_count',
    ]);
```
Replace all four `count($this->mark_records->...)` calls with `$this->total_marks`, `$this->bot_submitted`, etc.

### D3. `StudentsController` — Up to 1,500 Extra Queries per Page

```php
// StudentsController.php:418–465 — 5 relations, no ->with()
$this->current_class        // line 419
$this->stream               // line 427, 437
$this->current_theology_class  // line 450
$this->theology_stream      // line 461
$this->parent               // line 561, 577
```

With `per_page=300` (common): 300 rows × 5 relations = **1,500 extra queries** per page load.

`Admin::user()` is also called **43 times** in this controller. Assign once:
```php
$u = Admin::user();
```

**Fix:**
```php
$grid->model()->with(['current_class', 'stream', 'current_theology_class', 'theology_stream', 'parent']);
```

### D4. `StudentHasClassController` — Double-Depth N+1 + Filter N+1

```php
// Filter loop (line 114–131): $ex->academic_class->academic_year — double N+1
AcademicClassSctream::where([...])->get()  // missing ->with('academic_class.academic_year')

// Display callbacks (lines 187–266):
$this->student      // not in ->with()
$this->class        // not in ->with()
$this->stream->academic_class  // double depth, not in ->with()
$this->year         // not in ->with()
// Plus: SecondarySubject::whereIn()->get() per row for optional subjects
```

**Fix:**
```php
// Filter:
AcademicClassSctream::with('academic_class.academic_year')->where([...])->get()

// Grid model:
$grid->model()->with(['student', 'class', 'stream.academic_class', 'year']);

// Optional subjects column: pre-compute a map, pass via use($map)
```

### D5. `FinancialBudgetRecordController` + `FinancialExpenditureRecordController`

Each has 4–5 relations in `display()` callbacks with no `->with()`, plus Term filter loops accessing `$term->academic_year`:

```php
// FinancialBudgetRecordController.php — no ->with() on grid model
$this->account    // line 175
$this->par        // line 184
$this->term       // line 193
$this->created_by // line 200
// → 4 queries × 30 rows = 120 extra queries per page

// FinancialExpenditureRecordController.php
$this->account, $this->par, $this->supplier, $this->term, $this->created_by
// → 5 queries × 30 rows = 150 extra queries per page
```

**Fix:**
```php
// Budget:
$grid->model()->with(['account', 'par', 'term', 'created_by']);
// Expenditure:
$grid->model()->with(['account', 'par', 'supplier', 'term', 'created_by']);
// Both: add ->with('academic_year') to Term filter queries
```

### D6. `ServiceSubscriptionController`

```php
$this->due_term               // not in ->with()
$this->sub                    // not in ->with()
$this->sub->account           // double depth — N+1 through sub
$this->service                // not in ->with()
```
`Admin::user()` called **17 times** in one class.

**Fix:**
```php
$grid->model()->with(['due_term', 'sub.account', 'service']);
```

### D7. `TheologyMarkRecordController` — 6 Relations, No `->with()`

```php
$this->termlyReportCard, $this->term, $this->academicClass,
$this->stream, $this->subject, $this->student
// → 6 queries × 100 rows = 600 extra queries per page
```

**Fix:**
```php
$grid->model()->with(['termlyReportCard', 'term', 'academicClass', 'stream', 'subject', 'student']);
```

### D8. `SubjectController` — Double-Depth Relation

```php
$this->academic_class->academic_year->name  // line 128 — double N+1
$this->teacher->name                        // line 138 — N+1
```

**Fix:**
```php
$grid->model()->with(['academic_class.academic_year', 'teacher']);
```

### D9. `AcademicClassController` — Collection Loads for Counting

```php
count($this->academic_class_sctreams)  // loads full collection just to count
count($this->subjects)                  // loads full collection just to count
User::where(...)->count()              // 1 query per class for student count
```

**Fix:**
```php
$grid->model()->with(['university_programme', 'class_teacher'])
    ->withCount([
        'academic_class_sctreams as streams_count',
        'subjects as subjects_count',
        'students as students_count' => fn($q) => $q->where('status', 1),
    ]);
```

### D10. `SuppliersController` — SUM Query per Row

```php
// 1 SUM aggregation on financial_records per supplier row (no index on supplier_id)
FinancialRecord::where([...])->where('supplier_id', $this->id)->sum('amount')
```

**Fix:** Pre-compute all supplier totals in a single grouped query before the grid column definition:
```php
$supplierTotals = DB::table('financial_records')
    ->where('enterprise_id', $u->enterprise_id)
    ->where('type', 'EXPENDITURE')
    ->groupBy('supplier_id')
    ->selectRaw('supplier_id, SUM(amount) as total')
    ->pluck('total', 'supplier_id');

// In display callback:
->display(function () use ($supplierTotals) {
    return number_format($supplierTotals[$this->id] ?? 0);
})
```

### D11. `ProgressiveAssessmentController` — 3 COUNT Queries per Row

```php
StudentTestRecord::where(...)->count()           // query per row
StudentProgressiveReport::where(...)->count()    // query per row × 2
```

**Fix:**
```php
$grid->model()->withCount([
    'studentTestRecords as records_count',
    'studentProgressiveReports as reports_count',
    'studentProgressiveReports as pdfs_count' => fn($q) => $q->whereNotNull('pdf_url')->where('pdf_url', '!=', ''),
]);
```

### D12. `HomeController` — 4 Full-Table Scans on Dashboard

```php
$markBase = MarkRecord::where('enterprise_id', $eid)->where('term_id', $active_term->id);
$totalMR      = $markBase->count();                                    // full scan #1
$submittedBOT = (clone $markBase)->where('bot_is_submitted','Yes')->count(); // full scan #2
$submittedMOT = (clone $markBase)->where('mot_is_submitted','Yes')->count(); // full scan #3
$submittedEOT = (clone $markBase)->where('eot_is_submitted','Yes')->count(); // full scan #4
```

Each scans 80,847 rows. This runs on **every dashboard load**.

**Fix (requires Part A indexes first):**
```php
$stats = DB::table('mark_records')
    ->where('enterprise_id', $eid)
    ->where('term_id', $active_term->id)
    ->selectRaw('
        COUNT(*) as total,
        SUM(bot_is_submitted = "Yes") as bot,
        SUM(mot_is_submitted = "Yes") as mot,
        SUM(eot_is_submitted = "Yes") as eot
    ')
    ->first();
```

### D13. Recurring Pattern: Term Filter Dropdown N+1

**Affects 8+ controllers:** `TransactionController`, `DeletedTransactionController`, `FinancialBudgetRecordController`, `FinancialExpenditureRecordController`, `ServiceSubscriptionController`, `StockRecordController`, `ProgressiveAssessmentController`, and others.

```php
// Pattern — fires 1 query per term to load academic_year
foreach (Term::where(['enterprise_id' => $u->enterprise_id])->get() as $ex) {
    $exams[$ex->id] = $ex->name_text;  // $ex->name_text accesses ->academic_year relation
}
```

**Fix (apply everywhere this pattern appears):**
```php
foreach (Term::with('academic_year')->where(['enterprise_id' => $u->enterprise_id])->get() as $ex) {
    $exams[$ex->id] = $ex->name_text;
}
```

### D14. `redirect()->back()` Inside `grid()` Methods

Found in `MarkRecordController` (line 212) and `TheologyMarkRecordController` (line 295). Returning a redirect from `grid()` can trigger infinite redirect loops in some laravel-admin versions.

**Fix:** Move the guard to the controller's `index()` override:
```php
public function index(Content $content)
{
    $reportCard = TermlyReportCard::where([...])->first();
    if (!$reportCard) {
        return redirect()->route('admin.mark-records.index')
            ->with('error', 'No report card found for this term.');
    }
    return parent::index($content);
}
```

### D15. `BatchServiceSubscriptionController` — `Model::find()` Per Row in Display

```php
Service::find($v)  // per row
Term::find($v)     // per row
User::find($id)    // up to 3× per row (inside a loop)
```

**Fix:** Add `->with(['service', 'term'])` to the grid model; pre-load user names as a keyed map for the subscriber loop.

---

## PART E — CONFIGURATION ISSUES

### E1. `CACHE_DRIVER=file` (Local and Production)

File-based caching means every cache read/write is a disk I/O operation. For a multi-tenant school system where query results, dropdown data, and session state are cached frequently, this is a significant bottleneck.

**Fix:** Install Redis (available free on most cPanel/WHM hosts) and set:
```
CACHE_DRIVER=redis
SESSION_DRIVER=redis
```

### E2. `QUEUE_CONNECTION=sync` (Production Confirmed)

All queued jobs — bulk messages, fee imports, report generation — run **inline during the HTTP request**. The user's browser waits for the entire operation to complete (potentially minutes) before getting a response. This is why fee imports and report generation feel slow from the UI.

**Fix:**
```
QUEUE_CONNECTION=database
```
Run `php artisan queue:table && php artisan migrate`, then set up a queue worker: either a cron job (`php artisan queue:work --once`) or a persistent daemon.

### E3. Route Cache Not Running (Production)

`php artisan route:cache` fails because two routes share the same name (`[.home]`). This means all 1,504 routes are **re-parsed from disk on every HTTP request**.

**Error:** `Unable to prepare route [reports-finance] for serialization. Another route has already been assigned name [.home].`

**Fix:** Find the duplicate named route in `routes/web.php` and rename one:
```bash
grep -n "->name('\.home')\|->name('home')" routes/web.php
```
Once the conflict is resolved, run:
```bash
php artisan optimize
```

### E4. `APP_DEBUG` / Debugbar

- **Local:** `APP_DEBUG=true` — debugbar is injecting profiling data into every response. This adds ~50–150ms overhead per request. Acceptable for development but ensure it is never accidentally deployed.
- **Production:** `APP_DEBUG=false`, `DEBUGBAR_ENABLED=false` — correctly disabled. However the `laravel-debugbar` package is in `require` (not `require-dev`) so it is compiled into the production vendor directory. Move it to `require-dev` to exclude it from production deployments.

### E5. OPcache Suboptimal Configuration (Production)

| Setting | Current | Recommended |
|---|---|---|
| `opcache.max_accelerated_files` | 4,000 | 20,000 (Laravel + vendor > 10K files) |
| `opcache.memory_consumption` | 128MB | 256MB |
| `opcache.validate_timestamps` | On | Off (set `0` for production; reload on deploy) |

When `opcache.max_accelerated_files=4000` and Laravel has 10K+ PHP files, OPcache silently stops caching new files — those files are re-parsed on every request.

**Fix:** Add to `php.ini` (or `.htaccess` if cPanel):
```ini
opcache.max_accelerated_files=20000
opcache.memory_consumption=256
opcache.validate_timestamps=0
```
After changing `validate_timestamps=0`, reset OPcache after every deploy: `php artisan opcache:clear` or trigger a PHP-FPM reload.

---

## PART F — PRODUCTION-SPECIFIC CRITICAL ISSUES

### F1. 🚨 DB Password = DB Username (`DB_PASSWORD=schooics_main`)

The database password is identical to the database username. If the MySQL port is ever exposed (firewall misconfiguration, temporary test) or any attacker gains knowledge of the username, the database is immediately accessible.

**Fix:** Change immediately via cPanel MySQL management. Update `.env` with the new password. This does not require any code change.

### F2. 🚨 SMS API Password = `123456` (`EUROSATGROUP_PASSWORD=123456`)

An attacker who discovers the EUROSATGROUP endpoint and credentials can send unlimited SMS messages billed to this account. The password `123456` is the most commonly tested default.

**Fix:** Log in to the EUROSATGROUP portal and change the password. Update `.env`.

### F3. 🚨 Production Log File is 1.6 GB

The production `laravel.log` is **1.6 GB / 9.3 million lines**. Every error write appends to this file, causing progressively longer disk seeks.

**Root cause:** `LOG_CHANNEL=stack` points to a single non-rotating file. The file has grown unbounded.

**Immediate action (safe to run now):**
```bash
# SSH into production
> /home4/schooics/public_html/storage/logs/laravel.log   # truncate (not delete — permissions preserved)
```
**Then** set `LOG_CHANNEL=daily` in production `.env` so logs rotate daily and old logs auto-expire.

### F4. 🚨 DB Account Being Locked Intermittently

The production log shows 46 occurrences of:
```
SQLSTATE[HY000] [3118] Access denied for user 'schooics_main'@'localhost'. Account is locked.
```
This means MySQL is **temporarily locking the database user account**. Likely causes: too many failed connection attempts, or MySQL's `max_connect_errors` threshold being hit due to connection pooling failures.

**Investigation steps:**
```sql
SELECT User, Account_locked FROM mysql.user WHERE User='schooics_main';
ALTER USER 'schooics_main'@'localhost' ACCOUNT UNLOCK;  -- to unblock
FLUSH HOSTS;  -- to reset connection error count
```

### F5. Missing Database Table on Production

The production log shows:
```
SQLSTATE[42S02]: Table 'schooics_main.employee_monitoring_records' doesn't exist
```
A migration was never run on the production server. Any page that queries `employee_monitoring_records` will crash.

**Fix:**
```bash
ssh production
cd /home4/schooics/public_html
php artisan migrate --force
```
Or run only the specific migration that creates `employee_monitoring_records`.

### F6. Missing Controller on Production

```
Target class [App\Admin\Controllers\SchoolPaySyncTestController] does not exist
```
A route references this controller but it was deleted. **5 errors logged** for this — some production user or cron is hitting this route.

**Fix:** Either restore the controller or remove the route from `app/Admin/routes.php`.

### F7. SchoolPay Webhook Duplicate Inserts

The `Utils::schoool_pay_sync()` function (called from `routes/api.php`) attempts to insert `school_pay_transactions` records on every webhook call. When SchoolPay retries a webhook (normal behaviour), the second call fails with:
```
SQLSTATE[23000]: Duplicate entry for key 'uq_spt_school_pay_id'
```
This exception propagates up and causes error logging for every retry.

**Fix:** Change the insert to `updateOrCreate`:
```php
// Instead of:
$spt = new SchoolPayTransaction();
$spt->school_pay_transporter_id = $data['transId'];
// ...
$spt->save();

// Use:
SchoolPayTransaction::updateOrCreate(
    ['school_pay_transporter_id' => $data['transId']],
    [...all other fields...]
);
```

### F8. 485 Errors — `View [test] not found`

A route somewhere returns `view('test')` which does not exist. This fires 485 times in the production log — something (a bot, a cron, a feature) keeps hitting it.

**Fix:**
```bash
grep -rn "view('test')\|view(\"test\")" app/ routes/
```
Remove or fix the offending route/controller.

---

## PART G — LOG ERRORS (LOCAL)

### G1. Logger EMERGENCY on Every Request (FIXED)

The `CreateDatabaseLogger` was receiving `LOG_LEVEL=local` (invalid Monolog level) on the production server, crashing the logger on **every single admin request** and writing a 60-line stack trace to the log. **Already fixed** in `app/Logging/CreateDatabaseLogger.php` with a try/catch fallback.

### G2. `Log::info('Admin bootstrap started')` on Every Request

`app/Admin/bootstrap.php` line 5 writes an INFO log on every admin page load. Even with file logging, this is 1 disk write per request just for a startup message. On production (database logger), this would trigger a DB INSERT per request.

**Fix:** Remove this line entirely. It serves no production value.

### G3. Route `[login]` Not Defined — 276 Production Errors

Some middleware redirects to `route('login')` but the named route does not exist in the admin panel context.

**Fix:** Check `app/Http/Middleware/` and vendor `Authenticate` middleware for `route('login')` references, and ensure the admin login route has a `->name('login')` or the redirect target uses `admin_url('auth/login')`.

---

## PART H — Recommended Fix Priority Order

### Phase 1 — Do Immediately (No Risk, High Impact)

| # | Task | Files | Estimated Gain |
|---|---|---|---|
| 1 | Add DB indexes (Part A SQL) | Via MySQL | 10–100× query speed |
| 2 | Truncate 1.6GB prod log, set `LOG_CHANNEL=daily` | `.env` on prod | Eliminates I/O on every log write |
| 3 | Change `DB_PASSWORD` and `EUROSATGROUP_PASSWORD` | `.env` on prod | Security |
| 4 | Remove `Log::info('Admin bootstrap started')` | `bootstrap.php:5` | 1 disk write less per request |
| 5 | Fix duplicate route name → run `route:cache` | `routes/web.php` | 1,504 routes parsed on every request → cached |
| 6 | Truncate/restart DB account lockout on prod | MySQL | Eliminates 500 errors |
| 7 | Run missing migration (`employee_monitoring_records`) | Production | Eliminates 500 errors on affected pages |

### Phase 2 — This Week (Moderate Risk, Large Impact)

| # | Task | Files | Estimated Gain |
|---|---|---|---|
| 8 | Move `process_pending_subscriptions` out of bootstrap | `bootstrap.php`, `Kernel.php` | Remove DB write + loop from every request |
| 9 | Cache `$ent->save()` / remove per-request Enterprise UPDATE | `bootstrap.php` | Remove 1 DB write per request |
| 10 | Fix `Subject.$appends` — 4× `::find()` → relations | `Models/Subject.php` | 4 × N extra queries eliminated |
| 11 | Fix `StudentHasClass.$appends` — 4× `::find()` → relations | `Models/StudentHasClass.php` | 4 × N extra queries eliminated |
| 12 | Fix `WalletRecord` SUM → increment/decrement | `Models/WalletRecord.php` | Full table scan eliminated on every SMS send |
| 13 | Fix `TransactionController` — add `->with(['account','by','term'])` | `TransactionController.php` | N+1 on transactions grid eliminated |
| 14 | Fix `HomeController` — merge 4 `mark_records` COUNTs into 1 | `HomeController.php` | 3 fewer full table scans per dashboard load |
| 15 | Fix duplicate `boot()` hooks in Account, Transaction, ServiceSubscription | 3 model files | Double execution of heavy hooks eliminated |
| 16 | Fix `SchoolPay` webhook → `updateOrCreate` | `Models/Utils.php` | Eliminates 500 errors on retry |
| 17 | Remove/fix `View [test] not found` route | `routes/` | Eliminates 485 production errors |

### Phase 3 — This Month (Infrastructure, Low Risk)

| # | Task | Notes |
|---|---|---|
| 18 | Set `QUEUE_CONNECTION=database`, deploy queue worker | Requires `php artisan queue:table && php artisan migrate` |
| 19 | Set `CACHE_DRIVER=redis`, `SESSION_DRIVER=redis` | Requires Redis; available on cPanel via cPanel Redis plugin |
| 20 | Fix OPcache config on production | Edit `php.ini` or via cPanel PHP settings |
| 21 | Move `laravel-debugbar` to `require-dev` | `composer.json` + production deployment |
| 22 | Fix `$casts` for JSON columns (ProgressiveAssessment, StudentHasClass) | `Models/` |
| 23 | Fix `Account.$appends` — SUM attributes | `Models/Account.php` |
| 24 | Fix `TermlyReportCard::do_reports_generate` `Subject::all()` → scoped | `Models/TermlyReportCard.php:325` |
| 25 | Add MySQL slow query logging on production | `my.cnf`; may need hosting support ticket |
| 26 | Remove `SchoolPaySyncTestController` route | `app/Admin/routes.php` |
| 27 | Fix `max_execution_time=0` on production | `php.ini` → set to 300 |

---

## PART I — What NOT to Change

These should be left as-is to avoid breaking things:

- **`Transaction::my_update()`** on every transaction save — this is intentional; account balance must stay in sync. The indexes (Part A) will make this fast.
- **`bootstrap.php` license check** — must remain per-request; it protects expired accounts.
- **`insertOrIgnore` / `upsert` in `TermlyReportCard`** — recently optimised; do not revert.
- **`direct_messages` indexes** — already well-indexed; leave alone.
- **`admin_users` composite index** — already has `(enterprise_id, user_type, status)` which is the right composite for student/employee queries.
- **`participants` indexes** — already has the right session/admin/enterprise indexes.

---

## APPENDIX — Index of Files to Change

| File | Changes Needed |
|---|---|
| `app/Admin/bootstrap.php` | Remove `Log::info`, remove `Utils::system_boot()`, cache active_term/enterprise in session, remove `$ent->save()` |
| `app/Models/WalletRecord.php` | Replace `updateWalletBalance()` with `DB::increment/decrement` |
| `app/Models/Subject.php` | Fix 4 `$appends` getters to use relations; add missing `belongsTo` relations |
| `app/Models/StudentHasClass.php` | Fix 4 `$appends` getters to use relations; add `$casts` for JSON column |
| `app/Models/ServiceSubscription.php` | Fix 3 `$appends` getters; merge duplicate `deleting` hook |
| `app/Models/Account.php` | Fix `owner()` relation method; merge duplicate `updated` hook |
| `app/Models/Transaction.php` | Merge duplicate `deleted` hook |
| `app/Models/TermlyReportCard.php` | Scope `Subject::all()` in `do_reports_generate` |
| `app/Models/ProgressiveAssessment.php` | Add `$casts` for JSON columns; remove manual accessors |
| `app/Models/Term.php` | Add `protected $with = ['academic_year']` |
| `app/Admin/Controllers/TransactionController.php` | Add `->with(['account','by','term'])` to grid model |
| `app/Admin/Controllers/HomeController.php` | Merge 4 COUNT queries into 1 SELECT with SUM |
| `app/Models/Utils.php` | Fix `schoool_pay_sync()` to use `updateOrCreate` |
| `routes/web.php` | Fix duplicate route name |
| `app/Admin/routes.php` | Remove dead `SchoolPaySyncTestController` route |
| `config/logging.php` | Already fine; ensure `.env` on production uses `LOG_CHANNEL=daily` |
| `.env` (production) | Change DB_PASSWORD, EUROSATGROUP_PASSWORD, LOG_CHANNEL, QUEUE_CONNECTION |
| `php.ini` (production) | opcache.max_accelerated_files=20000, opcache.memory_consumption=256 |
| MySQL (production) | Run all `ALTER TABLE ... ADD INDEX` statements from Part A |
