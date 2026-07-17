<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSupplierIdToFinancialRecords extends Migration
{
    public function up()
    {
        Schema::table('financial_records', function (Blueprint $table) {
            $table->unsignedBigInteger('supplier_id')->nullable()->after('created_by_id');
            $table->string('payment_method', 100)->nullable()->after('supplier_id');
        });
    }

    public function down()
    {
        Schema::table('financial_records', function (Blueprint $table) {
            $table->dropColumn(['supplier_id', 'payment_method']);
        });
    }
}
