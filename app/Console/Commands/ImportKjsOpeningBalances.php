<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\Enterprise;
use App\Models\Term;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Console\Command;

class ImportKjsOpeningBalances extends Command
{
    protected $signature = 'kjs:import-opening-balances
                            {file? : Path to CSV file (default: /Users/mac/Desktop/kjs-data.csv)}
                            {--dry-run : Preview all changes without saving anything}
                            {--enterprise-id=7 : Enterprise ID (7 = Kira Junior School)}';

    protected $description = 'Import KJS student opening balances from CSV. Creates one OPENING_BALANCE adjustment transaction per student to force their account to the CSV balance.';

    // Class-section header patterns (exact uppercase match anywhere in colB)
    private array $classSectionTokens = [
        'BABY CLASS', 'MIDDLE CLASS', 'TOP CLASS',
        'PRIMARY ONE', 'PRIMARY TWO', 'PRIMARY THREE',
        'PRIMARY FOUR', 'PRIMARY FIVE', 'PRIMARY SIX', 'PRIMARY SEVEN',
        'TAHFIDH CLASS',
    ];

    // Other non-student rows to skip
    private array $skipTokens = [
        'SUBTOTAL', 'SUB TOTAL', 'GRAND', 'INCOME COLLECTED',
    ];

    public function handle(): int
    {
        $file         = $this->argument('file') ?? '/Users/mac/Desktop/kjs-data.csv';
        $isDryRun     = (bool) $this->option('dry-run');
        $enterpriseId = (int) $this->option('enterprise-id');

        // ── File ───────────────────────────────────────────────────────────────
        if (!file_exists($file)) {
            $this->error("File not found: {$file}");
            return 1;
        }

        // ── Enterprise ─────────────────────────────────────────────────────────
        $enterprise = Enterprise::find($enterpriseId);
        if (!$enterprise) {
            $this->error("Enterprise ID {$enterpriseId} not found.");
            return 1;
        }
        $this->info("Enterprise : {$enterprise->name} (ID: {$enterprise->id})");

        // ── Term ───────────────────────────────────────────────────────────────
        $term = $enterprise->active_term()
            ?? Term::where('enterprise_id', $enterprise->id)->orderBy('id', 'desc')->first();
        if (!$term) {
            $this->error("No term found for enterprise {$enterprise->name}");
            return 1;
        }
        $this->info("Term       : {$term->name_text} (ID: {$term->id})");

        if ($isDryRun) {
            $this->warn("DRY RUN — no changes will be saved\n");
        }

        // ── Parse CSV ─────────────────────────────────────────────────────────
        $handle = fopen($file, 'r');
        if (!$handle) {
            $this->error("Cannot open file: {$file}");
            return 1;
        }
        // Strip UTF-8 BOM
        $bom = fread($handle, 3);
        if (bin2hex($bom) !== 'efbbbf') {
            rewind($handle);
        }

        $stats = [
            'rows'      => 0,
            'adjusted'  => 0,
            'no_change' => 0,
            'not_found' => 0,
            'no_code'   => 0,
            'duplicate' => 0,
            'skipped'   => 0,
            'errors'    => 0,
        ];

        // Issue log entries: ['class' => ..., 'name' => ..., 'code' => ..., 'balance' => ..., 'reason' => ...]
        $issueLog    = [];
        $seenCodes   = [];   // code => ['class', 'name', 'rowNum'] for duplicate detection
        $currentClass = 'Unknown';

        $rowNum = 0;
        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;

            while (count($row) < 4) $row[] = '';

            $colA = trim($row[0]);
            $colB = trim($row[1]);
            $colC = trim($row[2]);
            $colD = trim($row[3]);

            // Detect class section header (e.g. ",BABY CLASS 2025,,")
            $upperB = strtoupper($colB);
            foreach ($this->classSectionTokens as $token) {
                if (str_contains($upperB, $token)) {
                    $currentClass = $colB;
                    $stats['skipped']++;
                    continue 2;
                }
            }

            // Skip repeated S/N header rows and subtotal rows
            if (strtoupper($colA) === 'S/N') {
                $stats['skipped']++;
                continue;
            }
            foreach ($this->skipTokens as $token) {
                if (str_contains($upperB, $token)) {
                    $stats['skipped']++;
                    continue 2;
                }
            }
            // CSV-level header row
            if (str_contains($upperB, 'STUNDENT') || $upperB === 'NAME') {
                $stats['skipped']++;
                continue;
            }

            // Clean code: keep digits only
            $cleanCode = preg_replace('/[^0-9]/', '', $colC);

            // Students with no valid code
            if (strlen($cleanCode) < 7) {
                if (strlen($colB) > 2) {
                    $issueLog[] = [
                        'class'   => $currentClass,
                        'name'    => $colB,
                        'code'    => $colC ?: '(empty)',
                        'balance' => $colD,
                        'reason'  => 'No valid school pay code in CSV',
                    ];
                    $stats['no_code']++;
                } else {
                    $stats['skipped']++;
                }
                continue;
            }

            $stats['rows']++;
            $csvBalance    = $this->parseBalance($colD);

            // Flip sign for system convention
            $targetBalance = $csvBalance * -1;

            // Duplicate code detection
            if (isset($seenCodes[$cleanCode])) {
                $first = $seenCodes[$cleanCode];
                $issueLog[] = [
                    'class'   => $currentClass,
                    'name'    => $colB,
                    'code'    => $cleanCode,
                    'balance' => number_format($csvBalance),
                    'reason'  => "Duplicate code — also used by \"{$first['name']}\" ({$first['class']}) on row {$first['row']}. Skipped to avoid double-adjustment.",
                ];
                $stats['duplicate']++;
                continue;
            }
            $seenCodes[$cleanCode] = ['name' => $colB, 'class' => $currentClass, 'row' => $rowNum];

            // Find student
            $student = User::where('enterprise_id', $enterprise->id)
                ->where('school_pay_payment_code', $cleanCode)
                ->first();

            if (!$student) {
                $issueLog[] = [
                    'class'   => $currentClass,
                    'name'    => $colB,
                    'code'    => $cleanCode,
                    'balance' => number_format($csvBalance),
                    'reason'  => 'Student not found in system (code may be wrong or student not enrolled)',
                ];
                $stats['not_found']++;
                continue;
            }

            // Get or create account
            $account = Account::firstOrCreate(
                ['administrator_id' => $student->id, 'enterprise_id' => $enterprise->id],
                ['name' => $student->name . ' - Account', 'balance' => 0, 'status' => 1, 'type' => 'STUDENT_ACCOUNT']
            );

            // Current balance — summed live from DB
            $currentBalance = (float) Transaction::where('account_id', $account->id)->sum('amount');

            if (abs($currentBalance - $targetBalance) < 0.01) {
                $stats['no_change']++;
                continue;
            }

            $adjustmentAmount = $targetBalance - $currentBalance;

            if ($isDryRun) {
                $this->line(sprintf(
                    "  %-40s | %s | current: %s | target: %s | adj: %s",
                    $student->name, $cleanCode,
                    number_format($currentBalance), number_format($targetBalance), number_format($adjustmentAmount)
                ));
                $stats['adjusted']++;
                continue;
            }

            try {
                $tx                            = new Transaction();
                $tx->enterprise_id             = $enterprise->id;
                $tx->account_id                = $account->id;
                $tx->amount                    = $adjustmentAmount;
                $tx->description               = "Opening balance import — KJS ({$student->name})";
                $tx->type                      = 'OPENING_BALANCE';
                $tx->academic_year_id          = $term->academic_year_id;
                $tx->term_id                   = $term->id;
                $tx->payment_date              = now()->toDateTimeString();
                $tx->source                    = 'IMPORTED';
                $tx->school_pay_transporter_id = null;
                $tx->is_contra_entry           = false;
                $tx->save();
                // Transaction::created → my_update() → accounts.balance = SUM(all) = targetBalance ✓

                $stats['adjusted']++;
                $this->line(sprintf(
                    "  OK  %-40s | %s → %s",
                    $student->name, number_format($currentBalance), number_format($targetBalance)
                ));
            } catch (\Throwable $e) {
                $issueLog[] = [
                    'class'   => $currentClass,
                    'name'    => $student->name,
                    'code'    => $cleanCode,
                    'balance' => number_format($csvBalance),
                    'reason'  => 'Transaction save failed: ' . $e->getMessage(),
                ];
                $stats['errors']++;
            }
        }

        fclose($handle);

        // ── Summary ────────────────────────────────────────────────────────────
        $this->newLine();
        $this->line('══════════════════════════════════════════════════════════════');
        $this->info('SUMMARY' . ($isDryRun ? ' (DRY RUN — nothing saved)' : ''));
        $this->line('══════════════════════════════════════════════════════════════');
        $this->info("  Balances adjusted  : {$stats['adjusted']}");
        $this->line("  Already correct    : {$stats['no_change']}");
        $this->line("  Rows skipped       : {$stats['skipped']}");
        $this->warn("  No code in CSV     : {$stats['no_code']}");
        $this->warn("  Duplicate codes    : {$stats['duplicate']}");
        $this->warn("  Not found          : {$stats['not_found']}");
        if ($stats['errors'] > 0) {
            $this->error("  Errors             : {$stats['errors']}");
        }

        // ── Issues report ──────────────────────────────────────────────────────
        if (!empty($issueLog)) {
            $this->newLine();
            $this->line('══════════════════════════════════════════════════════════════');
            $this->warn('ISSUES REPORT — ' . count($issueLog) . ' student(s) skipped');
            $this->line('══════════════════════════════════════════════════════════════');

            $byClass = [];
            foreach ($issueLog as $issue) {
                $byClass[$issue['class']][] = $issue;
            }

            foreach ($byClass as $class => $issues) {
                $this->newLine();
                $this->warn("  CLASS: {$class}");
                $this->line('  ' . str_repeat('─', 70));
                foreach ($issues as $issue) {
                    $this->line(sprintf(
                        "  %-45s | Code: %-12s | Bal: %12s",
                        $issue['name'], $issue['code'], $issue['balance']
                    ));
                    $this->line("    ⚠  {$issue['reason']}");
                }
            }
        }

        return 0;
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    private function parseBalance(string $raw): float
    {
        $raw = trim($raw);
        if ($raw === '' || $raw === '-' || $raw === '--' || $raw === '—') {
            return 0.0;
        }
        $negative = str_starts_with($raw, '-');
        $cleaned  = preg_replace('/[^0-9.]/', '', $raw);
        return $negative ? -(float)$cleaned : (float)$cleaned;
    }
}
