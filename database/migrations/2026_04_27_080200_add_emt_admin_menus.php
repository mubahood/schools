<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddEmtAdminMenus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $existingMain = DB::table('admin_menu')->where('uri', 'employee-monitoring-records')->first();
        if (!$existingMain) {
            $parent = DB::table('admin_menu')->where('uri', 'employees')->first();
            $parentId = $parent ? (int) $parent->id : 0;

            $order = (int) (DB::table('admin_menu')->max('order') ?? 0) + 1;

            DB::table('admin_menu')->insert([
                'parent_id' => $parentId,
                'order' => $order,
                'title' => 'Employee Monitoring Records',
                'icon' => 'fa-user-secret',
                'uri' => 'employee-monitoring-records',
                'permission' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $existingReports = DB::table('admin_menu')->where('uri', 'employee-monitoring-report-records')->first();
        if (!$existingReports) {
            $main = DB::table('admin_menu')->where('uri', 'employee-monitoring-records')->first();
            $parentId = $main ? (int) $main->id : 0;

            $maxOrder = (int) DB::table('admin_menu')->where('parent_id', $parentId)->max('order');
            $order = $maxOrder > 0 ? $maxOrder + 1 : ((int) (DB::table('admin_menu')->max('order') ?? 0) + 1);

            DB::table('admin_menu')->insert([
                'parent_id' => $parentId,
                'order' => $order,
                'title' => 'EMT Reports',
                'icon' => 'fa-bar-chart',
                'uri' => 'employee-monitoring-report-records',
                'permission' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('admin_menu')->where('uri', 'employee-monitoring-report-records')->delete();
        DB::table('admin_menu')->where('uri', 'employee-monitoring-records')->delete();
    }
}
