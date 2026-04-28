<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class FixEmtMenuVisibility extends Migration
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

        $records = DB::table('admin_menu')->where('uri', 'employee-monitoring-records')->first();
        if (!$records) {
            $nextOrder = (int) (DB::table('admin_menu')->where('parent_id', $parentId)->max('order') ?? 0) + 1;
            DB::table('admin_menu')->insert([
                'parent_id' => $parentId,
                'order' => $nextOrder,
                'title' => 'Employee Monitoring Records',
                'icon' => 'fa-user-secret',
                'uri' => 'employee-monitoring-records',
                'permission' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            DB::table('admin_menu')->where('id', $records->id)->update([
                'parent_id' => $parentId,
                'permission' => null,
                'updated_at' => now(),
            ]);
        }

        $reports = DB::table('admin_menu')->where('uri', 'employee-monitoring-report-records')->first();
        if (!$reports) {
            $nextOrder = (int) (DB::table('admin_menu')->where('parent_id', $parentId)->max('order') ?? 0) + 1;
            DB::table('admin_menu')->insert([
                'parent_id' => $parentId,
                'order' => $nextOrder,
                'title' => 'EMT Reports',
                'icon' => 'fa-bar-chart',
                'uri' => 'employee-monitoring-report-records',
                'permission' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            DB::table('admin_menu')->where('id', $reports->id)->update([
                'parent_id' => $parentId,
                'permission' => null,
                'updated_at' => now(),
            ]);
        }

        // Keep ordering sensible under Human resource.
        $recordId = DB::table('admin_menu')->where('uri', 'employee-monitoring-records')->value('id');
        $reportId = DB::table('admin_menu')->where('uri', 'employee-monitoring-report-records')->value('id');
        $maxOrder = (int) (DB::table('admin_menu')->where('parent_id', $parentId)->max('order') ?? 100);

        if ($recordId) {
            DB::table('admin_menu')->where('id', $recordId)->update(['order' => $maxOrder + 1]);
        }
        if ($reportId) {
            DB::table('admin_menu')->where('id', $reportId)->update(['order' => $maxOrder + 2]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Non-destructive rollback: keep menu entries as-is.
    }
}
