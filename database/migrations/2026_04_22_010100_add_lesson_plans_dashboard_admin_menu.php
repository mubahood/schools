<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $existing = DB::table('admin_menu')->where('uri', 'lesson-plans-dashboard')->first();
        if ($existing) {
            return;
        }

        $lessonMenu = DB::table('admin_menu')->where('uri', 'lesson-plans')->first();
        if (!$lessonMenu) {
            return;
        }

        $maxOrder = (int) DB::table('admin_menu')
            ->where('parent_id', $lessonMenu->parent_id)
            ->max('order');

        DB::table('admin_menu')->insert([
            'parent_id' => $lessonMenu->parent_id,
            'order' => $maxOrder + 1,
            'title' => 'Lesson Plan Dashboard',
            'icon' => 'fa-dashboard',
            'uri' => 'lesson-plans-dashboard',
            'permission' => null,
            'created_at' => now(),
            'updated_at' => now(),
            'access_by' => $lessonMenu->access_by,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('admin_menu')->where('uri', 'lesson-plans-dashboard')->delete();
    }
};
