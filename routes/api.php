<?php

use App\Http\Controllers\ApiAuthController;
use App\Http\Controllers\ApiMainController;
use App\Http\Controllers\QuickSearchController;
use App\Http\Middleware\JwtMiddleware;
use App\Models\AcademicClass;
use App\Models\AcademicClassSctream;
use App\Models\Book;
use App\Models\DirectMessage;
use App\Models\SecondaryReportCard;
use App\Models\StudentHasClass;
use App\Models\Subject;
use App\Models\TermlyReportCard;
use App\Models\User;
use App\Models\Utils;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::POST("users/register", [ApiAuthController::class, "register"]);
Route::POST("users/login", [ApiAuthController::class, "login"]);

Route::middleware([JwtMiddleware::class])->group(function () {
    Route::get("student-verification", [ApiMainController::class, 'student_verification']);
    Route::get("service-subscriptions", [ApiMainController::class, 'service_subscriptions']);
    Route::post("service-subscriptions", [ApiMainController::class, 'service_subscriptions_store']);
    Route::get("services", [ApiMainController::class, 'services']);
    Route::get("posts", [ApiMainController::class, 'posts']);
    Route::get("post-views", [ApiMainController::class, 'post_views']);
    Route::post("post-views", [ApiMainController::class, 'post_view_create']);
    Route::post("password-change", [ApiMainController::class, 'password_change']);
    Route::post("update-profile", [ApiMainController::class, 'update_profile']);
    Route::get("exams", [ApiMainController::class, 'exams_list']);
    Route::post("marks", [ApiMainController::class, 'mark_submit']);
    Route::get("users/me", [ApiAuthController::class, 'me']);
    Route::get("manifest", [ApiMainController::class, 'manifest']);
    Route::get("my-classes", [ApiMainController::class, 'classes']);
    Route::get("class-streams", [ApiMainController::class, 'streams']);
    Route::post("update-bio/{id}", [ApiMainController::class, 'update_bio']);
    Route::post("verify-student/{id}", [ApiMainController::class, 'verify_student']);
    Route::post("update-guardian/{id}", [ApiMainController::class, 'update_guardian']);
    Route::post("session-create", [ApiMainController::class, 'session_create']);
    Route::get("my-subjects", [ApiMainController::class, 'my_subjects']);
    Route::get("student-has-class", [ApiMainController::class, 'student_has_class']);
    Route::get("transactions", [ApiMainController::class, 'transactions']);
    Route::post("transactions", [ApiMainController::class, 'transactions_post']);
    Route::post("accounts-change-balance", [ApiMainController::class, 'accounts_change_balance']);
    Route::post("accounts-change-status", [ApiMainController::class, 'accounts_change_status']);
    Route::get("my-sessions", [ApiMainController::class, 'my_sessions']);
    Route::get("my-students", [ApiMainController::class, 'get_my_students']);
    Route::post("post-media-upload", [ApiMainController::class, 'upload_media']);

    //=====ATTENDANCE========//
    Route::get("participants", [ApiMainController::class, 'participants']);
    Route::get("student-report-cards", [ApiMainController::class, 'student_report_cards']);
    Route::get("disciplinary-records", [ApiMainController::class, 'disciplinary_records']);
    /* ====== END OF ATTENDANCE ====== */
});



Route::get('git', function (Request $r) {
    die("done");
    //$resp = shell_exec('git pull --rebase=interactive -s recursive -X theirs');
    //$resp = shell_exec('git commit --romina');
    // $resp = shell_exec('cd public_html/ && git pull');
    $resp = exec('PWD');


    echo "=========START=========";
    echo "<pre>";
    print_r($resp);
    echo "</pre>";
    echo "=========END=========";
});
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('streams', function (Request $r) {
    $id = ((int)($r->get('q')));
    $enterprise_id = $r->get('enterprise_id');

    $c = AcademicClassSctream::where([
        'enterprise_id' => $enterprise_id,
        'academic_class_id' => $id,
    ])->limit(100)->get();

    $data = [];
    foreach ($c as $key => $v) {
        $data[] = [
            'id' => $v->id . "",
            'text' => $v->name
        ];
    }
    return [
        'data' => $data
    ];
});

Route::get('classes', function (Request $r) {
    $academic_year_id = ((int)($r->get('q')));
    $enterprise_id = $r->get('enterprise_id');

    $c = AcademicClass::where([
        'academic_year_id' => $academic_year_id,
        'enterprise_id' => $enterprise_id,
    ])->limit(100)->get();

    $data = [];
    foreach ($c as $key => $v) {
        $data[] = [
            'id' => $v->id . "",
            'text' => $v->name
        ];
    }
    return [
        'data' => $data
    ];
});

Route::get('ajax-users', function (Request $r) {
    $q = trim($r->get('q'));
    $enterprise_id = $r->get('enterprise_id');
    $user_type = $r->get('user_type');
    $status = $r->get('status');
    $conditions['enterprise_id'] =  $enterprise_id;
    if ($user_type != null) {
        $conditions['user_type'] =  $user_type;
    }
    if ($status != null) {
        $conditions['status'] =  $status;
    }

    $c = Administrator::where($conditions)
        ->where('name', 'like', "%$q%")
        ->limit(100)->get();

    $data = [];
    $surfix = "";
    foreach ($c as $key => $v) {
        if ($user_type == 'student') {
            if ($v->current_class != null) {
                $surfix = " - " . $v->current_class->name_text;
            }
        }
        $data[] = [
            'id' => $v->id . "",
            'text' => "#{$v->id} - " . $v->name . $surfix
        ];
    }
    return [
        'data' => $data
    ];
});


Route::get('promotion-to-class', function (Request $r) {
    $from_class = AcademicClass::find((int)($r->get('q')));
    $enterprise_id = $r->get('enterprise_id');

    $academic_year_id = 0;
    if ($from_class != null) {
        $academic_year_id = $from_class->academic_year_id;
    }

    $classes = AcademicClass::where(
        'enterprise_id',
        '=',
        $enterprise_id,
    )->where(
        'academic_year_id',
        '!=',
        $academic_year_id
    )->limit(100)->get();

    $data = [];
    foreach ($classes as $key => $v) {
        $data[] = [
            'id' => $v->id . "",
            'text' => $v->name_text . ""
        ];
    }
    return [
        'data' => $data
    ];
});


Route::get('class-subject', function (Request $r) {
    $clasess = Subject::where([
        'academic_class_id' =>  (int)($r->get('q')),
        'enterprise_id' =>  (int)($r->get('enterprise_id')),
    ])->get();



    $data = [];
    foreach ($clasess as $key => $v) {
        $data[] = [
            'id' => $v->id . "",
            'text' => $v->subject_name . ""
        ];
    }
    return [
        'data' => $data
    ];
});




Route::get('promotion-termly-report-cards', function (Request $r) {
    $from_class = AcademicClass::find((int)($r->get('q')));
    $enterprise_id = $r->get('enterprise_id');

    $academic_year_id = 0;
    if ($from_class != null) {
        $academic_year_id = $from_class->academic_year_id;
    }

    $report_cards = TermlyReportCard::where(
        'enterprise_id',
        '=',
        $enterprise_id,
    )->where(
        'academic_year_id',
        '!=',
        $academic_year_id
    )->limit(100)->get();

    $data = [];
    foreach ($report_cards as $key => $v) {
        $data[] = [
            'id' => $v->id . "",
            'text' => $v->report_title . ""
        ];
    }
    return [
        'data' => $data
    ];
});

Route::get('ajax', function (Request $r) {

    $_model = trim($r->get('model'));
    $conditions = [];
    foreach ($_GET as $key => $v) {
        if (substr($key, 0, 6) != 'query_') {
            continue;
        }
        $_key = str_replace('query_', "", $key);
        $conditions[$_key] = $v;
    }

    if (strlen($_model) < 2) {
        return [
            'data' => []
        ];
    }

    $model = "App\Models\\" . $_model;
    $search_by_1 = trim($r->get('search_by_1'));
    $search_by_2 = trim($r->get('search_by_2'));

    $q = trim($r->get('q'));
    $enterprise_id = ((int)($r->get('enterprise_id')));
    if (strlen($q) < 1) {
        return [
            'data' => []
        ];
    }
    $res_1 = $model::where(
        $search_by_1,
        'like',
        "%$q%"
    )
        ->where([
            'enterprise_id' => $enterprise_id
        ])
        ->where($conditions)
        ->limit(20)->get();
    $res_2 = [];

    if ((count($res_1) < 20) && (strlen($search_by_2) > 1)) {
        $res_2 = $model::where(
            $search_by_2,
            'like',
            "%$q%"
        )
            ->where([
                'enterprise_id' => $enterprise_id
            ])
            ->where($conditions)
            ->limit(20)->get();
    }

    $data = [];
    foreach ($res_1 as $key => $v) {
        $name = "";
        if (isset($v->name)) {
            $name = " - " . $v->name;
        }
        $data[] = [
            'id' => $v->id,
            'text' => "#$v->id" . $name
        ];
    }
    foreach ($res_2 as $key => $v) {
        $name = "";
        if (isset($v->name)) {
            $name = " - " . $v->name;
        }
        $data[] = [
            'id' => $v->id,
            'text' => "#$v->id" . $name
        ];
    }

    return [
        'data' => $data
    ];
});

Route::get('message-sender', function (Request $r) {
    Utils::send_messages();
});
Route::get('reconcile', function (Request $r) {
    Utils::reconcile($r);
    Utils::schoool_pay_sync();
});
Route::get('school-pay-reconcile', function (Request $r) {
    Utils::schoool_pay_sync();
});

Route::get('process-balance', function (Request $r) {
    $start = microtime(true);
    foreach (Administrator::where([
        'status' => 1,
        'user_type' => 'student'
    ])->get() as $key => $value) {
        if ($value->account == null) {
            continue;
        }
        $value->account->processBalance();
    }



    $end = microtime(true);

    // Calculate the time difference
    $executionTime = $end - $start;

    // Convert execution time to minutes and seconds
    $minutes = floor($executionTime / 60);
    $seconds = $executionTime % 60;

    echo "<hr>Execution Time: {$minutes} minutes and {$seconds} seconds<br>";
});

Route::get('books', function (Request $r) {
    $q = $r->get('q');
    $enterprise_id = $r->get('enterprise_id');

    $c = Book::where('title', 'like', "%$q%")
        ->where([
            'enterprise_id' => $enterprise_id
        ])
        ->limit(100)->get();

    $data = [];
    foreach ($c as $key => $v) {
        $data[] = [
            'id' => $v->id,
            'text' => $v->title
        ];
    }

    return [
        'data' => $data
    ];
});



Route::get('report-cards', function (Request $r) {


    $q = trim($r->get('q'));
    $enterprise_id = ((int)($r->get('enterprise_id')));
    if (strlen($q) < 1) {
        return [
            'data' => []
        ];
    }

    $res_1 = User::where(
        'name',
        'like',
        "%$q%"
    )
        ->where([
            'enterprise_id' => $enterprise_id
        ])
        ->limit(50)->get();

    $data = [];
    foreach ($res_1 as $key => $v) {
        $name = "";
        if (isset($v->name)) {
            $name = " - " . $v->name;
        }

        foreach ($v->report_cards as  $report) {
            $data[] = [
                'id' => $report->id,
                'text' => "#$report->id " . $name . " - {$report->termly_report_card->report_title}"
            ];
        }
    }


    return [
        'data' => $data
    ];
});

Route::get("studentsFinancialAccounts", [QuickSearchController::class, 'studentsFinancialAccounts']);


Route::get('select-student-has-class', function (Request $r) {
    $q = trim($r->get('q'));
    $hasClasses = StudentHasClass::where([
        'administrator_id' => ((int)($q))
    ])->get();
    $data = [];
    foreach ($hasClasses as $key => $v) {
        $data[] = [
            'id' => $v->id,
            'text' => $v->class->name_text
        ];
    }
    return [
        'data' => $data
    ];
});

Route::get('select-secondary-report-cards', function (Request $r) {

    $cards = SecondaryReportCard::toDropdownArray($r->enterprise_id);


    $q = trim($r->get('q'));
    $hasClasses = StudentHasClass::where([
        'administrator_id' => ((int)($q))
    ])->get();
    $data = [];
    foreach ($hasClasses as $key => $v) {
        $data[] = [
            'id' => $v->id,
            'text' => $v->class->name_text
        ];
    }
    return [
        'data' => $data
    ];
});
