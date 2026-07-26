<?php

use Illuminate\Database\Migrations\Migration;

class SeedSplitTransactionsMenu extends Migration
{
    public function up()
    {
        // Add under "School fees" parent (id = 58)
        $exists = DB::table('admin_menu')->where('uri', 'split-transactions')->exists();
        if (!$exists) {
            DB::table('admin_menu')->insert([
                'parent_id' => 58,
                'order'     => 48,
                'title'     => 'Split Transactions',
                'icon'      => 'fa-code-fork',
                'uri'       => 'split-transactions',
                'permission' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down()
    {
        DB::table('admin_menu')->where('uri', 'split-transactions')->delete();
    }
}
