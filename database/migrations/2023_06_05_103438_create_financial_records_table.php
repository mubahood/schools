<?php

use App\Models\AcademicYear;
use App\Models\Account;
use App\Models\Enterprise;
use App\Models\Term;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFinancialRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('financial_records', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Enterprise::class);
            $table->foreignIdFor(Account::class);
            $table->foreignIdFor(AcademicYear::class);
            $table->foreignIdFor(Term::class);
            $table->integer('parent_account_id');
            $table->integer('created_by_id');
            $table->bigInteger('amount')->default(0);
            $table->bigInteger('termly_school_fees_balancing_id')->nullable();
            $table->text('description')->nullable();
            $table->string('type')->nullable();
            $table->date('payment_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('financial_records');
    }
}
