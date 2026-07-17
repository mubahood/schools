<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCreditFieldsToFinancialRecords extends Migration
{
    public function up()
    {
        Schema::table('financial_records', function (Blueprint $table) {
            $table->string('is_credit', 3)->default('No')->after('payment_method');
            $table->bigInteger('credit_amount')->nullable()->after('is_credit');
        });
    }

    public function down()
    {
        Schema::table('financial_records', function (Blueprint $table) {
            $table->dropColumn(['is_credit', 'credit_amount']);
        });
    }
}
