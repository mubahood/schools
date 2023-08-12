<?php

use App\Http\Controllers\Controller;
use App\Http\Controllers\DummyDataController;
use App\Http\Controllers\MainController;
use App\Http\Controllers\PrintController2;
use App\Models\AcademicClass;
use App\Models\AcademicClassFee;
use App\Models\Account;
use App\Models\Book;
use App\Models\BooksCategory;
use App\Models\Course;
use App\Models\Enterprise;
use App\Models\Exam;
use App\Models\FinancialRecord;
use App\Models\Gen;
use App\Models\Mark;
use App\Models\MarkRecord;
use App\Models\StudentHasClass;
use App\Models\StudentHasFee;
use App\Models\Subject;
use App\Models\Term;
use App\Models\TermlyReportCard;
use App\Models\Transaction;
use App\Models\Utils;
use Encore\Admin\Auth\Database\Administrator;
use Faker\Core\Uuid;
use Illuminate\Support\Facades\Route;
use Mockery\Matcher\Subset;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

Route::match(['get', 'post'], '/print', [PrintController2::class, 'index']);
Route::match(['get', 'post'], '/report-cards', [PrintController2::class, 'secondary_report_cards']);
Route::get('/temps', function () {

  ini_set('memory_limit', '-1');
  set_time_limit(-1);


  $i = 0;
  $marks = Mark::where([
    'transfered' => 'No',
    'enterprise_id' => 13,
  ])->get();  
  foreach ($marks as $mark) {
    $exam = $mark->exam;
    if ($exam == null) {
      die("Exam not found");
    }
    //if ($mark->student == null) continue;
    $mark_record = MarkRecord::where([
      'term_id' => $exam->term_id,
      'subject_id' => $mark->subject_id,
      'administrator_id' => $mark->student_id,
    ])->first();

    if ($mark_record == null) {
      die("Mark record not found");
      $mark_record = new MarkRecord();
      $mark_record->term_id = $exam->term_id;
      $mark_record->enterprise_id = $exam->enterprise_id;
      $mark_record->subject_id = $mark->subject_id;
      $mark_record->administrator_id = $mark->student_id;
      $mark_record->academic_class_id = $mark->class_id;
      $mark_record->main_course_id = $mark->student->main_course_id;
    }

    $termly_report_card = TermlyReportCard::where([
      'term_id' => $exam->term_id,
    ])->first();

    if ($termly_report_card == null) {
      $termly_report_card = new TermlyReportCard();
      $termly_report_card->enterprise_id = $exam->enterprise_id;
      $termly_report_card->term_id = $exam->term_id;
      $termly_report_card->grading_scale_id = 1;
      try {
        $termly_report_card->save();
      } catch (\Throwable $th) {
        echo "FAILED => " . $th->getMessage() . "<br>";
        die();
      }
    }

    $mark_record->termly_report_card_id = $termly_report_card->id;
    $mark_record->academic_class_sctream_id = $mark->student->stream_id;

    if ($exam->type == 'B.O.T') {
      $mark_record->bot_score = $mark->score;
      $mark_record->bot_missed = 'Yes';
      if ($mark->is_missed == 1) {
        $mark_record->bot_missed = 'No';
      }

      $mark_record->bot_is_submitted = 'No';
      if ($mark->is_submitted == 1) {
        $mark_record->bot_is_submitted = 'Yes';
      }
    } else if ($exam->type == 'M.O.T') {
      $mark_record->mot_score = $mark->score;
      $mark_record->mot_missed = 'Yes';
      if ($mark->is_missed == 1) {
        $mark_record->mot_missed = 'No';
      }

      $mark_record->mot_is_submitted = 'No';
      if ($mark->is_submitted == 1) {
        $mark_record->mot_is_submitted = 'Yes';
      }
    } else if ($exam->type == 'E.O.T') {
      $mark_record->eot_score = $mark->score;
      $mark_record->eot_missed = 'Yes';
      if ($mark->is_missed == 1) {
        $mark_record->eot_missed = 'No';
      }

      $mark_record->eot_is_submitted = 'No';
      if ($mark->is_submitted == 1) {
        $mark_record->eot_is_submitted = 'Yes';
      }
      $i++;
      try {
        die("SAVINGING");
        $mark_record->save();
        // $mark->transfered = 'Yes';
        // $mark->save();
        DB::update('update marks set transfered = "Yes" where id = ?', [$mark->id]);
        echo "$i. TRANSFERED " . $mark->id . " -> " . $mark->student->name . ' - ' . $mark->student->name . "<br>";
      } catch (\Throwable $th) {
        die("FAILED SAVINGING");
        echo "$i. FAILED => " . $th->getMessage() . $mark->id . " -> " . $mark->student->name . ' - ' . $mark->student->name . "<br>";
      }
    }
  }
  die("======DONE-1======");

  $termoly_report_cards = TermlyReportCard::where([])->get();
  $i = 0;
  foreach ($termoly_report_cards as $key => $ternly_report_card) {
    $exams = Exam::where([
      'term_id' => $ternly_report_card->term_id
    ])->get();
    if ($exams->count() == 0) continue;
    foreach ($exams as $exam) {
      $marks = Mark::where([
        'exam_id' => $exam->id,
        'transfered' => 'No'
      ])->get();
      foreach ($marks as $mark) {
        if ($mark->student == null) continue;
        $mark_record = MarkRecord::where([
          'term_id' => $ternly_report_card->term_id,
          'subject_id' => $mark->subject_id,
          'administrator_id' => $mark->student_id,
        ])->first();

        if ($mark_record == null) {
          $mark_record = new MarkRecord();
          $mark_record->term_id = $ternly_report_card->term_id;
          $mark_record->enterprise_id = $ternly_report_card->enterprise_id;
          $mark_record->subject_id = $mark->subject_id;
          $mark_record->administrator_id = $mark->student_id;
          $mark_record->academic_class_id = $mark->class_id;
          $mark_record->main_course_id = $mark->student->main_course_id;
        }
        $mark_record->termly_report_card_id = $ternly_report_card->id;
        $mark_record->academic_class_sctream_id = $mark->student->stream_id;

        if ($exam->type == 'B.O.T') {
          $mark_record->bot_score = $mark->score;
          $mark_record->bot_missed = 'Yes';
          if ($mark->is_missed == 1) {
            $mark_record->bot_missed = 'No';
          }

          $mark_record->bot_is_submitted = 'No';
          if ($mark->is_submitted == 1) {
            $mark_record->bot_is_submitted = 'Yes';
          }
        } else if ($exam->type == 'M.O.T') {
          $mark_record->mot_score = $mark->score;
          $mark_record->mot_missed = 'Yes';
          if ($mark->is_missed == 1) {
            $mark_record->mot_missed = 'No';
          }

          $mark_record->mot_is_submitted = 'No';
          if ($mark->is_submitted == 1) {
            $mark_record->mot_is_submitted = 'Yes';
          }
        } else if ($exam->type == 'E.O.T') {
          $mark_record->eot_score = $mark->score;
          $mark_record->eot_missed = 'Yes';
          if ($mark->is_missed == 1) {
            $mark_record->eot_missed = 'No';
          }

          $mark_record->eot_is_submitted = 'No';
          if ($mark->is_submitted == 1) {
            $mark_record->eot_is_submitted = 'Yes';
          }
          $i++;
          try {
            $mark_record->save();
            $mark->transfered = 'Yes';
            $mark->save();
            echo "$i. TRANSFERED " . $mark->id . " -> " . $mark->student->name . ' - ' . $mark->student->name . "<br>";
          } catch (\Throwable $th) {
            echo "$i. FAILED => " . $th->getMessage() . $mark->id . " -> " . $mark->student->name . ' - ' . $mark->student->name . "<br>";
          }
        }
      }
    }
  }
  die('done');
  /* 	 	
	
bot_score	
mot_score	
eot_score	
bot_is_submitted	
mot_is_submitted Descending 1	
eot_is_submitted	
bot_missed	
mot_missed	
eot_missed	
initials	
remarks	
total_score	
total_score_display	
 
*/  /* 
    "id" => 1
    "created_at" => "2022-10-22 05:22:49"
    "updated_at" => "2023-08-09 09:46:48"
    "enterprise_id" => 7
    "exam_id" => 1
    "" => 8
    "subject_id" => 29
    "student_id" => 2473
    "teacher_id" => 2997
    "score" => 84.0
    "remarks" => "V.Good"
    "is_submitted" => 1
    "is_missed" => 1
    "main_course_id" => 18
    "transfered" => "Yes"
  */
  dd("done");



  dd(count($marks));

  $terms = Term::where([])->get();
  /*   foreach ($terms as $key => $term) {
    echo $term->id . ". ".$term->enterprise->name." => " . $term->name . " ===> " . $term->mark_records->count() . "<br>";
  } */

  /*   $tr = TermlyReportCard::find(7);
  TermlyReportCard::generate_marks($tr);
  die('done'); */


  $termly_report_cards = TermlyReportCard::where([])->get();

  foreach ($termly_report_cards as $key => $value) {
    MarkRecord::where([
      'term_id' => $value->term_id
    ])->update([
      'termly_report_card_id' => $value->id
    ]);
    echo ($value->d . ". " . $value->name . " ===> " . $value->mark_records->count() . " <br>");
  }
  die("done");
  dd($termly_report_cards->count());

  $marks = Mark::where(['transfered' => 'No'])->orderBy('id', 'desc')->get();


  //$old->termly_report_card_id
  $i = 0;
  ini_set('memory_limit', '-1');
  set_time_limit(-1);
  foreach ($marks as $key => $old) {


    if ($old->exam == null) {
      throw new Exception("Exam not found", 1);
      continue;
    }
    $i++;






    $new = MarkRecord::where([
      'term_id' => $old->exam->term_id,
      'subject_id' => $old->subject_id,
      'administrator_id' => $old->student_id,
    ])->first();


    $s = Administrator::find($old->student_id);

    if ($old->student == null) {
      echo ("===> Student not found <=======" . $old->student_id . "<br>");
      $old->delete();
      continue;
    }

    $msg = " FOR => {$old->id} {$old->student->name} <== <br>";
    if ($new == null) {
      echo "{$i} NEW {$msg}  ";

      $new = new MarkRecord();
      $new->updated_at = $old->updated_at;
      $new->created_at = $old->created_at;
      $new->enterprise_id = $old->enterprise_id;
      $new->termly_report_card_id = 1;
      if ($old->subject == null) {
        $old->delete();
        echo ("Subject not found" . $old->subject_id . "<br>");
        continue;
      }

      $new->term_id = $old->exam->term_id;
      $new->administrator_id = $old->student_id;
      $new->academic_class_id = $old->class_id;
      $new->academic_class_sctream_id = null;
      $new->main_course_id = $old->main_course_id;
      $new->subject_id = $old->subject_id;
      $new->initials = '-';
    } else {
      continue;
      echo "{$i}. OLD {$msg}";
    }

    if ($old->subject->teacher != null) {
      $new->initials = substr($old->subject->teacher->first_name, 0, 1);
      if (strlen($old->subject->teacher->last_name) > 2) {
        $new->initials .= substr($old->subject->teacher->last_name, 0, 1);
      }
    }

    if ($old->exam->type == 'B.O.T') {
      $new->bot_score = $old->score;
      $new->bot_missed = 'Yes';
      if ($old->is_missed == 1) {
        $new->bot_missed = 'No';
      }

      $new->bot_is_submitted = 'No';
      if ($old->is_submitted == 1) {
        $new->bot_is_submitted = 'Yes';
      }
    } else if ($old->exam->type == 'M.O.T') {
      $new->mot_score = $old->score;
      $new->mot_missed = 'Yes';
      if ($old->is_missed == 1) {
        $new->mot_missed = 'No';
      }

      $new->mot_is_submitted = 'No';
      if ($old->is_submitted == 1) {
        $new->mot_is_submitted = 'Yes';
      }
    } else if ($old->exam->type == 'E.O.T') {
      $new->eot_score = $old->score;
      $new->eot_missed = 'Yes';
      if ($old->is_missed == 1) {
        $new->eot_missed = 'No';
      }

      $new->eot_is_submitted = 'No';
      if ($old->is_submitted == 1) {
        $new->eot_is_submitted = 'Yes';
      }
    }

    try {
      $new->save();
    } catch (\Throwable $th) {
      echo $th->getMessage();
      continue;
    }
    $old->transfered = 'Yes';
    $old->save();
  }

  die("done");


  die("simple temp.");
  $sql = "SELECT * FROM `transactions` WHERE  `description` LIKE '%Tuition Fees Term 2%' ORDER BY `id` DESC";
  $trans = DB::select($sql);
  $i = 0;
  $done = [];
  $dups = [];
  foreach ($trans as $key => $tra) {
    $i++;
    if (in_array($tra->account_id, $done)) {
      $acc = Account::find($tra->account_id);
      if ($acc == null) {
        die("Acc not found");
      }

      Transaction::where([
        'id' => $tra->id
      ])->delete();
      $acc->status = 0;
      $acc->save();
      $dups[] = $tra->account_id;
      continue;
    }
    $done[] = $tra->account_id;
    //echo $i . ". " . $tra->id . " - " . $tra->amount . " - " . $tra->description . " ==> $tra->account_id <br>";
  }

  $i = 0;
  foreach ($dups as $key => $value) {
    $acc = Account::find($value);
    if ($acc == null) {
      die("null");
    }
    $i++;
    $clas = AcademicClass::where([
      'id' => $acc->owner->current_class_id
    ])->first();
    $classss = "";
    if ($clas != null) {
      $classss = $clas->short_name;
    }

    echo $i . ". " . $acc->owner->name . " - " . $classss . "<br>";
  }
  die('done');
  /* 
1. Raudha Namagembe Kabanda - P.2
2. Rayhana Najjemba - B.C
3. Hassan Adinan Wasswa - P.6
4. Mukiibi Abdul Malik - B.C
5. Raham Tabingwa Rashid - P.2
*/
  dd($dups);
  die("romina");

  die("done");
  $i = 0;
  foreach (Administrator::where([
    'user_type' => 'Student',
    'enterprise_id' => 7,
    'status' => 1,
  ])->get() as $key => $stud) {
    $acc = $stud->getAccount();
    $trans = Transaction::where(
      'description',
      'like',
      '%Fees Term 2%'
    )
      ->where([
        'account_id' => $acc->id,
      ])
      ->get();
    if ($trans->count() < 1) {
      continue;
    }


    Transaction::where(
      'description',
      'like',
      '%Tuition Fees Term 2%'
    )
      ->where([
        'account_id' => $acc->id,
        'term_id' => 8
      ])
      ->delete();
    $i++;
    $stud->status = 2;
    $stud->save();

    $hasFee = StudentHasFee::where([
      'administrator_id' => $stud->id,
      'academic_class_id' => $stud->current_class_id,
    ])->delete();
    echo $stud->id . ". " . $trans->count() . "<br>";
  }
  die("done");

  $fees = AcademicClassFee::where([
    'enterprise_id' => 7,
    'due_term_id' => 8
  ])->get();
  foreach ($fees as $key => $fee) {
    $hasFee = StudentHasFee::where([
      'academic_class_id' => $fee->id
    ])->get();
    if ($hasFee->count() > 0) {
      echo "DELETED => " . $hasFee->count() . " records for {$fee->id}<br>";
      $hasFee = StudentHasFee::where([
        'academic_class_id' => $fee->id
      ])->delete();
    } else {
      echo "FOUND => " . $hasFee->count() . " records for {$fee->id}<br>";
    }
  }
  $trans = Transaction::where(
    'description',
    'like',
    '%Tuition Fees Term 2%'
  )
    ->where([
      'enterprise_id' => 7,
    ])
    ->get();

  echo "DELETE => " . ($trans->count());
  Transaction::where(
    'description',
    'like',
    '%Tuition Fees Term 2%'
  )
    ->where([
      'enterprise_id' => 7,
    ])->delete();
  die("temp");
});
Route::get('/demo', function () {
  set_time_limit(-1);
  ini_set('memory_limit', '-1');
  $ent_id = 10;
  DummyDataController::account_parents($ent_id);
  DummyDataController::accounts($ent_id);
  DummyDataController::budget_and_expenses($ent_id);
  DummyDataController::fees_billing($ent_id);
  DummyDataController::transactions($ent_id);
});
/* 
enterprise_id
generate_teachers	
number_of_teachers	
temp	
create_courses	
courses_type	
create_academic_year	
create_term	
create_classes	
classes_type	
create_subjects	
create_grade_scale	
grade_scale_type	
generate_students	
number_of_students	
generate_marks	

*/
Route::get('/gen', function () {
  set_time_limit(-1);
  ini_set('memory_limit', '-1');
  $i = 0;
  $t =  Enterprise::find(7)->active_term();
  $hasClasses = [];
  $ads = Administrator::where([
    'user_type' => 'student',
    'enterprise_id' => 7
  ])
    ->where('status', '!=', 1)
    ->get();
  foreach ($ads as $key => $admin) {
    if ($admin->account == null) {
      continue;
    }
    $f = StudentHasFee::where([
      'administrator_id' => $admin->id,
    ])->orderBy('id', 'desc')->first();

    if ($f == null) {
      continue;
    }
    $class = AcademicClass::find($f->academic_class_id);
    if ($class == null) {
      continue;
    }
    foreach ($class->academic_class_fees as $key => $fee) {
      if ($fee->due_term_id != $t->id) {
        continue;
      }

      $f1 = StudentHasFee::where([
        'administrator_id' => $admin->id,
      ])->orderBy('id', 'desc')->first();

      if ($f1 == null) {
        continue;
      }
      $f1->delete();
      $trans = Transaction::where([
        'account_id' => $admin->account->id,
        'term_id' => $t->id
      ])
        ->where('amount', '<', -500000)
        ->get();
      foreach ($trans as $k) {
        $i++;
        echo $i . ". " . $admin->name . " - {$k->description} - $t->created_at <br>";
      }
    }


    /*  $tran = Transaction::where([
      'account_id' => $admin->account->id,
      'term_id' => $t->id,
      'type' => 'FEES_PAYMENT',
    ])
      ->where('amount', '>', 1)
      ->orderBy('id', 'desc')
      ->first();
    if ($tran == null) {
      $admin->status = 2;
      $admin->save();
      continue;
    } */
  }
  die('done');
  $i = 0;
  foreach (Transaction::where([
    'source' => NULL
  ])->get() as $key => $tra) {
    try {
      if (
        $tra->school_pay_transporter_id != null &&
        strlen($tra->school_pay_transporter_id > 5)
      ) {
        $tra->source = 'SCHOOL_PAY';
      }
    } catch (\Throwable $th) {
    }
    if (strlen($tra->source) < 3) {
      $tra->source = 'GENERATED';
    }
    $i++;
    echo $i . " DONE WITH => {$tra->id} - {$tra->amount} -> {$tra->source}<br>";
    $tra->save();
  }
  die("done");

  $types = [];
  $i = 0;
  foreach (Account::all() as $v) {
    if (in_array($v->type, [
      'EMPLOYEE_ACCOUNT',
      'CASH_ACCOUNT',
      'BANK_ACCOUNT',
      'supplier',
      'FEES_ACCOUNT',
      'parent'
    ])) {
      foreach ($v->transactions as $trans) {
        $i++;
        echo $i . " DELETED => {$trans->id} - {$trans->amount}<br>";
        $trans->delete();
      }
    }

    if (in_array($v->type, [
      'OTHER_ACCOUNT'
    ])) {
      foreach ($v->transactions as $trans) {
        $i++;
        echo $i . " {$v->type} => {$v->name} => TRANSFAERED => {$trans->id} : {$trans->description}<br>";

        $rec = new FinancialRecord();
        $rec->created_at = $v->created_at;
        $rec->updated_at = $trans->updated_at;
        $rec->enterprise_id = $trans->enterprise_id;
        $rec->account_id = $trans->account_id;
        $rec->academic_year_id = $trans->academic_year_id;
        $rec->term_id = $trans->term_id;
        $rec->created_by_id = $trans->created_by_id;
        $rec->amount = -1 * $trans->amount;
        $rec->description = $trans->description;
        $rec->payment_date = $trans->payment_date;
        $rec->type = 'EXPENDITURE';
        if ($rec->amount > 0) {
          $rec->amount = -1 * $rec->amount;
        }
        $rec->save();
        $trans->delete();
      }
    }
    continue;
  }
  /*     




  */
  /* 
      "id" => 9700
    "created_at" => "2022-11-13 08:28:22"
    "updated_at" => "2022-11-13 08:28:22"
    "enterprise_id" => 7
    "account_id" => 865
    "amount" => 350000
    "description" => "Cartridge refill"
    "academic_year_id" => 2
    "term_id" => 6
    "school_pay_transporter_id" => null
    "created_by_id" => 2985
    "is_contra_entry" => 0
    "type" => "other"
    "contra_entry_account_id" => 15
    "contra_entry_transaction_id" => 9701
    "payment_date" => "2022-10-19 00:00:00"
    "termly_school_fees_balancing_id" => null
  */
  die("done");
  die(Gen::find($_GET['id'])->do_get());
})->name("gen");


Route::get('create-streams', [Utils::class, 'create_streams']);
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
