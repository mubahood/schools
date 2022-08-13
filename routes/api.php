<?php

use App\Models\AcademicClass;
use App\Models\AcademicClassSctream;
use App\Models\Book;
use App\Models\Utils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('git', function (Request $r) {
    $resp = shell_exec('git config pull');

    echo "<pre>";
    print_r($resp);

    die("this is done here! Test new !!!!!!!");
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

Route::get('ajax', function (Request $r) {

    $_model = trim($r->get('model'));

    if (strlen($_model) < 2) {
        return [
            'data' => []
        ];;
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
    $enterprise_id = ((int)($r->get('enterprise_id')));
    Utils::reconcile($enterprise_id);
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
