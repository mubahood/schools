<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOnesignalPlayerIdToAdminUsers extends Migration
{
    public function up()
    {
        Schema::table('admin_users', function (Blueprint $table) {
            if (!Schema::hasColumn('admin_users', 'onesignal_player_id')) {
                $table->text('onesignal_player_id')->nullable()->after('avatar');
            }
        });
    }

    public function down()
    {
        Schema::table('admin_users', function (Blueprint $table) {
            $table->dropColumn('onesignal_player_id');
        });
    }
}
