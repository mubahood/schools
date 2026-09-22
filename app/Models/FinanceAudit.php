<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * An append-only record of who touched money and what it looked like before.
 * Never updated, never deleted.
 */
class FinanceAudit extends Model
{
    protected $fillable = ['enterprise_id', 'subject_type', 'subject_id', 'action', 'actor_id',
        'actor_name', 'amount_before', 'amount_after', 'changes', 'ip'];

    public static function record(string $type, $subject, string $action, ?array $before = null, ?array $after = null): void
    {
        try {
            $actor = \Encore\Admin\Facades\Admin::user();
            $diff = [];
            if ($before !== null && $after !== null) {
                foreach ($after as $k => $v) {
                    if (($before[$k] ?? null) != $v) {
                        $diff[$k] = ['from' => $before[$k] ?? null, 'to' => $v];
                    }
                }
            }
            self::create([
                'enterprise_id' => $subject->enterprise_id,
                'subject_type' => $type,
                'subject_id' => $subject->id,
                'action' => $action,
                'actor_id' => $actor->id ?? null,
                'actor_name' => $actor->name ?? 'system',
                'amount_before' => $before['amount'] ?? null,
                'amount_after' => $after['amount'] ?? ($subject->amount ?? null),
                'changes' => $diff ? json_encode($diff) : null,
                'ip' => request()->ip(),
            ]);
        } catch (\Throwable $e) {
            // An audit failure must never block the transaction it describes.
            \Illuminate\Support\Facades\Log::error('Finance audit failed', ['error' => $e->getMessage()]);
        }
    }
}
