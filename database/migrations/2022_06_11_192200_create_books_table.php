<?php

use App\Models\BooksCategory;
use App\Models\Enterprise;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBooksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Enterprise::class);
            $table->foreignIdFor(BooksCategory::class);
            $table->text('api_id')->nullable();
            $table->text('title')->nullable();
            $table->text('subtitle')->nullable();
            $table->text('author_id')->nullable();
            $table->text('published_date')->nullable();
            $table->text('description')->nullable();
            $table->text('isbn')->nullable();
            $table->integer('page_count')->nullable();
            $table->text('thumbnail')->nullable();
            $table->text('language')->nullable();
            $table->integer('price')->nullable();
            $table->integer('quantity')->nullable();
            $table->text('pdf')->nullable();
        });
    }
 
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('books');
    }
}
