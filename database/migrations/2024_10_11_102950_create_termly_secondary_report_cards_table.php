<?php

use App\Models\AcademicYear;
use App\Models\Enterprise;
use App\Models\Term;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTermlySecondaryReportCardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('termly_secondary_report_cards', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Enterprise::class);
            $table->foreignIdFor(AcademicYear::class);
            $table->foreignIdFor(Term::class);
            $table->string('report_title')->nullable();
            $table->string('has_u1')->default('Yes')->nullable();
            $table->string('has_u2')->default('Yes')->nullable();
            $table->string('has_u3')->default('Yes')->nullable();
            $table->string('has_u4')->default('No')->nullable();
            $table->string('has_u5')->default('No')->nullable();
            $table->string('do_update')->default('No')->nullable();
            $table->string('generate_marks')->default('No')->nullable();
            $table->string('generate_marks_for_classes')->nullable();
            $table->string('delete_marks_for_non_active')->default('No')->nullable();
            $table->string('submit_u1')->default('Yes')->nullable();
            $table->string('submit_u2')->default('Yes')->nullable();
            $table->string('submit_u3')->default('Yes')->nullable();
            $table->string('submit_u4')->default('No')->nullable();
            $table->string('submit_u5')->default('No')->nullable();
            $table->string('submit_project')->default('No')->nullable();
            $table->string('submit_exam')->default('No')->nullable();
            $table->string('reports_generate')->default('No')->nullable();
            $table->string('reports_include_u1')->default('No')->nullable();
            $table->string('reports_include_u2')->default('No')->nullable();
            $table->string('reports_include_u3')->default('No')->nullable();
            $table->string('reports_include_u4')->default('No')->nullable();
            $table->string('reports_include_u5')->default('No')->nullable();
            $table->string('reports_include_exam')->default('No')->nullable();
            $table->string('reports_include_project')->default('No')->nullable();
            $table->string('reports_template')->nullable();
            $table->string('reports_who_fees_balance')->default('No')->nullable();
            $table->string('reports_display_report_to_parents')->default('No')->nullable();
            $table->text('hm_communication')->nullable();
            $table->string('generate_class_teacher_comment')->nullable();
            $table->string('generate_head_teacher_comment')->nullable();
            $table->string('generate_positions')->nullable();
            $table->string('display_positions')->nullable();
            $table->text('bottom_message')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('termly_secondary_report_cards');
    }
}
