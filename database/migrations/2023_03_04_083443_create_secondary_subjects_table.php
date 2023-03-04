<?php

use App\Models\AcademicClass;
use App\Models\AcademicYear;
use App\Models\Enterprise;
use App\Models\ParentCourse;
use App\Models\Term;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSecondarySubjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('secondary_subjects', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Enterprise::class)->onDelete('cascade')->onUpdate('cascade')->nullable(); 
            $table->foreignIdFor(AcademicClass::class)->onDelete('cascade')->onUpdate('cascade')->nullable(); 
            $table->foreignIdFor(ParentCourse::class)->onDelete('cascade')->onUpdate('cascade')->nullable(); 
            $table->foreignIdFor(AcademicYear::class)->onDelete('cascade')->onUpdate('cascade')->nullable(); 
            $table->foreignIdFor(Administrator::class,'teacher_1')->onDelete('cascade')->onUpdate('cascade')->nullable(); 
            $table->foreignIdFor(Administrator::class,'teacher_2')->onDelete('cascade')->onUpdate('cascade')->nullable(); 
            $table->foreignIdFor(Administrator::class,'teacher_3')->onDelete('cascade')->onUpdate('cascade')->nullable(); 
            $table->foreignIdFor(Administrator::class,'teacher_4')->onDelete('cascade')->onUpdate('cascade')->nullable(); 
            $table->text('subject_name')->nullable(); 
            $table->text('details')->nullable(); 
            $table->text('code')->nullable(); 
            $table->tinyInteger('is_optional')->nullable(); 
        }); 
    }
/* 			



id	
name	
short_name	
code	
type	
is_verified Descending 1	
is_compulsory	
s1_term1_topics	
s1_term2_topics	
s1_term3_topics	
s2_term1_topics	
s2_term2_topics	
s2_term3_topics	
s3_term1_topics	
s3_term2_topics	
s3_term3_topics	
s4_term1_topics	
s4_term2_topics	
s4_term3_topics	
	
Edit E

*/
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('secondary_subjects');
    }
}
