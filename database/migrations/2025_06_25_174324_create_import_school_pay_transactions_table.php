<?php

use App\Models\Enterprise;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateImportSchoolPayTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('import_school_pay_transactions', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Enterprise::class, 'enterprise_id');
            $table->string('school_pay_transporter_id')->nullable();
            $table->string('amount')->nullable();
            $table->string('description')->nullable();
            $table->string('payment_date')->nullable();
            $table->string('schoolpayReceiptNumber')->nullable();
            $table->string('paymentDateAndTime')->nullable();
            $table->string('settlementBankCode')->nullable();
            $table->string('sourceChannelTransDetail')->nullable();
            $table->string('sourceChannelTransactionId')->nullable();
            $table->string('sourcePaymentChannel')->nullable();
            $table->string('studentClass')->nullable();
            $table->string('studentName')->nullable();
            $table->string('studentPaymentCode')->nullable();
            $table->string('studentRegistrationNumber')->nullable();
            $table->string('transactionCompletionStatus')->nullable();
            $table->string('file_path')->nullable();
            $table->string('source')->nullable();

            /* 
            Date created
            Description
            Student name
            Class code
            Payment code
            Registration number
            Channel trans id
            Reciept number
            Channel code
            Channel memo
            Amount
            Bank name
            */
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('import_school_pay_transactions');
    }
}
