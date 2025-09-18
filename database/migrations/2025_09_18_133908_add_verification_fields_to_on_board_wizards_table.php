<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVerificationFieldsToOnBoardWizardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('on_board_wizards', function (Blueprint $table) {
            $table->string('verification_token')->nullable()->after('completed_at');
            $table->timestamp('verification_sent_at')->nullable()->after('verification_token');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('on_board_wizards', function (Blueprint $table) {
            $table->dropColumn(['verification_token', 'verification_sent_at']);
        });
    }
}