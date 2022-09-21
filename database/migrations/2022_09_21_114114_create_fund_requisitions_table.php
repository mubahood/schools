<?php

use App\Models\Enterprise;
use App\Models\StockItemCategory;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFundRequisitionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fund_requisitions', function (Blueprint $table) {
            $table->id();
            $table->timestamps(); 
            $table->foreignIdFor(Enterprise::class);
            $table->foreignIdFor(StockItemCategory::class);
            $table->foreignIdFor(Administrator::class,'applied_by');
            $table->foreignIdFor(Administrator::class,'approved_by'); 
            $table->text('quantity')->nullable();
            $table->float('total_amount')->nullable();
            $table->text('invoice')->nullable();
            $table->text('status')->nullable();
            $table->text('description')->nullable();
            $table->date('deleted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fund_requisitions');
    }
}
