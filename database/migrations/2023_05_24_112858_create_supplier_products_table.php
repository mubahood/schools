<?php

use App\Models\Enterprise;
use App\Models\StockItemCategory;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSupplierProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('supplier_products', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Enterprise::class)->onDelete('cascade')->onUpdate('cascade'); 
            $table->foreignIdFor(Administrator::class)->onDelete('cascade')->onUpdate('cascade'); 
            $table->foreignIdFor(StockItemCategory::class)->onDelete('cascade')->onUpdate('cascade');
            $table->text('name')->nullable(); 
            $table->text('image')->nullable(); 
            $table->text('images')->nullable(); 
            $table->text('details')->nullable(); 
            $table->integer('price')->nullable(); 
            $table->text('price_details')->nullable(); 


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('supplier_products');
    }
}
