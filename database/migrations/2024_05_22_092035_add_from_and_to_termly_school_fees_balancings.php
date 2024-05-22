<?php

use App\Models\Term;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFromAndToTermlySchoolFeesBalancings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('termly_school_fees_balancings', function (Blueprint $table) {
            $table->foreignIdFor(Term::class, 'from_term_id')->nullable();
            $table->foreignIdFor(Term::class, 'to_term_id')->nullable();
            $table->string('updated_existed_balances')->default('No');
            $table->string('target_students_status')->default('Active'); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('termly_school_fees_balancings', function (Blueprint $table) {
            //
        });
    }
}
