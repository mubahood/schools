<?php

namespace App\Admin\Controllers;

use App\Models\Enterprise;
use App\Services\BillingService;
use Encore\Admin\Facades\Admin;
use Illuminate\Routing\Controller;

/**
 * The screen a locked school sees instead of the system.
 *
 * Who sees what is deliberate: the people who can settle the bill get the
 * bill. Everyone else gets a plain explanation and the name of the person to
 * speak to, because showing a teacher an invoice they cannot pay only moves
 * the confusion around.
 */
class LicenceController extends Controller
{
    /** The people at a school who can act on an invoice. */
    public const BILLING_ROLES = ['admin', 'bursar', 'finance', 'hm', 'deputy-hm'];

    public function expired()
    {
        $u = Admin::user();
        $ent = $u ? Enterprise::find($u->enterprise_id) : null;
        if (!$ent) {
            return redirect(admin_url('auth/login'));
        }

        $lock = BillingService::lockState($ent);
        if (!$lock) {
            // Paid while they were sitting on this page: send them back in.
            return redirect(admin_url('/'));
        }

        $canSettle = false;
        foreach (self::BILLING_ROLES as $r) {
            if ($u->isRole($r)) {
                $canSettle = true;
                break;
            }
        }

        return view('admin.billing.licence-expired', [
            'ent' => $ent,
            'lock' => $lock,
            'invoice' => $lock['invoice'],
            'user' => $u,
            'canSettle' => $canSettle,
            'contact' => $this->schoolContact($ent),
            'co' => config('newline'),
        ]);
    }

    /** Who a teacher should actually go and find. */
    private function schoolContact(Enterprise $ent): ?object
    {
        $owner = $ent->administrator_id ? \App\Models\User::find($ent->administrator_id) : null;
        if ($owner) {
            return (object) ['name' => $owner->name, 'phone' => $owner->phone_number_1, 'email' => $owner->email];
        }

        return $ent->phone_number || $ent->email
            ? (object) ['name' => $ent->name, 'phone' => $ent->phone_number, 'email' => $ent->email]
            : null;
    }
}
