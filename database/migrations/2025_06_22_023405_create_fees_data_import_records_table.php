<?php

use App\Models\Enterprise;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeesDataImportRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fees_data_import_records', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Enterprise::class);
            $table->foreignIdFor(\App\Models\FeesDataImport::class, 'fees_data_import_id');
            $table->string('index')->nullable();
            $table->string('identify_by')->nullable();
            $table->string('reg_number')->nullable();
            $table->string('udpated_balance')->nullable();
            $table->string('school_pay')->nullable();
            $table->string('current_balance')->nullable();
            $table->string('previous_fees_term_balance')->nullable();
            $table->string('status')->default('Pending'); // pending, processing, completed, failed
            $table->text('summary')->nullable();
            $table->text('error_message')->nullable(); // For storing any error messages during import
            $table->text('data')->nullable(); // For storing the raw data of the record
            $table->text('services_data')->nullable(); // For storing services data if applicable

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fees_data_import_records');
    }
}
