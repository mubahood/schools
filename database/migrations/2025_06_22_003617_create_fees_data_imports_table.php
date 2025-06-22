<?php

use App\Models\Enterprise;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeesDataImportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fees_data_imports', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Enterprise::class);
            $table->foreignIdFor(User::class, 'created_by_id')->nullable();
            $table->text('title')->nullable();
            $table->string('identify_by')->nullable();
            $table->string('school_pay_column')->nullable();
            $table->string('reg_number_column')->nullable();
            $table->string('services_columns')->nullable();
            $table->string('current_balance_column')->nullable();
            $table->string('previous_fees_term_balance_column')->nullable();
            $table->string('status')->default('Pending'); // pending, processing, completed, failed
            $table->text('summary')->nullable();
            $table->text('file_path')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fees_data_imports');
    }
}
