<?php

use App\Models\Enterprise;
use App\Models\SupplierOrder;
use App\Models\SupplierProduct;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSupplierOrderItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('supplier_order_items', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Enterprise::class)->onDelete('cascade')->onUpdate('cascade'); 
            $table->foreignIdFor(SupplierOrder::class)->onDelete('cascade')->onUpdate('cascade'); 
            $table->foreignIdFor(SupplierProduct::class)->onDelete('cascade')->onUpdate('cascade'); 
            $table->integer('name')->nullable();  
            $table->integer('quantity')->nullable();  
            $table->integer('unit_price')->nullable();  
            $table->integer('total')->nullable();  
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('supplier_order_items');
    }
}
