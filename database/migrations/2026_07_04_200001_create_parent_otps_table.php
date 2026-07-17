<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateParentOtpsTable extends Migration
{
    public function up()
    {
        Schema::create('parent_otps', function (Blueprint $table) {
            $table->id();
            $table->string('phone_number')->unique();
            $table->string('otp_code');           // bcrypt hash of the 6-digit code
            $table->timestamp('expires_at');
            $table->boolean('is_used')->default(false);
            $table->timestamps();

            $table->index('phone_number');
        });
    }

    public function down()
    {
        Schema::dropIfExists('parent_otps');
    }
}
