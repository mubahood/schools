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
use App\Models\FinancialRecord;
use App\Models\Gen;
use App\Models\StudentHasClass;
use App\Models\StudentHasFee;
use App\Models\Subject;
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
