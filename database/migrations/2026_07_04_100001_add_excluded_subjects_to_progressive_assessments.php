<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('progressive_assessments', function (Blueprint $table) {
            $table->text('excluded_subjects')->nullable()->after('classes');
            $table->string('delete_excluded_records')->default('No')->after('excluded_subjects');
        });
    }

    public function down(): void
    {
        Schema::table('progressive_assessments', function (Blueprint $table) {
            $table->dropColumn(['excluded_subjects', 'delete_excluded_records']);
        });
    }
};
