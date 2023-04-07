<?php

use App\Http\Controllers\ApiAuthController;
use App\Http\Controllers\ApiMainController;
use App\Models\AcademicClass;
use App\Models\AcademicClassSctream;
use App\Models\Book;
use App\Models\Subject;
use App\Models\TermlyReportCard;
use App\Models\User;
use App\Models\Utils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::POST("users/register", [ApiAuthController::class, "register"]);
Route::POST("users/login", [ApiAuthController::class, "login"]);

Route::group(['middleware' => 'api'], function ($router) {


    Route::get("student-verification", [ApiMainController::class, 'student_verification']);

    Route::get("exams", [ApiMainController::class, 'exams_list']);
    Route::post("marks", [ApiMainController::class, 'mark_submit']);

    Route::get("users/me", [ApiAuthController::class, 'me']);
    Route::get("my-classes", [ApiMainController::class, 'classes']);
    Route::get("class-streams", [ApiMainController::class, 'streams']);
    Route::post("update-bio/{id}", [ApiMainController::class, 'update_bio']);
    Route::post("verify-student/{id}", [ApiMainController::class, 'verify_student']);
    Route::post("update-guardian/{id}", [ApiMainController::class, 'update_guardian']);
    Route::post("session-create", [ApiMainController::class, 'session_create']);
    Route::get("my-subjects", [ApiMainController::class, 'my_subjects']);
    Route::get("transactions", [ApiMainController::class, 'transactions']);
    Route::get("my-sessions", [ApiMainController::class, 'my_sessions']);
    Route::get("my-students", [ApiMainController::class, 'get_my_students']);
    Route::post("post-media-upload", [ApiMainController::class, 'upload_media']);
});



Route::get('git', function (Request $r) {
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

Route::get('reconcile', function (Request $r) {
    Utils::reconcile($r);
    Utils::schoool_pay_sync();
});
Route::get('school-pay-reconcile', function (Request $r) {
    Utils::schoool_pay_sync();
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
