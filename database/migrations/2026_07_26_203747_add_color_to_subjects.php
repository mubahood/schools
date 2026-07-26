<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddColorToSubjects extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('subjects', 'color')) {
            Schema::table('subjects', function (Blueprint $table) {
                $table->string('color', 20)->nullable()->after('subject_name');
            });
        }

        // Seed default colors based on subject name keywords
        $map = [
            '#3a86ff' => ['math'],
            '#8338ec' => ['english', 'language', 'lit'],
            '#2b9348' => ['science', 'biology', 'chemistry', 'physics', 'integrated sci'],
            '#fb8500' => ['social', 'sst', 'history', 'geography', 'civics'],
            '#e63946' => ['religio', 'cre', 'ire', 'christian', 'islam'],
            '#06d6a0' => ['kiswahili', 'swahili'],
            '#f72585' => ['art', 'craft', 'creative', 'drawing'],
            '#f4a261' => ['physical', 'sport', 'health', 'p.e', 'pe '],
            '#1b4332' => ['agricult'],
            '#0096c7' => ['ict', 'computer', 'technolog', 'digital'],
            '#6a0572' => ['music'],
            '#c77c00' => ['luganda', 'local', 'vernacular', 'mother tongue'],
        ];

        // Use raw DB to bypass model boot hooks
        DB::table('subjects')->whereNull('color')->orderBy('id')->each(function ($s) use ($map) {
            $n = strtolower($s->subject_name ?? '');
            if (!$n) return;
            foreach ($map as $color => $keywords) {
                foreach ($keywords as $kw) {
                    if (str_contains($n, $kw)) {
                        DB::table('subjects')->where('id', $s->id)->update(['color' => $color]);
                        return;
                    }
                }
            }
        });
    }

    public function down()
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropColumn('color');
        });
    }
}
