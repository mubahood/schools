<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddFinanceDashboardMenu extends Migration
{
    public function up()
    {
        // Skip if already inserted
        if (DB::table('admin_menu')->where('uri', 'finance-dashboard')->exists()) {
            return;
        }

        // Finance parent is id=73; insert Dashboard at order=0 (first)
        DB::table('admin_menu')->insert([
            'parent_id'  => 73,
            'order'      => 0,
            'title'      => 'Finance Overview',
            'icon'       => 'fa-tachometer',
            'uri'        => 'finance-dashboard',
            'permission' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Shift existing Finance children up by 1 so Overview stays first
        DB::table('admin_menu')
            ->where('parent_id', 73)
            ->where('uri', '!=', 'finance-dashboard')
            ->increment('order');
    }

    public function down()
    {
        DB::table('admin_menu')->where('uri', 'finance-dashboard')->delete();
    }
}
