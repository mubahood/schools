<?php

namespace App\Console\Commands;

use App\Models\Enterprise;
use App\Services\SchoolPaySyncService;
use Illuminate\Console\Command;

class SyncSchoolPayCommand extends Command
{
    protected $signature   = 'schoolpay:sync
                                {--enterprise= : Sync a specific enterprise ID only}
                                {--date=       : Date to sync (Y-m-d). Defaults to today}
                                {--from=       : Start of date range (Y-m-d)}
                                {--to=         : End of date range (Y-m-d). Defaults to today}
                                {--import      : Force auto-import even if not configured on enterprise}';

    protected $description = 'Fetch transactions from SchoolPay API and import into the ledger';

    public function handle(): int
    {
        $enterpriseId = $this->option('enterprise');
        $date         = $this->option('date')  ?: date('Y-m-d');
        $from         = $this->option('from');
        $to           = $this->option('to')    ?: date('Y-m-d');
        $forceImport  = (bool) $this->option('import');

        // Build enterprise list
        $query = Enterprise::whereNotNull('school_pay_code')
            ->where('school_pay_code', '!=', '')
            ->where('school_pay_status', 'Active');

        if ($enterpriseId) {
            $query->where('id', $enterpriseId);
        }

        $enterprises = $query->get();

        if ($enterprises->isEmpty()) {
            $this->warn('No enterprises with active SchoolPay configuration found.');
            return 0;
        }

        $this->info("Syncing " . $enterprises->count() . " enterprise(s)...");

        $totalStored   = 0;
        $totalImported = 0;
        $allErrors     = [];

        foreach ($enterprises as $enterprise) {
            $this->line("  → {$enterprise->name} (code: {$enterprise->school_pay_code})");

            if ($from) {
                $result = SchoolPaySyncService::syncDateRange($enterprise, $from, $to);
                $this->line("    Date range {$from} – {$to}: {$result['days']} days, {$result['stored']} new, {$result['skipped']} skipped, {$result['imported']} imported");
            } else {
                $result = SchoolPaySyncService::syncDate($enterprise, $date) ;
                // If force import flag, run auto-import regardless of enterprise setting
                if ($forceImport && $enterprise->school_pay_import_automatically !== 'Yes') {
                    $imp               = SchoolPaySyncService::autoImportPending($enterprise);
                    $result['imported'] += $imp['imported'];
                    $result['errors']   = array_merge($result['errors'], $imp['errors']);
                }
                $this->line("    {$date}: {$result['stored']} new, {$result['skipped']} skipped, {$result['imported']} imported");
            }

            if (!empty($result['errors'])) {
                foreach ($result['errors'] as $err) {
                    $this->warn("    ⚠ {$err}");
                }
                $allErrors = array_merge($allErrors, $result['errors']);
            }

            $totalStored   += $result['stored'];
            $totalImported += $result['imported'];
        }

        $this->info("Done. Total stored: {$totalStored}, imported: {$totalImported}, errors: " . count($allErrors));
        return empty($allErrors) ? 0 : 1;
    }

    // Allow calling syncDate as a static passthrough for range syncs
    public static function syncDate(Enterprise $enterprise, string $date): array
    {
        return SchoolPaySyncService::syncDateRange($enterprise, $date, $date);
    }
}
