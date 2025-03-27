<?php

use App\Models\AcademicClass;
use App\Models\BulkPhotoUpload;
use App\Models\Enterprise;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBulkPhotoUploadItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bulk_photo_upload_items', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Enterprise::class)->nullable();
            $table->foreignIdFor(AcademicClass::class)->nullable();
            $table->foreignIdFor(BulkPhotoUpload::class)->nullable();
            $table->foreignIdFor(User::class, 'student_id')->nullable();
            $table->text('new_image_path')->nullable();
            $table->text('old_image_path')->nullable();
            $table->string('status')->default('Pending');
            $table->string('error_message')->nullable();
            $table->string('naming_type')->nullable();
            $table->string('file_name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bulk_photo_upload_items');
    }
}
