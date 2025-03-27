<?php

use App\Models\AcademicClass;
use App\Models\Enterprise;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBulkPhotoUploadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bulk_photo_uploads', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Enterprise::class)->nullable();
            $table->foreignIdFor(AcademicClass::class)->nullable();
            $table->text('file_path')->nullable();
            $table->text('file_name')->nullable();
            $table->string('naming_type')->nullable();
            $table->string('status')->default('Pending');
            $table->string('error_message')->nullable();
            $table->integer('total_images')->default(0);
            $table->integer('success_images')->default(0);
            $table->integer('failed_images')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bulk_photo_uploads');
    }
}
