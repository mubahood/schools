<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddEmtDashboardAdminMenu extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $hr = DB::table('admin_menu')->where('uri', 'employees')->first();
        if (!$hr) {
            return;
        }

        $parentId = (int) $hr->id;
        $dashboard = DB::table('admin_menu')->where('uri', 'employee-monitoring-dashboard')->first();

        if (!$dashboard) {
            $nextOrder = (int) (DB::table('admin_menu')->where('parent_id', $parentId)->max('order') ?? 0) + 1;
            DB::table('admin_menu')->insert([
                'parent_id' => $parentId,
                'order' => $nextOrder,
                'title' => 'EMT Dashboard',
                'icon' => 'fa-dashboard',
                'uri' => 'employee-monitoring-dashboard',
                'permission' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            DB::table('admin_menu')->where('id', $dashboard->id)->update([
                'parent_id' => $parentId,
                'title' => 'EMT Dashboard',
                'icon' => 'fa-dashboard',
                'permission' => null,
                'updated_at' => now(),
            ]);
        }

        $dashboardId = DB::table('admin_menu')->where('uri', 'employee-monitoring-dashboard')->value('id');
        $recordId = DB::table('admin_menu')->where('uri', 'employee-monitoring-records')->value('id');
        $reportId = DB::table('admin_menu')->where('uri', 'employee-monitoring-report-records')->value('id');
        $maxOrder = (int) (DB::table('admin_menu')->where('parent_id', $parentId)->max('order') ?? 100);

        if ($dashboardId) {
            DB::table('admin_menu')->where('id', $dashboardId)->update(['order' => $maxOrder + 1]);
        }
        if ($recordId) {
            DB::table('admin_menu')->where('id', $recordId)->update(['order' => $maxOrder + 2]);
        }
        if ($reportId) {
            DB::table('admin_menu')->where('id', $reportId)->update(['order' => $maxOrder + 3]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('admin_menu')->where('uri', 'employee-monitoring-dashboard')->delete();
    }
}
