<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Clear URI on id=204 so it becomes a pure group/folder
        DB::table('admin_menu')->where('id', 204)->update([
            'uri'   => '',
            'title' => 'Progressive Assessment',
            'icon'  => 'fa-tasks',
            'order' => 58,
        ]);

        // 2. Insert "Manage Assessments" as first child of 204
        $exists = DB::table('admin_menu')
            ->where('parent_id', 204)->where('uri', 'progressive-assessments')->exists();
        if (!$exists) {
            DB::table('admin_menu')->insert([
                'parent_id'  => 204,
                'title'      => 'Manage Assessments',
                'uri'        => 'progressive-assessments',
                'icon'       => 'fa-list',
                'order'      => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 3. Move Test items (205,206,207,208) under 204 and set clean order
        DB::table('admin_menu')->where('id', 205)->update(['parent_id'=>204,'order'=>20,'title'=>'Marks Entry']);
        DB::table('admin_menu')->where('id', 206)->update(['parent_id'=>204,'order'=>30,'title'=>'Student Reports']);
        DB::table('admin_menu')->where('id', 207)->update(['parent_id'=>204,'order'=>40,'title'=>'Print Report Cards','icon'=>'fa-print']);
        DB::table('admin_menu')->where('id', 208)->update(['parent_id'=>204,'order'=>50,'title'=>'Performance Stats','icon'=>'fa-bar-chart']);
        DB::table('admin_menu')->where('id', 212)->update(['parent_id'=>204,'order'=>60,'title'=>'Assessment Sheets','icon'=>'fa-file-text-o']);
    }

    public function down(): void
    {
        // Restore original flat structure
        DB::table('admin_menu')->where('id', 204)->update(['uri'=>'progressive-assessments','order'=>58]);
        DB::table('admin_menu')->where('parent_id', 204)->where('uri', 'progressive-assessments')
            ->whereIn('title', ['Manage Assessments'])->delete();
        DB::table('admin_menu')->whereIn('id',[205,206,207,208])->update(['parent_id'=>41]);
    }
};
