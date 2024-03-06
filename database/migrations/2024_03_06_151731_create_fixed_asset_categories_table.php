<?php

use App\Models\Enterprise;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFixedAssetCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fixed_asset_categories', function (Blueprint $table) {

            $table->uuid('id')->primary();
            $table->timestamps(); 
            $table->foreignIdFor(Enterprise::class);
            $table->text('name');
            $table->string('code')->nullable();
            $table->text('photo')->nullable();
            $table->text('description')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fixed_asset_categories');
    }
}
