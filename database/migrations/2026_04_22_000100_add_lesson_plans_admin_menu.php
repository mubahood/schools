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
        $exists = DB::table('admin_menu')->where('uri', 'lesson-plans')->first();
        if ($exists) {
            return;
        }

        $lessonMenu = DB::table('admin_menu')->where('uri', 'schems-work-items')->first();

        $parentId = $lessonMenu ? (int) $lessonMenu->parent_id : 0;
        $order = $lessonMenu ? ((int) $lessonMenu->order + 1) : ((int) (DB::table('admin_menu')->max('order') ?? 0) + 1);
        $accessBy = $lessonMenu && isset($lessonMenu->access_by) ? $lessonMenu->access_by : json_encode(['admin', 'dos', 'teacher', 'hm']);

        DB::table('admin_menu')->insert([
            'parent_id' => $parentId,
            'order' => $order,
            'title' => 'Lesson plans',
            'icon' => 'fa-book',
            'uri' => 'lesson-plans',
            'permission' => null,
            'access_by' => $accessBy,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('admin_menu')->where('uri', 'lesson-plans')->delete();
    }
};
