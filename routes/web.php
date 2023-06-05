<?php

use App\Http\Controllers\Controller;
use App\Http\Controllers\MainController;
use App\Http\Controllers\PrintController2;
use App\Models\AcademicClass;
use App\Models\Account;
use App\Models\Book;
use App\Models\BooksCategory;
use App\Models\Course;
use App\Models\FinancialRecord;
use App\Models\Gen;
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

Route::get('/gen', function () {
  set_time_limit(-1);
  ini_set('memory_limit', '-1');
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
