<?php

use App\Models\TheologyClass;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEnterpriseIdToStreams extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('theology_streams', function (Blueprint $table) {
            //
            $table->foreignIdFor(TheologyClass::class)->nullable();  
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('theology_streams', function (Blueprint $table) {
            //
        });
    }
}
