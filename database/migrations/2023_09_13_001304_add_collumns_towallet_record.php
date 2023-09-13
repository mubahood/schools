<?php

use App\Models\Enterprise;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCollumnsTowalletRecord extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wallet_records', function (Blueprint $table) {
            $table->foreignIdFor(Enterprise::class);
        });
        Schema::table('enterprises', function (Blueprint $table) {
            $table->integer('wallet_balance')->default(0);
            $table->string('can_send_messages')->default('No');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('wallet_record', function (Blueprint $table) {
            //
        });
    }
}
