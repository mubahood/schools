<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddSourcedByAgentToAdminUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('admin_users', function (Blueprint $table) {
            if (Schema::hasColumn('admin_users', 'student_sourced_by_agent')) {
            $table->dropColumn('student_sourced_by_agent');
            }
            if (Schema::hasColumn('admin_users', 'student_sourced_by_agent_id')) {
            $table->dropColumn('student_sourced_by_agent_id');
            }
            if (Schema::hasColumn('admin_users', 'student_sourced_by_agent_commission')) {
            $table->dropColumn('student_sourced_by_agent_commission');
            }
            if (Schema::hasColumn('admin_users', 'student_sourced_by_agent_commission_paid')) {
            $table->dropColumn('student_sourced_by_agent_commission_paid');
            }
        }); 
    }

    /** 
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('admin_users', function (Blueprint $table) {
            $table->dropColumn([
                'student_sourced_by_agent',
                'student_sourced_by_agent_id',
                'student_sourced_by_agent_commission',
                'student_sourced_by_agent_commission_paid'
            ]);
        });
        Schema::table('admin_users', function (Blueprint $table) {
            //
        });
    }
}
