<?php

use App\Models\Enterprise;
use App\Models\StockBatch;
use App\Models\StockItemCategory;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_records', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Enterprise::class);
            $table->foreignIdFor(StockBatch::class);
            $table->foreignIdFor(StockItemCategory::class);
            $table->foreignIdFor(Administrator::class, 'created_by');
            $table->foreignIdFor(Administrator::class, 'received_by');
            $table->float('quanity');
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
        Schema::dropIfExists('stock_records');
    }
}
