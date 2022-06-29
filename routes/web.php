<?php

use App\Models\AcademicClass;
use App\Models\Book;
use App\Models\BooksCategory;
use App\Models\Course;
use App\Models\Subject;
use Illuminate\Support\Facades\Route;
use Mockery\Matcher\Subset;

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

/* Route::get('/', function () {

  $clases = AcademicClass::where([
    'enterprise_id' => 1
  ])->get();

  $courses = Course::where([
    'enterprise_id' => 1
  ])->get();


  foreach ($clases as $key => $clas) {
    foreach ($courses as $cou) {
      $sub = Subject::where([
        'academic_class_id' => $clas->id,
        'course_id' => $cou->id,
      ])->first();
      if ($sub == null) {
        $s = new Subject();
        $s->enterprise_id = $cou->enterprise_id;
        $s->subject_name = $cou->name;
        $s->course_id = $cou->id;
        $s->academic_class_id = $clas->id;
        $s->subject_teacher = 1;
        $s->code = 'U'.rand(100,1000);
        $s->details = '';
        $s->save();
        continue;
      } 
	
	 
      echo "<hr>" . $clas->name;
    }
  }

  die("<hr>romina");
  return view('welcome');
});
 */