<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddProgressiveAssessmentAdminMenus extends Migration
{
    public function up()
    {
        // parent_id 41 = Examination
        $order = DB::table('admin_menu')->where('parent_id', 41)->max('order') ?? 0;

        DB::table('admin_menu')->insert([
            [
                'parent_id'  => 41,
                'order'      => $order + 1,
                'title'      => 'Progressive Assessment',
                'icon'       => 'fa-tasks',
                'uri'        => 'progressive-assessments',
                'permission' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'parent_id'  => 41,
                'order'      => $order + 2,
                'title'      => 'Test Marks Entry',
                'icon'       => 'fa-pencil-square-o',
                'uri'        => 'student-test-records',
                'permission' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'parent_id'  => 41,
                'order'      => $order + 3,
                'title'      => 'Test Reports',
                'icon'       => 'fa-file-text-o',
                'uri'        => 'student-progressive-reports',
                'permission' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'parent_id'  => 41,
                'order'      => $order + 4,
                'title'      => 'Test Report Card Printing',
                'icon'       => 'fa-print',
                'uri'        => 'pa-report-card-printing',
                'permission' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'parent_id'  => 41,
                'order'      => $order + 5,
                'title'      => 'Test Performance Stats',
                'icon'       => 'fa-bar-chart',
                'uri'        => 'pa-stats',
                'permission' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down()
    {
        DB::table('admin_menu')
            ->whereIn('uri', ['progressive-assessments', 'student-test-records', 'student-progressive-reports', 'pa-report-card-printing', 'pa-stats'])
            ->delete();
    }
}
