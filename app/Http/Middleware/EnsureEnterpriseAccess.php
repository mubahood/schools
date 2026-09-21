<?php

namespace App\Http\Middleware;

use App\Models\Enterprise;
use App\Services\BillingService;
use Closure;
use Encore\Admin\Facades\Admin;
use Illuminate\Http\Request;

/**
 * Enforces the school's access lifecycle. Replaces the die() in admin/bootstrap.php.
 *
 * trialing / active / past_due  -> full access (past_due shows a banner)
 * suspended                     -> read-only: GET passes, writes are refused,
 *                                  billing and logout always allowed
 * cancelled                     -> billing page only
 *
 * Staff of the platform enterprise (id 1) and billing-exempt schools are never
 * touched. The parent mobile API gets a clean JSON refusal, never a redirect.
 */
class EnsureEnterpriseAccess
{
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

        // Keep the status honest even if the daily tick has not run today.
        $status = BillingService::refreshAccess($ent);
        $request->attributes->set('enterprise_access_status', $status);

        if (in_array($status, [BillingService::TRIALING, BillingService::ACTIVE, BillingService::PAST_DUE, BillingService::PENDING_VERIFICATION], true)) {
            return $next($request);
        }

        $alwaysAllowed = $request->is('billing*') || $request->is('invoice/*') || $request->is('gateway/*') || $request->is('auth/logout')
            || $request->routeIs('admin.logout') || $request->is('api/users/login') || $request->is('api/users/me');
        if ($alwaysAllowed) {
            return $next($request);
        }

        $readOnly = $status === BillingService::SUSPENDED && in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'], true);
        if ($readOnly) {
            return $next($request);
        }

        $isApi = $request->is('api/*') || $request->expectsJson() || auth('api')->check();
        if ($isApi) {
            return response()->json([
                'code' => 0,
                'message' => $status === BillingService::SUSPENDED
                    ? 'This school\'s subscription is suspended. Changes are disabled until payment is made.'
                    : 'This school\'s subscription is not active.',
                'data' => '',
                'access' => $status,
                'pay_url' => admin_url('billing'),
            ], 200);
        }

        return redirect(admin_url('billing'))->with('billing_blocked', $status);
    }
}
