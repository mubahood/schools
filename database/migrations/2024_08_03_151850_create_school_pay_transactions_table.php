<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSchoolPayTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('school_pay_transactions', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('enterprise_id')->nullable();
            $table->foreignId('account_id')->nullable();
            $table->foreignId('academic_year_id')->nullable();
            $table->foreignId('term_id')->nullable();
            $table->foreignId('school_pay_transporter_id')->nullable();
            $table->foreignId('created_by_id')->nullable();
            $table->foreignId('contra_entry_account_id')->nullable();
            $table->foreignId('contra_entry_transaction_id')->nullable();
            $table->foreignId('termly_school_fees_balancing_id')->nullable();
            $table->string('amount')->nullable();
            $table->string('description')->nullable();
            $table->string('is_contra_entry')->nullable();
            $table->string('type')->nullable();
            $table->string('payment_date')->nullable();
            $table->string('source')->nullable();
            $table->string('status')->default('Not Imported');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('school_pay_transactions');
    }
}
