<?php

use App\Models\AcademicClass;
use App\Models\AcademicClassSctream;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
