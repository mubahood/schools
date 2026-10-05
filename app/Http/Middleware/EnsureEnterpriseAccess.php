<?php

namespace App\Http\Middleware;

use App\Models\Enterprise;
use App\Services\BillingService;
use Closure;
use Encore\Admin\Facades\Admin;
use Illuminate\Http\Request;

/**
 * Enforces the school's access lifecycle.
 *
 * A school whose invoice has passed its deadline, or whose licence has run
 * out, is locked: every screen gives way to the licence page, which carries
 * the bill and the way to pay it. The bill itself, the payment gateway and
 * logout are never locked away — locking someone out of the thing that
 * unlocks them would be a trap.
 *
 * Staff of the platform enterprise (id 1) and billing-exempt schools are
 * never touched. Clients that expect JSON get a refusal they can act on
 * rather than a redirect they cannot follow.
 */
class EnsureEnterpriseAccess
{
    /** Reachable even while locked. Everything else gives way to the licence page. */
    private function isAlwaysAllowed(Request $request): bool
    {
        return $request->is('licence-expired')
            || $request->is('billing*')
            || $request->is('invoice/*')
            || $request->is('gateway/*')
            || $request->is('auth/*')
            || $request->routeIs('admin.logout')
            || $request->is('api/users/login')
            || $request->is('api/users/me');
    }

    public function handle(Request $request, Closure $next)
    {
        $user = Admin::user() ?: auth('api')->user();
        if (!$user || !$user->enterprise_id) {
            return $next($request);
        }
        $ent = Enterprise::find($user->enterprise_id);
        if (!$ent || $ent->id == 1 || $ent->billing_exempt) {
            return $next($request);
        }

        $lock = BillingService::lockState($ent);
        $request->attributes->set('enterprise_access_status', $ent->fresh()->access_status);
        $request->attributes->set('enterprise_lock', $lock);

        if (!$lock) {
            return $next($request);
        }
        if ($this->isAlwaysAllowed($request)) {
            return $next($request);
        }

        $invoice = $lock['invoice'] ?? null;
        if ($request->is('api/*') || $request->expectsJson() || auth('api')->check()) {
            return response()->json([
                'code' => 0,
                'message' => 'This school\'s licence has expired. ' . $lock['reason']
                    . ' Please ask your school administrator to settle invoice '
                    . ($invoice->number ?? '') . '.',
                'data' => '',
                'locked' => true,
                'access' => $lock['status'],
                'days_overdue' => $lock['days_overdue'],
                'invoice_number' => $invoice->number ?? null,
                'amount_due' => $lock['amount'],
                'pay_url' => $invoice ? $invoice->publicUrl() : admin_url('billing'),
            ], 200);
        }

        return redirect(admin_url('licence-expired'));
    }
}
