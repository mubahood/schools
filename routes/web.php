<?php

use App\Http\Controllers\Controller;
use App\Http\Controllers\MainController;
use App\Http\Controllers\PrintController2;
use App\Models\AcademicClass;
use App\Models\Book;
use App\Models\BooksCategory;
use App\Models\Course;
use App\Models\StudentHasClass;
use App\Models\Subject;
use App\Models\Utils;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Support\Facades\Route;
use Mockery\Matcher\Subset;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\App;


Route::match(['get', 'post'], '/print', [PrintController2::class, 'index']);
Route::match(['get', 'post'], '/report-cards', [PrintController2::class, 'secondary_report_cards']);


Route::get('generate-variables', [MainController::class, 'generate_variables']);
Route::get('process-photos', [MainController::class, 'process_photos']);
Route::get('student-data-import', [MainController::class, 'student_data_import']);

Route::get('print-admission-letter', function () {
  //return view('print/print-admission-letter');
  $pdf = App::make('dompdf.wrapper');
  //$pdf->setOption(['DOMPDF_ENABLE_REMOTE' => false]);

  $pdf->loadHTML(view('print/print-admission-letter'));
  return $pdf->stream();
});
Route::get('print-receipt', function () {
  $pdf = App::make('dompdf.wrapper');
  $pdf->loadHTML(view('print/print-receipt'));
  return $pdf->stream();
});
