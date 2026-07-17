<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCreditorRecordsTable extends Migration
{
    public function up()
    {
        Schema::create('creditor_records', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('enterprise_id');
            $table->unsignedBigInteger('financial_record_id')->nullable(); // source expenditure
            $table->unsignedBigInteger('supplier_id')->nullable();         // who is owed
            $table->unsignedBigInteger('account_id')->nullable();
            $table->unsignedBigInteger('term_id')->nullable();
            $table->unsignedBigInteger('academic_year_id')->nullable();
            $table->unsignedBigInteger('created_by_id')->nullable();
            $table->string('description')->nullable();
            $table->bigInteger('original_amount')->default(0);  // total credit owed (positive)
            $table->bigInteger('paid_amount')->default(0);      // total paid so far
            $table->bigInteger('balance')->default(0);          // original - paid
            $table->date('due_date')->nullable();
            $table->string('status', 20)->default('Pending');   // Pending/Partial/Paid/Overdue
            $table->string('payment_method', 50)->nullable();
            $table->text('notes')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('creditor_records');
    }
}
