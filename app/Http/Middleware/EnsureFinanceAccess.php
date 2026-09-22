<?php

namespace App\Http\Middleware;

use Closure;
use Encore\Admin\Facades\Admin;
use Illuminate\Http\Request;

/**
 * The finance module handles the school's money, so it is restricted to the
 * people accountable for it. Menu visibility was doing this job before, which
 * is not access control: the API routes sit outside the menu, and a teacher
 * could read and write the entire ledger by calling them directly.
 */
class EnsureFinanceAccess
{
    /** Roles that may see and record school spending. */
    public const ROLES = ['super-admin', 'admin', 'bursar', 'finance', 'hm', 'deputy-hm'];

    /** Roles that may only look. Everything else is refused outright. */
    public const READ_ONLY_ROLES = ['hm', 'deputy-hm'];

    public function handle(Request $request, Closure $next)
    {
        $u = Admin::user();
        if (!$u) {
            return $this->deny($request, 'Please sign in.', 401);
        }

        $allowed = false;
        foreach (self::ROLES as $r) {
            if ($u->isRole($r)) {
                $allowed = true;
                break;
            }
        }
        if (!$allowed) {
            return $this->deny($request, 'The finance module is restricted to finance staff.', 403);
        }

        // Heads oversee the books; they do not keep them.
        if (!in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'], true)) {
            $writer = $u->isRole('super-admin') || $u->isRole('admin') || $u->isRole('bursar') || $u->isRole('finance');
            if (!$writer) {
                return $this->deny($request, 'You have view-only access to finance records.', 403);
            }
        }

        return $next($request);
    }

    private function deny(Request $request, string $message, int $status)
    {
        if ($request->expectsJson() || $request->is('*/api/*') || $request->ajax()) {
            return response()->json(['success' => false, 'message' => $message], $status);
        }
        abort($status, $message);
    }
}
