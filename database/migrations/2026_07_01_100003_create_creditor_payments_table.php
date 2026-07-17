<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCreditorPaymentsTable extends Migration
{
    public function up()
    {
        Schema::create('creditor_payments', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('enterprise_id');
            $table->unsignedBigInteger('creditor_record_id');
            $table->unsignedBigInteger('created_by_id')->nullable();
            $table->bigInteger('amount_paid');
            $table->date('payment_date');
            $table->string('payment_method', 50)->nullable();
            $table->string('reference', 100)->nullable();
            $table->text('notes')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('creditor_payments');
    }
}
