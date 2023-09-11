<?php

use App\Models\Enterprise;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBulkMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bulk_messages', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Enterprise::class);
            $table->text('message_title')->nullable();
            $table->text('message_body')->nullable();
            $table->enum('message_delivery_type', ['Sheduled', 'Now'])->default('Now')->nullable();
            $table->dateTime('message_delivery_time')->nullable();
            $table->enum('send_action', ['Send', 'Draft'])->default('Draft')->nullable();
            $table->enum('send_confirm', ['Yes', 'No'])->default('No')->nullable();
            $table->enum('clone_action', ['Duplicate', 'Dont Duplicate'])->default('Dont Duplicate')->nullable();
            $table->enum('clone_confirm', ['Yes', 'No'])->default('No')->nullable();
            $table->enum('target_types', ['Individuals', 'To Teachers', 'To Parents'])->nullable();
            $table->text('target_individuals_phone_numbers')->nullable();
            $table->text('target_teachers_ids')->nullable();
            $table->enum('target_parents_condition_type', ['Fees Balance', 'Specific Parents'])->nullable();
            $table->text('target_parents_condition_phone_numbers')->nullable();
            $table->enum('target_parents_condition_fees_type', ['Less Than', 'Equal To'])->nullable();
            $table->enum('target_parents_condition_fees_status', ['Only Verified', 'All'])->nullable();
            $table->integer('target_parents_condition_fees_amount')->default(0)->nullable();
        });
    }
    /* 
	
	To Teachers 
	To Parents of Students
o	Purpose
	Fees balance
•	Verification
o	All
o	
•	Condition 
o	Balance equal to
o	Balance less than
o	Balance greater than
	General Communication

*/
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bulk_messages');
    }
}
