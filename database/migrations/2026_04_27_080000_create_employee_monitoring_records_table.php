<?php

use App\Models\AcademicClass;
use App\Models\Enterprise;
use App\Models\Subject;
use App\Models\Term;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeMonitoringRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employee_monitoring_records', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Enterprise::class)->nullable()->index();
            $table->foreignIdFor(Term::class)->nullable()->index();
            $table->date('due_date')->nullable()->index();
            $table->date('monitored_on')->nullable()->index();
            $table->time('time_in')->nullable();
            $table->time('time_out')->nullable();
            $table->decimal('hours', 8, 2)->default(0);
            $table->integer('duration_minutes')->default(0);
            $table->foreignIdFor(Subject::class)->nullable()->index();
            $table->foreignIdFor(AcademicClass::class, 'academic_class_id')->nullable()->index();
            $table->foreignIdFor(User::class, 'employee_id')->nullable()->index();
            $table->text('comment')->nullable();
            $table->string('monitor_name')->nullable();
            $table->string('monitor_role')->nullable();
            $table->string('status')->default('Pending')->index();
            $table->foreignIdFor(User::class, 'created_by')->nullable()->index();
            $table->foreignIdFor(User::class, 'updated_by')->nullable()->index();
            $table->json('meta')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employee_monitoring_records');
    }
}
