<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSchemeWorkCurriculumColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('schem_work_items', function (Blueprint $table) {
            $table->text('theme')->nullable()->after('period');
            $table->text('sub_topic')->nullable()->after('topic');
            $table->text('content')->nullable()->after('sub_topic');
            $table->text('competence_subject')->nullable()->after('content');
            $table->text('competence_language')->nullable()->after('competence_subject');
            $table->text('life_skills_values')->nullable()->after('skills');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('schem_work_items', function (Blueprint $table) {
            $table->dropColumn([
                'theme',
                'sub_topic',
                'content',
                'competence_subject',
                'competence_language',
                'life_skills_values',
            ]);
        });
    }
}
