<?php

namespace App\Services;

use App\Models\Enterprise;

/**
 * The one place that decides whether a user should be shown an outstanding
 * licence invoice, and what to say about it.
 *
 * Both dashboards and the navbar read from here, so the countdown in the
 * header and the bar on the page can never disagree.
 */
class BillingAlert
{
    /** People who can actually do something about a bill. */
    private const ROLES = ['admin', 'bursar', 'finance', 'hm', 'deputy-hm'];

    public static function forUser($user): ?array
    {
        if (!$user || !$user->enterprise_id) {
            return null;
        }
        $allowed = false;
        foreach (self::ROLES as $r) {
            if ($user->isRole($r)) {
                $allowed = true;
                break;
            }
        }
        if (!$allowed) {
            return null;
        }

        return self::forEnterprise(Enterprise::find($user->enterprise_id));
    }

    public static function forEnterprise(?Enterprise $ent): ?array
    {
        if (!$ent) {
            return null;
        }
        try {
            $inv = BillingService::dueInvoice($ent);
            if (!$inv) {
                return null;
            }
            $days = $inv->daysToDue();
            $locked = in_array($ent->access_status, ['suspended', 'cancelled'], true) && !$ent->billing_exempt;
            $overdue = $inv->isOverdue();

            return [
                'inv' => $inv,
                'days' => $days,
                'overdue' => $overdue,
                'locked' => $locked,
                'amount' => $inv->balance() ?: $inv->amount,
                'tone' => $locked ? '#8E0F0F' : ($overdue ? '#B3261E' : config('newline.brand')),
                'headline' => $locked ? 'System locked, licence unpaid'
                    : ($overdue ? 'Licence payment overdue' : 'Licence payment due'),
                // "14d" / "today" / "3d over" — short enough for a navbar chip
                'countdown' => $days === null ? null
                    : ($days < 0 ? abs($days) . 'd over' : ($days === 0 ? 'today' : $days . 'd left')),
                'pay_url' => admin_url('billing'),
                'pdf_url' => admin_url('billing/invoice/' . $inv->id . '/pdf'),
            ];
        } catch (\Throwable $e) {
            return null;
        }
    }
}
