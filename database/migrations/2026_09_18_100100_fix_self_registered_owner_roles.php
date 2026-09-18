<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The signup wizard assigned role 6 with the comment "Super-admin role".
 * Role 6 is "Director of Studies". Every self-registered owner (enterprise
 * id > 28) who holds both the Owner role (2) and DOS (6) loses the accidental
 * DOS role. Owners who were given only DOS on purpose are untouched.
 */
return new class extends Migration
{
    public function up()
    {
        // Two steps: MySQL refuses to read from the table being deleted from.
        $owners = DB::table('enterprises')->where('id', '>', 28)->pluck('administrator_id')->all();
        $withOwnerRole = DB::table('admin_role_users')->whereIn('user_id', $owners)->where('role_id', 2)->pluck('user_id')->all();
        if ($withOwnerRole) {
            DB::table('admin_role_users')->whereIn('user_id', $withOwnerRole)->where('role_id', 6)->delete();
        }
    }

    public function down()
    {
        // Intentionally irreversible: the removed role was never meant to exist.
    }
};
