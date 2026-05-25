<?php

use App\Models\Enterprise;
use App\Models\GradingScale;
use App\Models\Term;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProgressiveAssessmentsTable extends Migration
{
    public function up()
    {
        Schema::create('progressive_assessments', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->foreignIdFor(Enterprise::class)->nullable();
            $table->foreignIdFor(Term::class)->nullable();
            $table->bigInteger('academic_year_id')->nullable();
            $table->foreignIdFor(GradingScale::class)->nullable();

            $table->string('title')->nullable();
            $table->integer('number_of_tests')->default(10); // 1–10

            // classes to process (JSON array of academic_class_id)
            $table->text('classes')->nullable();

            // Visibility / submission control
            $table->string('can_submit_tests')->default('No');

            // Trigger flags — reset to 'No' after processing
            $table->string('generate_records')->default('No');
            $table->string('reports_generate')->default('No');
            $table->string('generate_positions')->default('No');
            $table->string('generate_comments')->default('No');
            $table->string('delete_records_for_non_active')->default('No');

            // Report options
            $table->string('positioning_type')->default('Class'); // Class / Stream
            $table->string('display_to_parents')->default('No');
            $table->string('display_positions')->default('Yes'); // Yes / No
            $table->string('display_class_teacher_comments')->default('Yes');

            $table->text('hm_communication')->nullable();
            $table->text('bottom_message')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('progressive_assessments');
    }
}
