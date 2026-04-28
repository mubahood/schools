<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddParentCommitmentAdminMenus extends Migration
{
    /**
     * Fees module parent_id = 58 ("School fees" menu item).
     * Add two new items under it:
     *   1. Commitment Records  (uri: parent-commitment-records)
     *   2. Commitment Dashboard (uri: parent-commitment-dashboard)
     */
    public function up()
    {
        $feesParentId = 58; // "School fees" root menu — confirmed via production DB

        // 1. Commitment Records
        $existing = DB::table('admin_menu')->where('uri', 'parent-commitment-records')->first();
        if (!$existing) {
            $order = (int)(DB::table('admin_menu')->max('order') ?? 0) + 1;
            DB::table('admin_menu')->insert([
                'parent_id'  => $feesParentId,
                'order'      => $order,
                'title'      => 'Commitment Records',
                'icon'       => 'fa-handshake-o',
                'uri'        => 'parent-commitment-records',
                'permission' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 2. Commitment Dashboard
        $existingDash = DB::table('admin_menu')->where('uri', 'parent-commitment-dashboard')->first();
        if (!$existingDash) {
            $order = (int)(DB::table('admin_menu')->max('order') ?? 0) + 1;
            DB::table('admin_menu')->insert([
                'parent_id'  => $feesParentId,
                'order'      => $order,
                'title'      => 'Commitment Dashboard',
                'icon'       => 'fa-tachometer',
                'uri'        => 'parent-commitment-dashboard',
                'permission' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down()
    {
        DB::table('admin_menu')->whereIn('uri', [
            'parent-commitment-records',
            'parent-commitment-dashboard',
        ])->delete();
    }
}
