<?php

use App\Models\AcademicYear;
use App\Models\Enterprise;
use App\Models\Term;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportFinancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('report_finances', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Enterprise::class);
            $table->foreignIdFor(AcademicYear::class);
            $table->foreignIdFor(Term::class);
            $table->integer('total_expected_service_fees')->default(0)->nullable();
            $table->integer('total_expected_tuition')->default(0)->nullable();
            $table->integer('total_payment_school_pay')->default(0)->nullable();
            $table->integer('total_payment_manual_pay')->default(0)->nullable();
            $table->integer('total_payment_mobile_app')->default(0)->nullable();
            $table->integer('total_payment_total')->default(0)->nullable();
            $table->integer('total_school_fees_balance')->default(0)->nullable();
            $table->integer('total_budget')->default(0)->nullable();
            $table->integer('total_expense')->default(0)->nullable();
            $table->integer('total_stock_value')->default(0)->nullable();
            $table->integer('total_bursaries_funds')->default(0)->nullable();
            $table->text('messages')->nullable();
            $table->text('classes')->nullable();
            $table->text('active_studentes')->nullable();
            $table->text('active_studentes_ids')->nullable();
            $table->text('bursaries')->nullable();
            $table->text('services')->nullable();
            $table->text('services_sub_category')->nullable();
            $table->text('budget_vs_expenditure')->nullable();
            $table->text('stocks')->nullable();
            $table->text('other')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('report_finances');
    }
}
