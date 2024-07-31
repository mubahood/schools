<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Manifest extends Model
{
    use HasFactory;
    //user




    public static function get_total_fees_balance($u)
    {
        $accounts = self::get_active_students_acounts_ids($u);
        return Account::whereIn('id', $accounts)->sum('balance');
    }

    public static function get_total_expected_service_fees($u)
    {
        //$accs = self::get_active_students_acounts_ids($u);
        $users = self::get_active_students_user_ids($u);
        $total = 0;
        $sql = "SELECT SUM(total) as total FROM service_subscriptions WHERE administrator_id IN (" . implode(",", $users) . ")";
        $res = DB::select($sql);
        $total = $res[0]->total;
        return $total;
    }

    //get_total_expected_tuition is sum of account balances of active students
    public static function get_total_expected_tuition($u)
    {
        $ent = $u->enterprise;
        if ($ent == null) {
            throw new \Exception("Enterprise not found");
        }
        $active_academic_year = $ent->active_academic_year();
        $active_term = $ent->active_term();
        $tot = 0;
        if ($active_term == null) {
            return $tot;
        }
        $classes = $active_academic_year->classes;
        $grand_total = 0;
        foreach ($classes as $class) {
            $total_bill = AcademicClassFee::where([
                'enterprise_id' => $u->enterprise_id,
                'due_term_id' => $active_term->id,
                'academic_class_id' => $class->id
            ])
                ->sum('amount');
            $active_students_count = User::where([
                'user_type' => 'student',
                'current_class_id' => $class->id,
                'status' => 1
            ])->count();
            $grand_total += ($total_bill * $active_students_count);
        }
        return $grand_total;
    }

    public static function get_active_students_user_ids($u)
    {
        $accounts = [];
        $ent = $u->enterprise;
        if ($ent == null) {
            throw new \Exception("Enterprise not found");
        }
        $sql = "SELECT admin_users.id FROM admin_users WHERE 
            admin_users.user_type = 'student' AND
            admin_users.status = 1 AND
            admin_users.enterprise_id = $ent->id 
            ";
        $res = DB::select($sql);
        foreach ($res as $key => $value) {
            $accounts[] = $value->id;
        }
        return $accounts;
    }

    public static function get_active_students_acounts_ids($u)
    {
        $accounts = [];
        $ent = $u->enterprise;
        if ($ent == null) {
            throw new \Exception("Enterprise not found");
        }
        $sql = "SELECT accounts.id FROM accounts
            ,admin_users WHERE 
            accounts.administrator_id = admin_users.id AND
            admin_users.enterprise_id = $ent->id AND
            admin_users.user_type = 'student' AND
            admin_users.status = 1
            ";
        $res = DB::select($sql);
        foreach ($res as $key => $value) {
            $accounts[] = $value->id;
        }
        return $accounts;
    }
}
