<?php

use App\Models\Enterprise;
use App\Models\Term;
use App\Models\TermlyReportCard;
use App\Models\TheologyClass;
use App\Models\TheologyStream;
use App\Models\TheologySubject;
use App\Models\TheologyTermlyReportCard;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTheologyMarkRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('theology_mark_records', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Enterprise::class)->nullable();
            $table->foreignIdFor(TheologyTermlyReportCard::class)->nullable();
            $table->foreignIdFor(Term::class)->nullable();
            $table->foreignIdFor(Administrator::class)->nullable();
            $table->foreignIdFor(TheologyClass::class)->nullable();
            $table->foreignIdFor(TheologyStream::class)->nullable();
            $table->foreignIdFor(TheologySubject::class)->nullable();
            $table->integer('bot_score')->default(null)->nullable();
            $table->integer('mot_score')->default(null)->nullable();
            $table->integer('eot_score')->default(null)->nullable();
            $table->string('bot_is_submitted')->default('No')->nullable();
            $table->string('mot_is_submitted')->default('No')->nullable();
            $table->string('eot_is_submitted')->default('No')->nullable();
            $table->string('bot_missed')->default('Yes')->nullable();
            $table->string('mot_missed')->default('Yes')->nullable();
            $table->string('eot_missed')->default('Yes')->nullable();
            $table->string('initials')->default(null)->nullable();
            $table->text('remarks')->default(null)->nullable();
            $table->integer('total_score')->default(0)->nullable();
            $table->integer('total_score_display')->default(0)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('theology_mark_records');
    }
}
