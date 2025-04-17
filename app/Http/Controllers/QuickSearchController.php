<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AcademicClass;
use App\Models\AcademicClassSctream;
use App\Models\Account;
use App\Models\Mark;
use App\Models\Participant;
use App\Models\Service;
use App\Models\ServiceSubscription;
use App\Models\Session;
use App\Models\StudentHasClass;
use App\Models\User;
use App\Models\Utils;
use App\Traits\ApiResponser;
use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Throwable;
use Tymon\JWTAuth\Facades\JWTAuth;

class QuickSearchController extends Controller
{

    use ApiResponser;


    public function studentsFinancialAccounts(Request $r)
    {
        $u_id = trim($r->get('user_id'));
        $s = trim($r->get('q'));
        $u = Administrator::find($u_id);
        if (($u == null) ||
            ($s == null) ||
            (strlen($s) < 2)
        ) {
            return [
                'data' => []
            ];
        }
        $data = [];
        $user_ids = User::where([
            'enterprise_id' => $u->enterprise_id,
            'user_type' => 'student',
        ])
            ->where('name', 'like', "%$s%")
            ->limit(20)
            ->orderBy('name', 'asc')
            ->pluck('id')
            ->toArray();

        foreach (
            Account::whereIn('administrator_id', $user_ids)
                ->limit(20)
                ->orderBy('name', 'asc')
                ->get() as $key => $val
        ) {
            $current_class_text = "";
            if ($val->owner != null) {

                if ($val->owner == null) {
                    continue;
                }
                if ($val->owner->status != 1) {
                    continue;
                }

                $user = $val->owner;
                $user->current_class_text = $user->current_class_id;
                $class = $user->getActiveClass();
                if ($class != null) {
                    $current_class_text = " - " . $class->short_name . " - (Bal: " . number_format($val->balance) . ")";
                }
            }
            $data[] = [
                'id' => $val->id,
                'text' => $val->name . $current_class_text,
            ];
        }
        return [
            'data' => $data
        ];
    }
}
