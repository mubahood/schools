<?php

use App\Http\Controllers\Controller;
use App\Http\Controllers\DummyDataController;
use App\Http\Controllers\MainController;
use App\Http\Controllers\PrintController2;
use App\Http\Controllers\ReportCardsPrintingController;
use App\Models\AcademicClass;
use App\Models\AcademicClassFee;
use App\Models\Account;
use App\Models\BatchServiceSubscription;
use App\Models\Book;
use App\Models\BooksCategory;
use App\Models\Course;
use App\Models\Enterprise;
use App\Models\Exam;
use App\Models\FinancialRecord;
use App\Models\Gen;
use App\Models\IdentificationCard;
use App\Models\Mark;
use App\Models\MarkRecord;
use App\Models\ReportFinanceModel;
use App\Models\ReportsFinance;
use App\Models\SchemWorkItem;
use App\Models\SchoolPayTransaction;
use App\Models\ServiceSubscription;
use App\Models\TheologyMarkRecord;
use App\Models\StudentHasClass;
use App\Models\StudentHasFee;
use App\Models\StudentReportCard;
use App\Models\Subject;
use App\Models\Term;
use App\Models\TermlyReportCard;
use App\Models\TheologyMark;
use App\Models\TheologyTermlyReportCard;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Utils;
use Encore\Admin\Auth\Database\Administrator;
use Facade\FlareClient\Report;
use Faker\Core\Uuid;
use Illuminate\Support\Facades\Route;
use Mockery\Matcher\Subset;
use Faker\Factory as Faker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;


Route::get('assessment-sheets-generate', [ReportCardsPrintingController::class, 'assessment_sheets_generate']);
Route::get('report-card-printings', [ReportCardsPrintingController::class, 'index']);
Route::get('data-import', [ReportCardsPrintingController::class, 'data_import']);
Route::get('process-termly-school-fees-balancings', [MainController::class, 'process_termly_school_fees_balancings']);

Route::get('test', function (Request $request) {

  $marks = TheologyMarkRecord::where([
    'enterprise_id' => 7,
  ])->get();

/*   foreach ($marks as $v) {
    $v->bot_score = rand(20, 100);
    $v->mot_score = rand(20, 100);
    $v->eot_score = rand(20, 100);
    echo $v->bot_score . ", " . $v->mot_score . ", " . $v->eot_score . "<br>";
    $v->save();
  }

  die("done"); */ 

  $rep = TheologyTermlyReportCard::find(14);
  $rep->reports_generate = 'Yes';
  $rep->report_title .= '1';
  $rep->save();
  dd($rep->report_title);
  die("done");

  //$rep->
});
/* 
  #attributes: array:26 [▶
    "id" => 55326
    "created_at" => "2024-03-25 00:02:02"
    "updated_at" => "2024-05-02 21:28:41"
    "enterprise_id" => 7
    "termly_report_card_id" => 16
    "term_id" => 40
    "administrator_id" => 12926
    "academic_class_id" => 129
    "academic_class_sctream_id" => 90
    "main_course_id" => 1
    "subject_id" => 1069
    "bot_score" => 0
    "mot_score" => 96
    "eot_score" => 97
    "bot_is_submitted" => "No"
    "mot_is_submitted" => "Yes"
    "eot_is_submitted" => "Yes"
    "bot_missed" => "Yes"
    "mot_missed" => "Yes"
    "eot_missed" => "Yes"
    "initials" => "KD"
    "remarks" => "Excellent"
    "total_score" => 97
    "total_score_display" => 97
    "aggr_name" => "D1"
    "aggr_value" => 1
*/
Route::get('process-batch-service-subscriptions', function (Request $request) {
  $rep = BatchServiceSubscription::find($request->id);
  if ($rep == null) return "Report not found";
  if ($rep->is_processed == 'Yes') return "Already processed";

  $total = count($rep->administrators);
  $success = 0;
  $fail = 0;
  $total_count = 0;
  $fail_text = "";
  foreach ($rep->administrators as $key => $admin) {
    $total_count++;
    $user = User::find($admin);
    if ($user == null) {
      $fail++;
      $fail_text .= "User not found: " . $admin . "<br>";
      continue;
    }

    //existing subscription
    $sub = ServiceSubscription::where([
      'service_id' => $rep->service_id,
      'administrator_id' => $user->id,
      'due_term_id' => $rep->due_term_id,
    ])->first();

    if ($sub != null) {
      $fail++;
      $fail_text .= "User already subscribed: " . $user->name . ", ref: " . $sub->id . "<br>";
      echo 'Skipped: ' . $user->name . " because already subscribed<br>";
      continue;
    }
    $sub = new ServiceSubscription();
    $sub->service_id = $rep->service_id;
    $sub->enterprise_id = $rep->enterprise_id;

    $sub->quantity = $rep->quantity;
    $sub->due_term_id = $rep->due_term_id;
    $sub->administrator_id = $user->id;
    $sub->due_academic_year_id = $rep->due_academic_year_id;
    $sub->link_with = $rep->link_with;
    $sub->transport_route_id = $rep->transport_route_id;
    $sub->trip_type = $rep->trip_type;
    $error_text = null;
    try {
      $sub->save();
      echo 'SUCCESS: ' . $user->name . "<br>";
    } catch (\Exception $e) {
      $error_text = $e->getMessage();
      throw $e;
    }
    if ($error_text == null) {
      $success++;
    } else {
      $fail++;
      $fail_text .= "Error: " . $error_text . "<br>";
    }
  }
  $rep->success_count = $success;
  $rep->fail_count = $fail;
  $rep->total_count = $total_count;
  $rep->is_processed = 'Yes';
  $rep->processed_notes = $fail_text;
  $rep->save();
});
Route::get('gen-code', function () {
  $data = '1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZabcefghijklmnopqrstuvwxyz';
  $code_type = 'qr';
});

Route::get('identification-cards-generation', function () {
  $idCard = IdentificationCard::find($_GET['id']);
  $pdf = App::make('dompdf.wrapper');
  $ent = Enterprise::find($idCard->enterprise_id);
  /* return view('id_cards.id_cards', [
    'idCard' => $idCard,
    'ent' => $ent,
    'users' => $idCard->get_users(),
  ]); */
  $pdf->loadHTML(view('id_cards.id_cards', [
    'idCard' => $idCard,
    'ent' => $ent,
    'users' => $idCard->get_users(),
  ]));

  $pdf->render();
  $output = $pdf->output();
  $store_file_path = public_path('storage/files/' . $idCard->id . '.pdf');
  file_put_contents($store_file_path, $output);
  $idCard->pdf_generated = 'Yes';
  $idCard->file_link = $store_file_path;
  $idCard->save();
  //redirect to the print
  $rand = rand(1, 100000) . time();
  return redirect('identification-cards-print?id=' . $idCard->id . '&rand=' . $rand);
});
Route::get('identification-cards-print', function () {
  $idCard = IdentificationCard::find($_GET['id']);
  if ($idCard->pdf_generated == 'No') {
    return "PDF not generated yet";
  }
  //file_link is the path to the generated pdf
  if (!file_exists($idCard->file_link)) {
    return "PDF not found";
  }
  return response()->file($idCard->file_link);
});

Route::match(['get', 'post'], '/print', [PrintController2::class, 'index']);
Route::match(['get', 'post'], '/fixed-asset-print', [PrintController2::class, 'fixed_asset_prints']);
Route::match(['get', 'post'], '/report-cards', [PrintController2::class, 'secondary_report_cards']);
Route::match(['get', 'post'], '/secondary-report-cards-print', [PrintController2::class, 'secondary_report_cards']);

ini_set('memory_limit', '-1');
set_time_limit(-1);


//$old->termly_report_card_id
$i = 0;
ini_set('memory_limit', '-1');



/* Route::get('/demo', function () {
  set_time_limit(-1);
  ini_set('memory_limit', '-1');
  $ent_id = 10;
  DummyDataController::account_parents($ent_id);
  DummyDataController::accounts($ent_id);
  DummyDataController::budget_and_expenses($ent_id);
  DummyDataController::fees_billing($ent_id);
  DummyDataController::transactions($ent_id);
}); */


Route::get('/gen', function () {
  die(Gen::find($_GET['id'])->do_get());
})->name("gen");
Route::get('/gen-form', function () {
  die(Gen::find($_GET['id'])->make_forms());
})->name("gen-form");


Route::get('create-streams', [Utils::class, 'create_streams']);
Route::get('generate-variables', [MainController::class, 'generate_variables']);
Route::get('process-photos', [MainController::class, 'process_photos']);
Route::get('student-data-import', [MainController::class, 'student_data_import']);
Route::get('prepare-things', [Utils::class, 'prepare_things']);
Route::get('generate-report-card', function () {
  $rep = StudentReportCard::find($_GET['id']);
  $rep->download_self();
  $url = url('storage/files/' . $rep->pdf_url);
  header('Location: ' . $url);
  die();
});
Route::get('generate-report-cards', function () {

  $temlyReport = TermlyReportCard::find($_GET['id']);
  TermlyReportCard::do_reports_generate($temlyReport);
  die('done');
  $temlyReport->reports_generate = 'Yes';
  $temlyReport->save();
  dd('done');
  $i = 0;
  $reps = StudentReportCard::where(
    [
      'termly_report_card_id' => $_GET['id']
    ]
  )->get();
  foreach ($reps as $key => $rep) {
    if ($rep->owner == null) continue;
    $i++;
    $rep->download_self();
    $url = url('storage/files/' . $rep->pdf_url);
    echo $i . ". " . $rep->owner->name . ", <a href='$url' target='_blank'>Download</a><br>";
  }
  die('DONE!');
});

Route::get('generate-report-cards-pdf', function () {

  $i = 0;
  $reps = StudentReportCard::where(
    [
      'termly_report_card_id' => $_GET['id']
    ]
  )->get();
  foreach ($reps as $key => $rep) {
    if ($rep->owner == null) continue;
    $i++;
    $rep->download_self();
    $url = url('storage/files/' . $rep->pdf_url);
    echo $i . ". " . $rep->owner->name . ", <a href='$url' target='_blank'>Download</a><br>";
  }
  die('DONE!');
});

Route::get('reports-finance-process', function (Request $request) {
  $rep = ReportFinanceModel::find($request->id);
  if ($rep == null) return "Report not found";
  ReportFinanceModel::process($rep);
});
Route::get('reports-finance-create', function () {
  foreach (Term::all() as $key => $term) {
    $r = ReportFinanceModel::where([
      'term_id' => $term->id
    ])->first();
    if ($r == null) {
      $r = new ReportFinanceModel();
      $r->term_id = $term->id;
      $r->enterprise_id = $term->enterprise_id;
      $r->save();
    }
  }
});


Route::get('scheme-of-work-print', function (Request $request) {
  $sub = Subject::find($request->id);
  if ($sub == null) return "Subject not found";
  //active term
  $active = Term::where([
    'enterprise_id' => $sub->enterprise_id,
    'is_active' => 1
  ])->first();
  if ($active == null) return "Active term not found";
  $items = SchemWorkItem::where([
    'subject_id' => $sub->id,
    'term_id' => $active->id
  ])->get();
  $pdf = App::make('dompdf.wrapper');
  $class = AcademicClass::find($sub->academic_class_id);
  $pdf->loadHTML(view('print.scheme-of-work-print', [
    'term' => $active,
    'ent' => $active->enterprise,
    'sub' => $sub,
    'class' => $class,
    'isPrint' => true,
    'items' => $items
  ]));
  return $pdf->stream();
});


Route::get('reports-finance-print', function (Request $request) {
  //return view('print/print-admission-letter');
  $pdf = App::make('dompdf.wrapper');
  //$pdf->setOption(['DOMPDF_ENABLE_REMOTE' => false]);

  $r = ReportFinanceModel::find($request->id);
  if ($r == null) return "Report not found";
  $ent = Enterprise::find($r->enterprise_id);
  $pdf->loadHTML(view('reports.finance', [
    'r' => new ReportsFinance($ent)
  ]));
  return $pdf->stream();
});


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
Route::get('import-transaction', function (Request $request) {
  $schoo_pay = SchoolPayTransaction::find($request->trans_id);
  if ($schoo_pay == null) return "Transaction not found";
  //if already imported
  $trans = Transaction::where([
    'school_pay_transporter_id' => $schoo_pay->school_pay_transporter_id
  ])->first();
  if ($trans != null) {
    $schoo_pay->status = 'Imported';
    $schoo_pay->save();
    $style = 'background-color: red; color: white; padding: 10px;';
    return "<h1 style='$style'>Transaction already imported.</h1>";
  }
  try {
    $schoo_pay->doImport();
  } catch (\Exception $e) {
    $msg = $e->getMessage();
    $style = 'background-color: red; color: white; padding: 10px;';
    return "<h1 style='$style'>$msg</h1>";
  }

  $style = 'background-color: green; color: white; padding: 10px;';
  return "<h1 style='$style'>Transaction imported successfully</h1>";
});
