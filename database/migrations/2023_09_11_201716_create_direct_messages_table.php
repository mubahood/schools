<?php

use App\Models\BulkMessage;
use App\Models\Enterprise;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDirectMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('direct_messages', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Enterprise::class);
            $table->foreignIdFor(BulkMessage::class); 
            $table->foreignIdFor(Administrator::class);
            $table->string('receiver_number')->nullable();
            $table->text('message_body')->nullable();
            $table->enum('status', ['Pending', 'Sent', 'Failed'])->default('Pending')->nullable();
            $table->enum('is_scheduled', ['No', 'Yes'])->default('No')->nullable();
            $table->dateTime('delivery_time')->nullable();
            $table->text('error_message_message')->nullable();
            $table->text('response')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('direct_messages');
    }
}
