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

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
/*
Route::get('/', function () {

  $clases = AcademicClass::where([
    'enterprise_id' => 1
  ])->get();


  $f = Faker::create();

  $i = 1;

  for ($x=0; $x < 1000; $x++) {

  foreach ($clases as $key => $cl) {
    $i++;
    $sex = ['Male', 'Female'];
    $religion = ['Christian', 'Muslim'];
    $u = new Administrator();
    $u->username = 'student' . $i . "@gmail.com";
    $u->email = $u->username;
    $u->password = password_hash('4321', PASSWORD_DEFAULT);
    $u->avatar = 'no_image.jpg';
    $u->enterprise_id = 1;
    $u->first_name = $f->name(1);
    $u->emergency_person_name = $f->name(1);
    $u->father_name = $f->name(1);
    $u->mother_name = $f->name(1);
    $u->father_phone = $f->phoneNumber();
    $u->mother_phone = $f->phoneNumber();
    $u->emergency_person_phone = $f->phoneNumber();
    $u->phone_number_1 = $f->phoneNumber;
    $u->last_name = $u->first_name;
    $u->name = $u->first_name . " " . $u->last_name;
    $u->date_of_birth = '1994-08-14';
    $u->place_of_birth = 'Bwera, Kasese';
    $u->home_address = 'Bwera, Kasese';
    $u->current_address = 'Bwera, Kasese';
    $u->nationality = 'Ugandan';
    $u->national_id_number = '1210128991231';
    $u->user_type = 'student';
    shuffle($religion);
    $u->religion = $religion[0];
    shuffle($sex);
    $u->sex = $sex[0];
    $u->save();


    $has_class = new StudentHasClass();
    $has_class->enterprise_id = $u->enterprise_id;
    $has_class->academic_class_id = $cl->id;
    $has_class->administrator_id = $u->id;
    $has_class->academic_year_id = 1;
    $has_class->save();

    echo $cl->id . " === " . $u->phone_number_1 . "<hr>";
  }

  }



  die("<hr>romina");
  return view('welcome');
});
 */

Route::match(['get', 'post'], '/print', [PrintController2::class, 'index']);
Route::get('generate-variables', [MainController::class, 'generate_variables']);
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
