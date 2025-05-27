<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddErrorMessageSchoolPayTransactions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('school_pay_transactions', function (Blueprint $table) {
            $table->text('schoolpayReceiptNumber')->nullable();
            $table->text('paymentDateAndTime')->nullable();
            $table->text('settlementBankCode')->nullable();
            $table->text('sourceChannelTransDetail')->nullable();
            $table->text('sourceChannelTransactionId')->nullable();
            $table->text('sourcePaymentChannel')->nullable();
            $table->text('studentClass')->nullable();
            $table->text('studentName')->nullable();
            $table->text('studentPaymentCode')->nullable();
            $table->text('studentRegistrationNumber')->nullable();
            $table->text('transactionCompletionStatus')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('school_pay_transactions', function (Blueprint $table) {
            //
        });
    }
}
