<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lets a school-fees demand record that it was generated from parent commitment
 * records, and exactly which ones.
 *
 * `commitment_ids` is what makes the demand notice able to say WHEN each parent
 * committed: at print time the notice resolves the student's commitment from
 * this list rather than guessing at the most recent one.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('school_fees_demands', function (Blueprint $table) {
            if (!Schema::hasColumn('school_fees_demands', 'source')) {
                $table->string('source', 50)->nullable()->index();
            }
            if (!Schema::hasColumn('school_fees_demands', 'commitment_ids')) {
                $table->text('commitment_ids')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('school_fees_demands', function (Blueprint $table) {
            foreach (['source', 'commitment_ids'] as $col) {
                if (Schema::hasColumn('school_fees_demands', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
