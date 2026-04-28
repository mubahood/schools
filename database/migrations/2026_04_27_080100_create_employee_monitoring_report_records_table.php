<?php

use App\Models\Enterprise;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeMonitoringReportRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employee_monitoring_report_records', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Enterprise::class)->nullable()->index();
            $table->string('report_name')->nullable();
            $table->string('report_type')->default('individual_teacher')->index();
            $table->json('parameters')->nullable();
            $table->foreignIdFor(User::class, 'generated_by')->nullable()->index();
            $table->timestamp('generated_at')->nullable();
            $table->string('pdf_path')->nullable();
            $table->string('excel_path')->nullable();
            $table->string('status')->default('Pending')->index();
            $table->text('error_message')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employee_monitoring_report_records');
    }
}
