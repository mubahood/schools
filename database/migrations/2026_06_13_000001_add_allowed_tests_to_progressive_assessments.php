<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAllowedTestsToProgressiveAssessments extends Migration
{
    public function up()
    {
        Schema::table('progressive_assessments', function (Blueprint $table) {
            // JSON array of test slot numbers open for teacher entry, e.g. [1,3,5].
            // NULL / empty = all tests up to number_of_tests are open.
            $table->text('allowed_tests')->nullable()->after('can_submit_tests');
        });
    }

    public function down()
    {
        Schema::table('progressive_assessments', function (Blueprint $table) {
            $table->dropColumn('allowed_tests');
        });
    }
}
