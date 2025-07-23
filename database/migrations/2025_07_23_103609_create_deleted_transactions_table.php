<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeletedTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deleted_transactions', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->text('enterprise_id')->nullable();
            $table->text('account_id')->nullable();
            $table->text('amount')->nullable();
            $table->text('description')->nullable();
            $table->text('academic_year_id')->nullable();
            $table->text('term_id')->nullable();
            $table->text('school_pay_transporter_id')->nullable();
            $table->text('created_by_id')->nullable();
            $table->text('is_contra_entry')->nullable();
            $table->text('type')->nullable();
            $table->text('contra_entry_account_id')->nullable();
            $table->text('contra_entry_transaction_id')->nullable();
            $table->text('payment_date')->nullable();
            $table->text('termly_school_fees_balancing_id')->nullable();
            $table->text('source')->nullable();
            $table->text('academic_class_fee_id')->nullable();
            $table->text('is_last_term_balance')->nullable();
            $table->text('is_tuition')->nullable();
            $table->text('is_service')->nullable();
            $table->text('service_id')->nullable();
            $table->text('receipt_photo')->nullable();
            $table->text('peg_pay_transaction_number')->nullable();
            $table->text('bank_transaction_number')->nullable();
            $table->text('platform')->nullable();
            $table->text('cash_receipt_number')->nullable();
            $table->text('bank_account_id')->nullable();
            $table->text('particulars')->nullable();
            $table->text('deleted_by_id')->nullable();
            $table->text('deleted_at')->nullable();
            $table->text('deleted_reason')->nullable();
            $table->text('deleted_source')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('deleted_transactions');
    }
}
