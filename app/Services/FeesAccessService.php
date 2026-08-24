<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Decides whether a student's fees are cleared, for gating report-card access.
 *
 * Single source of truth: every endpoint that withholds results MUST go through
 * this class, so the rule cannot drift between the list, the detail and the PDF
 * routes.
 *
 * How a balance is stored
 * -----------------------
 * `accounts.balance` is the running sum of that account's transactions, so it is
 * NEGATIVE when money is owed, 0 when cleared and POSITIVE when in credit
 * (Transaction::my_update recomputes it on every change).
 *
 * Why the balance is SUMMED per student rather than read from one row
 * ------------------------------------------------------------------
 * A handful of students carry duplicate account rows (47 in the current data).
 * Summing is safe: no student has more than one non-zero balance, so the sum
 * equals the single real balance, and duplicates cannot hide a debt.
 *
 * Why `type` is filtered but `status` is not
 * -----------------------------------------
 * Fee accounts are 'STUDENT_ACCOUNT' (plus a legacy 'Student' spelling). Other
 * rows on the same table are bank/cash/employee/parent accounts and must never
 * count toward a student's fees. `accounts.status` is NOT filtered — it is 0 on
 * the majority of live fee accounts, so filtering it would wrongly clear
 * thousands of students who actually owe money.
 */
class FeesAccessService
{
    /** Account types that represent a student's school-fees account. */
    public const STUDENT_ACCOUNT_TYPES = ['STUDENT_ACCOUNT', 'Student'];

    /**
     * Signed fees balance for one student.
     * Negative = owes, 0 = cleared, positive = in credit.
     *
     * Returns 0 when the student has no fee account at all: nothing has been
     * billed, so there is nothing to withhold.
     */
    public static function balanceFor($studentId): int
    {
        $studentId = (int) $studentId;
        if ($studentId <= 0) {
            return 0;
        }

        return (int) DB::table('accounts')
            ->where('administrator_id', $studentId)
            ->whereIn('type', self::STUDENT_ACCOUNT_TYPES)
            ->sum('balance');
    }

    /**
     * Signed balances for many students at once, keyed by student id.
     * Used by list endpoints so gating cannot become an N+1 query.
     *
     * Students with no fee account are returned as 0 rather than omitted, so
     * callers can rely on every requested id being present.
     */
    public static function balancesFor(array $studentIds): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $studentIds))));
        if (empty($ids)) {
            return [];
        }

        $rows = DB::table('accounts')
            ->select('administrator_id', DB::raw('SUM(balance) AS bal'))
            ->whereIn('administrator_id', $ids)
            ->whereIn('type', self::STUDENT_ACCOUNT_TYPES)
            ->groupBy('administrator_id')
            ->pluck('bal', 'administrator_id');

        $out = [];
        foreach ($ids as $id) {
            $out[$id] = (int) ($rows[$id] ?? 0);
        }

        return $out;
    }

    /** Amount still owed as a POSITIVE number; 0 when cleared or in credit. */
    public static function outstandingFor($studentId): int
    {
        return self::outstandingFromBalance(self::balanceFor($studentId));
    }

    /** Convert a signed balance into a positive "amount owed". */
    public static function outstandingFromBalance($balance): int
    {
        $balance = (int) $balance;

        return $balance < 0 ? abs($balance) : 0;
    }

    /** True when the student owes nothing (balance is zero or in credit). */
    public static function hasCleared($studentId): bool
    {
        return self::balanceFor($studentId) >= 0;
    }

    /** Message shown to a parent whose child still owes fees. */
    public static function lockMessage($balance, $studentName = null): string
    {
        $owed = self::outstandingFromBalance($balance);
        $who  = $studentName ? trim($studentName) : 'this student';

        return 'Report card is locked. Outstanding school fees of UGX '
            . number_format($owed) . ' must be cleared before the report card for '
            . $who . ' can be viewed. Please contact the school bursar.';
    }

    /**
     * Staff bypass the gate entirely — it exists to withhold results from
     * parents, never from the people who run the school.
     */
    public static function isExemptStaff($user): bool
    {
        if ($user === null) {
            return false;
        }
        if (($user->user_type ?? null) === 'employee') {
            return true;
        }
        foreach (['super-admin', 'admin', 'dos', 'bursar', 'hm', 'nurse', 'warden', 'teacher'] as $role) {
            try {
                if (method_exists($user, 'isRole') && $user->isRole($role)) {
                    return true;
                }
            } catch (\Throwable $e) {
                // A missing/!broken role table must never grant access by accident.
                continue;
            }
        }

        return false;
    }
}
