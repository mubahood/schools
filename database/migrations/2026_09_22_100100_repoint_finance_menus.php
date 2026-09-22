<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The old suppliers and creditor-payments screens were retired in favour of
 * the finance module's own pages. Their menu entries would otherwise 404.
 */
return new class extends Migration
{
    public function up()
    {
        DB::table('admin_menu')->where('uri', 'suppliers')->update(['uri' => 'finance-suppliers']);
        DB::table('admin_menu')->where('uri', 'creditor-payments')->update(['uri' => 'finance-creditors']);

        // Two identical "Suppliers" entries existed; keep the first.
        $dupes = DB::table('admin_menu')->where('uri', 'finance-suppliers')->orderBy('id')->pluck('id');
        if ($dupes->count() > 1) {
            $keep = $dupes->first();
            $drop = $dupes->filter(fn ($id) => $id !== $keep && $id != 222)->values();
            if ($drop->isNotEmpty()) {
                DB::table('admin_role_menu')->whereIn('menu_id', $drop)->delete();
                DB::table('admin_menu')->whereIn('id', $drop)->delete();
            }
        }
    }

    public function down()
    {
        // Pointing them back at deleted routes would only recreate the 404.
    }
};
