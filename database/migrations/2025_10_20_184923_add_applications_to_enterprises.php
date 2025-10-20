<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddApplicationsToEnterprises extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('enterprises', function (Blueprint $table) {
            if (!Schema::hasColumn('enterprises', 'req_doc_birth_certificate')) {
            $table->text('req_doc_birth_certificate')->nullable();
            }
            if (!Schema::hasColumn('enterprises', 'req_doc_previous_school_report')) {
            $table->text('req_doc_previous_school_report')->nullable();
            }
            if (!Schema::hasColumn('enterprises', 'req_doc_passport_photo')) {
            $table->text('req_doc_passport_photo')->nullable();
            }
            if (!Schema::hasColumn('enterprises', 'req_doc_parent_id')) {
            $table->text('req_doc_parent_id')->nullable();
            }
            if (!Schema::hasColumn('enterprises', 'req_doc_immunization')) {
            $table->text('req_doc_immunization')->nullable();
            }
            if (!Schema::hasColumn('enterprises', 'req_doc_recommendation')) {
            $table->text('req_doc_recommendation')->nullable();
            }
            if (!Schema::hasColumn('enterprises', 'req_doc_leaving_certificate')) {
            $table->text('req_doc_leaving_certificate')->nullable();
            }
            if (!Schema::hasColumn('enterprises', 'req_doc_medical_report')) {
            $table->text('req_doc_medical_report')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('enterprises', function (Blueprint $table) {
            //
        });
    }
}
