<?php

use App\Models\Enterprise;
use App\Models\StockItemCategory;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSupplierOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('supplier_orders', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->foreignIdFor(Enterprise::class)->onDelete('cascade')->onUpdate('cascade');
            $table->foreignIdFor(Administrator::class, 'created_by')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignIdFor(Administrator::class, 'created_to')->onDelete('cascade')->onUpdate('cascade');
            $table->text('payment_method')->nullable();
            $table->text('payment_account')->nullable();
            $table->text('payment_transaction_id')->nullable();
            $table->text('customer_note')->nullable();
            $table->text('supplier_note')->nullable();
            $table->integer('amount_payable')->nullable();
            $table->integer('paid_amount')->nullable();
            $table->integer('balance')->nullable();
            $table->text('buyer_paid')->nullable();
            $table->text('shipping_method')->nullable();
            $table->string('order_status')->nullable();
            $table->string('goods_received')->nullable();
            $table->text('supplier_paid')->nullable();
            $table->text('invoice')->nullable();
            $table->text('receipt')->nullable();
            $table->string('processed')->nullable();
            $table->string('attach_documents')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('supplier_orders');
    }
}
