<?php

use App\Models\AcademicYear;
use App\Models\Enterprise;
use App\Models\Term;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Enterprise::class);
            $table->foreignIdFor(AcademicYear::class);
            $table->foreignIdFor(Term::class);
            $table->foreignIdFor(User::class, 'posted_by_id');
            $table->text('title')->nullable();
            $table->text('description')->nullable();
            $table->text('photo')->nullable();
            $table->text('file')->nullable();
            $table->string('type')->nullable();
            $table->string('target')->nullable();
            $table->string('status')->default('Active');
            $table->dateTime('event_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('posts');
    }
}
