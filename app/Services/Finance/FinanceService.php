<?php

namespace App\Services\Finance;

use App\Models\Account;
use App\Models\CreditorPayment;
use App\Models\CreditorRecord;
use App\Models\FinanceAudit;
use App\Models\FinancialRecord;
use App\Models\Term;
use Illuminate\Support\Facades\DB;

/**
 * Every write to the finance ledger goes through here.
 *
 * The rules it enforces, in one place instead of duplicated across two model
 * hooks and six controllers:
 *   - a record may only reference a term, account or supplier of its own school
 *   - expenditures are stored negative, budgets positive, always
 *   - the money is never recomputed out from under a row that already has it
 *   - credit spending keeps its creditor record in step, including when the
 *     spending stops being on credit or is deleted
 *   - a creditor can never be paid more than it is owed
 * Each public method is transactional and audited.
 */
class FinanceService
{
    public const TYPE_BUDGET = 'BUDGET';
    public const TYPE_EXPENDITURE = 'EXPENDITURE';

    // ── Normalisation ───────────────────────────────────────────────────

    /**
     * Derive the columns that must never come from the request: the term's
     * academic year, the account's vote, and the signed amount.
     *
     * @param FinancialRecord $m       the record being written
     * @param bool            $moneyGiven whether the caller actually supplied
     *                        quantity/unit_price. When it did not, the existing
     *                        amount is left alone — this is what stops a
     *                        description-only edit from resetting a row whose
     *                        quantity and unit price were never captured.
     */
    public static function normalise(FinancialRecord $m, bool $moneyGiven = true): void
    {
        if (!in_array($m->type, [self::TYPE_BUDGET, self::TYPE_EXPENDITURE], true)) {
            throw new FinanceException('A record must be a BUDGET or an EXPENDITURE.');
        }
        if (!$m->enterprise_id) {
            throw new FinanceException('A financial record must belong to a school.');
        }

        $term = Term::where('enterprise_id', $m->enterprise_id)->find($m->term_id);
        if (!$term) {
            throw new FinanceException('That term does not belong to this school.');
        }
        $m->term_id = $term->id;
        $m->academic_year_id = $term->academic_year_id;

        $account = Account::where('enterprise_id', $m->enterprise_id)->find($m->account_id);
        if (!$account) {
            throw new FinanceException('That account does not belong to this school.');
        }
        $m->account_id = $account->id;
        $m->parent_account_id = $account->account_parent_id;

        if ($m->supplier_id) {
            $ok = DB::table('admin_users')->where('id', $m->supplier_id)
                ->where('enterprise_id', $m->enterprise_id)->exists();
            if (!$ok) {
                throw new FinanceException('That supplier does not belong to this school.');
            }
        }

        if ($moneyGiven) {
            $qty = max(1, (int) ($m->quantity ?: 1));
            $unit = max(0, (int) ($m->unit_price ?: 0));
            $m->quantity = $qty;
            $m->unit_price = $unit;
            $m->amount = $qty * $unit;
        }

        $m->amount = self::sign($m->type, (int) $m->amount);

        if ($m->type === self::TYPE_EXPENDITURE && ($m->is_credit ?? 'No') === 'Yes') {
            $credit = abs((int) $m->credit_amount);
            if ($credit <= 0) {
                throw new FinanceException('An expenditure on credit needs a credit amount.');
            }
            if ($credit > abs((int) $m->amount)) {
                throw new FinanceException('The credit amount cannot exceed the expenditure itself.');
            }
            $m->credit_amount = $credit;
        } else {
            $m->is_credit = 'No';
            $m->credit_amount = null;
        }
    }

    /** Expenditures are negative, budgets positive. Never the other way round. */
    public static function sign(string $type, int $amount): int
    {
        $abs = abs($amount);

        return $type === self::TYPE_EXPENDITURE ? -$abs : $abs;
    }

    // ── Writes ──────────────────────────────────────────────────────────

    public static function create(array $data, string $type): FinancialRecord
    {
        return DB::transaction(function () use ($data, $type) {
            $m = new FinancialRecord();
            $m->fill($data);
            $m->type = $type;
            $m->save();

            self::syncCreditor($m);
            FinanceAudit::record('financial_record', $m, 'created', null, $m->toArray());

            return $m->fresh();
        });
    }

    public static function update(FinancialRecord $m, array $data): FinancialRecord
    {
        return DB::transaction(function () use ($m, $data) {
            $before = $m->toArray();
            // A caller that sends neither quantity nor unit price is not
            // editing the money, so the money is left exactly as it is.
            $moneyGiven = array_key_exists('quantity', $data) && array_key_exists('unit_price', $data);
            $m->fill($data);
            $m->moneyGiven = $moneyGiven;
            $m->save();

            self::syncCreditor($m);
            FinanceAudit::record('financial_record', $m, 'updated', $before, $m->toArray());

            return $m->fresh();
        });
    }

    public static function delete(FinancialRecord $m): void
    {
        DB::transaction(function () use ($m) {
            $before = $m->toArray();
            $creditor = CreditorRecord::where('financial_record_id', $m->id)->first();
            if ($creditor) {
                $paid = (int) CreditorPayment::where('creditor_record_id', $creditor->id)->sum('amount_paid');
                if ($paid > 0) {
                    throw new FinanceException(
                        'This expenditure has supplier payments recorded against it. Reverse the payments first.'
                    );
                }
                FinanceAudit::record('creditor_record', $creditor, 'deleted', $creditor->toArray(), null);
                $creditor->delete();
            }
            FinanceAudit::record('financial_record', $m, 'deleted', $before, null);
            $m->delete();
        });
    }

    // ── Creditors ───────────────────────────────────────────────────────

    /**
     * Keep the creditor ledger in step with the expenditure that created it,
     * including the cases the old hooks ignored: credit switched off, and the
     * expenditure deleted.
     */
    public static function syncCreditor(FinancialRecord $m): void
    {
        if ($m->type !== self::TYPE_EXPENDITURE) {
            return;
        }
        $creditor = CreditorRecord::where('financial_record_id', $m->id)->first();
        $onCredit = ($m->is_credit ?? 'No') === 'Yes' && abs((int) $m->credit_amount) > 0;

        if (!$onCredit) {
            if ($creditor) {
                $paid = (int) CreditorPayment::where('creditor_record_id', $creditor->id)->sum('amount_paid');
                if ($paid > 0) {
                    throw new FinanceException(
                        'Payments have already been made against this credit. It cannot be switched to cash.'
                    );
                }
                FinanceAudit::record('creditor_record', $creditor, 'deleted', $creditor->toArray(), null);
                $creditor->delete();
            }
            return;
        }

        if (!$creditor) {
            $creditor = new CreditorRecord();
            $creditor->financial_record_id = $m->id;
            $creditor->paid_amount = 0;
            $creditor->status = 'Pending';
        }
        $creditor->enterprise_id = $m->enterprise_id;
        $creditor->supplier_id = $m->supplier_id;
        $creditor->account_id = $m->account_id;
        $creditor->term_id = $m->term_id;
        $creditor->academic_year_id = $m->academic_year_id;
        $creditor->description = $m->description ?? '';
        $creditor->original_amount = abs((int) $m->credit_amount);
        $creditor->created_by_id = $creditor->created_by_id ?: $m->created_by_id;
        $creditor->save();
        $creditor->updateStatus();
    }

    /** Record a payment to a supplier. Refuses to overpay; safe under concurrency. */
    public static function payCreditor(CreditorRecord $creditor, array $data): CreditorPayment
    {
        return DB::transaction(function () use ($creditor, $data) {
            // Lock the creditor so two tellers cannot both see the same balance.
            $locked = CreditorRecord::where('id', $creditor->id)->lockForUpdate()->first();
            $paid = (int) CreditorPayment::where('creditor_record_id', $locked->id)->sum('amount_paid');
            $outstanding = max(0, (int) $locked->original_amount - $paid);
            $amount = (int) $data['amount_paid'];

            if ($amount > $outstanding) {
                throw new FinanceException(
                    'That is more than is owed. Outstanding balance is UGX ' . number_format($outstanding) . '.'
                );
            }

            $p = CreditorPayment::create($data + [
                'creditor_record_id' => $locked->id,
                'enterprise_id' => $locked->enterprise_id,
            ]);
            $locked->updateStatus();
            FinanceAudit::record('creditor_payment', $p, 'paid', null, $p->toArray());

            return $p;
        });
    }

    public static function deleteCreditorPayment(CreditorPayment $p): ?CreditorRecord
    {
        return DB::transaction(function () use ($p) {
            $creditor = CreditorRecord::find($p->creditor_record_id);
            FinanceAudit::record('creditor_payment', $p, 'deleted', $p->toArray(), null);
            $p->delete();
            if ($creditor) {
                $creditor->updateStatus();
            }

            return $creditor;
        });
    }
}
