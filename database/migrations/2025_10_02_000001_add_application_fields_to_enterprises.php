<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddApplicationFieldsToEnterprises extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('enterprises', function (Blueprint $table) {
            // Application System Toggle
            $table->enum('accepts_online_applications', ['Yes', 'No'])
                  ->default('No')
                  ->nullable()
                  ->after('sec_color')
                  ->comment('Enable/disable online student applications');
            
            // Application Fee
            $table->decimal('application_fee', 10, 2)
                  ->default(0.00)
                  ->nullable()
                  ->after('accepts_online_applications')
                  ->comment('Fee charged for online applications');
            
            // Application Instructions (Rich Text)
            $table->text('application_instructions')
                  ->nullable()
                  ->after('application_fee')
                  ->comment('Instructions shown to applicants');
            
            // Required Documents (JSON Array)
            $table->json('required_application_documents')
                  ->nullable()
                  ->after('application_instructions')
                  ->comment('JSON array of required documents with specifications');
            
            // Application Deadline
            $table->date('application_deadline')
                  ->nullable()
                  ->after('required_application_documents')
                  ->comment('Deadline for submitting applications');
            
            // Application Status Message (for closed applications)
            $table->text('application_status_message')
                  ->nullable()
                  ->after('application_deadline')
                  ->comment('Message shown when applications are closed');
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
            $table->dropColumn([
                'accepts_online_applications',
                'application_fee',
                'application_instructions',
                'required_application_documents',
                'application_deadline',
                'application_status_message'
            ]);
        });
    }
}
