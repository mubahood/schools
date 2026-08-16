<?php

namespace App\Console\Commands;

use App\Admin\Controllers\ParentsController;
use App\Models\Utils;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Repairs guardian contact data so the parents list, the edit form and phone
 * login all agree with each other.
 *
 * Background: the parents grid renders Administrator::getParentPhonNumber(),
 * which falls back across emergency_person_phone / phone_number_2 /
 * father_phone / mother_phone. The edit form and ApiAuthController both read
 * phone_number_1 only. Where a number lived solely in a fallback column, staff
 * saw a number in the list but a blank required field on Edit, and the parent
 * could not log in with the number they had been given.
 *
 * Run with --dry-run first; it reports without writing.
 */
class FixParentContacts extends Command
{
    protected $signature = 'parents:fix-contacts
                            {--dry-run : Report what would change without writing}
                            {--usernames : Also replace junk usernames such as "+256(not set)"}';

    protected $description = 'Backfill parent phone_number_1 from guardian columns and clean junk usernames';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');
        $this->info($dry ? 'DRY RUN — no changes will be written.' : 'Applying changes…');

        $this->backfillPhones($dry);

        if ($this->option('usernames')) {
            $this->cleanUsernames($dry);
        } else {
            $junk = DB::table('admin_users')
                ->whereIn('username', ParentsController::PHONE_JUNK)
                ->count();
            if ($junk > 0) {
                $this->warn("{$junk} accounts still hold a junk username. Re-run with --usernames to clean them.");
            }
        }

        return self::SUCCESS;
    }

    /**
     * Copy a real number into phone_number_1 wherever it is missing but present
     * in one of the guardian fallback columns.
     */
    private function backfillPhones(bool $dry): void
    {
        $cols = implode(', ', array_merge(
            ['id', 'phone_number_1'],
            ParentsController::PHONE_FALLBACK_COLUMNS
        ));

        $fixed = 0;
        $noNumber = 0;
        $scanned = 0;

        DB::table('admin_users')
            ->select(DB::raw($cols))
            ->where('user_type', 'parent')
            ->orderBy('id')
            ->chunk(500, function ($rows) use (&$fixed, &$noNumber, &$scanned, $dry) {
                foreach ($rows as $row) {
                    $scanned++;
                    if (ParentsController::isRealPhone($row->phone_number_1)) {
                        continue; // already correct
                    }
                    $resolved = ParentsController::resolvePhone($row);
                    if ($resolved === null) {
                        $noNumber++;
                        continue; // genuinely has no number anywhere
                    }
                    if (!$dry) {
                        DB::table('admin_users')
                            ->where('id', $row->id)
                            ->update(['phone_number_1' => $resolved]);
                    }
                    $fixed++;
                }
            });

        $this->line('');
        $this->info('Phone backfill');
        $this->line("  parents scanned                    : {$scanned}");
        $this->line("  phone_number_1 " . ($dry ? 'would be' : '') . " filled from guardian cols : {$fixed}");
        $this->line("  still have no number anywhere      : {$noNumber}");
    }

    /**
     * Replace placeholder usernames with the account's real number, but only
     * when that number is not already used by another account — admin_users has
     * no unique index on username, so duplicates would make phone login resolve
     * to an arbitrary record.
     */
    private function cleanUsernames(bool $dry): void
    {
        $rows = DB::table('admin_users')
            ->select('id', 'username', 'phone_number_1')
            ->whereIn('username', ParentsController::PHONE_JUNK)
            ->get();

        $updated = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            if (!ParentsController::isRealPhone($row->phone_number_1)) {
                $skipped++;
                continue;
            }
            $phone = Utils::prepare_phone_number($row->phone_number_1);
            $taken = DB::table('admin_users')
                ->where('username', $phone)
                ->where('id', '!=', $row->id)
                ->exists();
            if ($taken) {
                $skipped++;
                continue;
            }
            if (!$dry) {
                DB::table('admin_users')->where('id', $row->id)->update(['username' => $phone]);
            }
            $updated++;
        }

        $this->line('');
        $this->info('Username cleanup');
        $this->line("  junk usernames found               : " . $rows->count());
        $this->line("  " . ($dry ? 'would be ' : '') . "set to the real number         : {$updated}");
        $this->line("  left alone (no/duplicate number)   : {$skipped}");
    }
}
