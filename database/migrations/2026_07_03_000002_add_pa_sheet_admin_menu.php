<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddPaSheetAdminMenu extends Migration
{
    public function up()
    {
        // Find or create the Progressive Assessment parent menu
        $parent = DB::table('admin_menu')
            ->where('title', 'Progressive Assessment')
            ->whereNull('parent_id')
            ->first();

        // If not found at top level, find any PA parent
        if (!$parent) {
            $parent = DB::table('admin_menu')
                ->where('title', 'Progressive Assessment')
                ->first();
        }

        $parentId = $parent ? $parent->id : 0;
        $order    = DB::table('admin_menu')->max('order') + 1;

        // Add the PA Sheet menu item
        $exists = DB::table('admin_menu')->where('uri', 'progressive-assessment-sheets')->exists();
        if (!$exists) {
            DB::table('admin_menu')->insert([
                'parent_id'  => $parentId,
                'order'      => $order,
                'title'      => 'PA Assessment Sheets',
                'icon'       => 'fa-file-text-o',
                'uri'        => 'progressive-assessment-sheets',
                'permission' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down()
    {
        DB::table('admin_menu')->where('uri', 'progressive-assessment-sheets')->delete();
    }
}
