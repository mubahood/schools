<?php

use App\Http\Controllers\AttendanceDashboardController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\DummyDataController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\EmailVerificationController as LegacyEmailVerificationController;
use App\Http\Controllers\KnowledgeBaseController;
use App\Http\Controllers\MainController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\PrintController2;
use App\Http\Controllers\ReportCardsPrintingController;
use App\Http\Controllers\SitemapController;
use App\Models\AcademicClass;
use App\Models\AcademicClassFee;
use App\Models\Account;
use App\Models\BatchServiceSubscription;
use App\Models\Book;
use App\Models\BooksCategory;
use App\Models\BulkMessage;
use App\Models\BulkPhotoUpload;
use App\Models\BulkPhotoUploadItem;
use App\Models\Course;
use App\Models\DataExport;
use App\Models\DirectMessage;
use App\Models\Enterprise;
use App\Models\Exam;
use App\Models\FeesDataImport;
use App\Models\FeesDataImportRecord;
use App\Models\FinancialRecord;
use App\Models\Gen;
use App\Models\IdentificationCard;
use App\Models\ImportSchoolPayTransaction;
use App\Models\Mark;
use App\Models\MarkRecord;
use App\Models\ReportFinanceModel;
use App\Models\ReportsFinance;
use App\Models\SchemWorkItem;
use App\Models\SchoolFeesDemand;
use App\Models\SchoolPayTransaction;
use App\Models\SchoolReport;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceSubscription;
use App\Models\Session;
use App\Models\SessionReport;
use App\Models\StockBatch;
use App\Models\StockItemCategory;
use App\Models\TheologyMarkRecord;
use App\Models\StudentHasClass;
use App\Models\StudentHasFee;
use App\Models\StudentHasSemeter;
use App\Models\StudentHasTheologyClass;
use App\Models\StudentReportCard;
use App\Models\Subject;
use App\Models\Term;
use App\Models\TermlyReportCard;
use App\Models\TheologryStudentReportCard;
use App\Models\TheologyClass;
use App\Models\TheologyMark;
use App\Models\TheologyStream;
use App\Models\TheologyTermlyReportCard;
use App\Models\Transaction;
use App\Models\TransportSubscription;
use App\Models\UniversityProgramme;
use App\Models\User;
use App\Models\Utils;
use Carbon\Carbon;
use Dflydev\DotAccessData\Util;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Facades\Admin;
use Facade\FlareClient\Report;
use Faker\Core\Uuid;
use Illuminate\Support\Facades\Route;
use Mockery\Matcher\Subset;
use Faker\Factory as Faker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

Route::get('process-school-issues', function (Request $request) {
  $enterprise_id = $request->get('enterprise_id', null);
  
  // Check enterprise exists using raw query
  $ent = DB::selectOne("SELECT id, name FROM enterprises WHERE id = ?", [$enterprise_id]);
  if ($ent == null) {
    return "Enterprise not found";
  }

  // Get all active students with their payment codes in one query
  $active_students = DB::select("
    SELECT id, name, school_pay_payment_code
    FROM admin_users 
    WHERE enterprise_id = ? 
      AND user_type = 'student' 
      AND status = 1
  ", [$ent->id]);

  // Find duplicates using a single query
  $duplicates = DB::select("
    SELECT school_pay_payment_code, COUNT(*) as count, GROUP_CONCAT(CONCAT(name, ' (ID: ', id, ')') SEPARATOR '<br>- ') as students
    FROM admin_users
    WHERE enterprise_id = ?
      AND user_type = 'student'
      AND status = 1
      AND school_pay_payment_code IS NOT NULL
      AND LENGTH(school_pay_payment_code) >= 3
    GROUP BY school_pay_payment_code
    HAVING COUNT(*) > 1
  ", [$ent->id]);

  // Create lookup map for duplicates
  $duplicate_codes = [];
  foreach ($duplicates as $dup) {
    $duplicate_codes[$dup->school_pay_payment_code] = $dup->students;
  }

  $issue_count = 0;
  $updates = [
    'missing' => [],
    'duplicate' => [],
    'ok' => []
  ];

  // Process students and categorize
  foreach ($active_students as $student) {
    // Check for missing or short codes
    if ($student->school_pay_payment_code == null || strlen($student->school_pay_payment_code) < 3) {
      $issue_count++;
      $updates['missing'][] = $student->id;
      echo $issue_count . ". Missing School Pay Code: " . $student->name . " (ID: " . $student->id . ")<br>";
      continue;
    }

    // Check if this code is in duplicates
    if (isset($duplicate_codes[$student->school_pay_payment_code])) {
      $issue_count++;
      $message = "Duplicate School Pay Code ({$student->school_pay_payment_code}): {$student->name} (ID: {$student->id}) conflicts with:<br>- {$duplicate_codes[$student->school_pay_payment_code]}";
      $updates['duplicate'][$student->id] = $message;
      echo $issue_count . ". " . $message . "<br>";
      continue;
    }

    // No issues
    $updates['ok'][] = $student->id;
  }

  // Batch update using raw queries for performance
  if (!empty($updates['missing'])) {
    DB::update("
      UPDATE admin_users 
      SET masters_university_year_graduated = 'ISSUE',
          phd_university_name = 'Missing School Pay Code'
      WHERE id IN (" . implode(',', $updates['missing']) . ")
    ");
  }

  if (!empty($updates['duplicate'])) {
    foreach ($updates['duplicate'] as $id => $message) {
      DB::update("
        UPDATE admin_users 
        SET masters_university_year_graduated = 'ISSUE',
            phd_university_name = ?
        WHERE id = ?
      ", [$message, $id]);
    }
  }

  if (!empty($updates['ok'])) {
    DB::update("
      UPDATE admin_users 
      SET masters_university_year_graduated = 'OK',
          phd_university_name = NULL
      WHERE id IN (" . implode(',', $updates['ok']) . ")
    ");
  }

  die("Done processing school issues for enterprise: " . $ent->name); 
});

Route::get('attendance-report', function (Request $request) {
  $id = $request->get('id', null);
  $regenerate = $request->get('regenerate', 0); // Check if hard regenerate is requested

  $report = SessionReport::find($id);
  if ($report == null) {
    return "Report not found";
  }

  // If hard regenerate is requested, force regeneration
  if ($regenerate == 1) {
    try {
      // Reset PDF status to force regeneration
      $report->pdf_processed = 'No';
      $report->pdf_path = null;
      $report->save();

      // Process and generate new PDF
      $report->do_process();

      return redirect('/session-report-pdf/' . $report->id);
    } catch (\Exception $e) {
      return "Error regenerating report: " . $e->getMessage();
    }
  }

  // Normal generation: If PDF is already generated, redirect to view it
  if ($report->pdf_processed == 'Yes' && $report->pdf_path) {
    return redirect('/session-report-pdf/' . $report->id);
  }

  // Process report and generate PDF
  try {
    $report->do_process();
    return redirect('/session-report-pdf/' . $report->id);
  } catch (\Exception $e) {
    return "Error generating report: " . $e->getMessage();
  }
});

// Session Report PDF Download/View Route
Route::get('session-report-pdf/{id}', function ($id) {
  $report = SessionReport::find($id);

  if (!$report) {
    abort(404, 'Report not found');
  }

  // Check if user has access to this report
  $user = Auth::user();
  if ($user && $user->enterprise_id != $report->enterprise_id) {
    abort(403, 'Unauthorized access');
  }

  // If PDF exists, serve it
  if ($report->pdf_path && Storage::disk('public')->exists($report->pdf_path)) {
    $pdfPath = Storage::disk('public')->path($report->pdf_path);
    return response()->file($pdfPath, [
      'Content-Type' => 'application/pdf',
      'Content-Disposition' => 'inline; filename="' . basename($report->pdf_path) . '"'
    ]);
  }

  // If PDF doesn't exist, generate it
  try {
    if ($report->pdf_processed != 'Yes') {
      $report->do_process();
    } else {
      $report->generatePDF();
    }

    // Serve the newly generated PDF
    if ($report->pdf_path && Storage::disk('public')->exists($report->pdf_path)) {
      $pdfPath = Storage::disk('public')->path($report->pdf_path);
      return response()->file($pdfPath, [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => 'inline; filename="' . basename($report->pdf_path) . '"'
      ]);
    }

    return "PDF generation failed. No file was created.";
  } catch (\Exception $e) {
    return "Error generating PDF: " . $e->getMessage();
  }
})->name('session-report.pdf');
// Email Verification Routes (Public)
Route::middleware(['web'])->group(function () {
  Route::get('/email/verify', [EmailVerificationController::class, 'show'])
    ->name('verification.notice');

  Route::match(['GET', 'POST'], '/email/verification-notification', [EmailVerificationController::class, 'send'])
    ->name('verification.send');

  Route::get('/email/verify/{id}/{token}', [EmailVerificationController::class, 'verify'])
    ->name('verification.verify')
    ->where(['id' => '[0-9]+', 'token' => '[a-zA-Z0-9]+']);

  Route::get('/email/verification-check', [EmailVerificationController::class, 'check'])
    ->name('verification.check');

  Route::get('/email/verified', [EmailVerificationController::class, 'verified'])
    ->name('verification.verified');

  Route::match(['GET', 'POST'], '/email/resend', [EmailVerificationController::class, 'resend'])
    ->name('verification.resend');

  Route::get('/email/handle-verification', [EmailVerificationController::class, 'handleEmailVerification'])
    ->name('verification.handle');
});

// Student Application Routes (Public - No Authentication)
Route::prefix('apply')->name('apply.')->middleware(['web'])->group(function () {

  // Landing & Introduction
  Route::get('/', [\App\Http\Controllers\StudentApplicationController::class, 'landing'])
    ->name('landing');

  Route::match(['get', 'post'], '/start', [\App\Http\Controllers\StudentApplicationController::class, 'start'])
    ->name('start');

  // Step 1: School Selection
  Route::get('/school-selection', [\App\Http\Controllers\StudentApplicationController::class, 'schoolSelection'])
    ->name('school-selection')
    ->middleware('application.session');

  Route::post('/school-selection', [\App\Http\Controllers\StudentApplicationController::class, 'saveSchoolSelection'])
    ->name('school-selection.save')
    ->middleware('application.session');

  Route::post('/school-selection/confirm', [\App\Http\Controllers\StudentApplicationController::class, 'confirmSchool'])
    ->name('school-selection.confirm')
    ->middleware('application.session');

  // Step 2: Bio Data Form
  Route::get('/bio-data', [\App\Http\Controllers\StudentApplicationController::class, 'bioDataForm'])
    ->name('bio-data')
    ->middleware(['application.session', 'application.step:bio_data']);

  Route::post('/bio-data', [\App\Http\Controllers\StudentApplicationController::class, 'saveBioData'])
    ->name('bio-data.save')
    ->middleware('application.session');

  // Step 3: Confirmation & Review
  Route::get('/confirmation', [\App\Http\Controllers\StudentApplicationController::class, 'confirmationForm'])
    ->name('confirmation')
    ->middleware(['application.session', 'application.step:confirmation']);

  Route::post('/confirmation', [\App\Http\Controllers\StudentApplicationController::class, 'submitApplication'])
    ->name('submit')
    ->middleware('application.session');

  // Step 4: Document Upload
  Route::get('/documents', [\App\Http\Controllers\StudentApplicationController::class, 'documentsForm'])
    ->name('documents')
    ->middleware(['application.session', 'application.step:documents']);

  Route::post('/documents/upload', [\App\Http\Controllers\StudentApplicationController::class, 'uploadDocument'])
    ->name('documents.upload')
    ->middleware('application.session');

  Route::delete('/documents/{documentId}', [\App\Http\Controllers\StudentApplicationController::class, 'deleteDocument'])
    ->name('documents.delete')
    ->middleware('application.session');

  Route::post('/documents/complete', [\App\Http\Controllers\StudentApplicationController::class, 'completeDocuments'])
    ->name('documents.complete')
    ->middleware('application.session');

  // Success & Status
  Route::get('/success/{applicationNumber}', [\App\Http\Controllers\StudentApplicationController::class, 'success'])
    ->name('success');

  Route::match(['get', 'post'], '/status', [\App\Http\Controllers\StudentApplicationController::class, 'statusForm'])
    ->name('status.form');

  Route::post('/status/check', [\App\Http\Controllers\StudentApplicationController::class, 'checkStatus'])
    ->name('status.check');

  // Temporary Admission Letter Download
  Route::get('/admission-letter/{applicationNumber}', [\App\Http\Controllers\StudentApplicationController::class, 'downloadAdmissionLetter'])
    ->name('admission.letter');

  // AJAX Endpoints
  Route::post('/session/save', [\App\Http\Controllers\StudentApplicationController::class, 'saveSession'])
    ->name('session.save')
    ->middleware('application.session');

  Route::post('/session/heartbeat', [\App\Http\Controllers\StudentApplicationController::class, 'sessionHeartbeat'])
    ->name('session.heartbeat')
    ->middleware('application.session');

  // Resume Application
  Route::get('/resume/{sessionToken}', [\App\Http\Controllers\StudentApplicationController::class, 'resume'])
    ->name('resume');
});

Route::get('test-mail', function () {
  $student = Admin::user();
  if ($student == null) {
    return "Student not found";
  }
  $email = $student->email;
  //validate email $email
  if ($email == null || strlen($email) < 5) {
    throw new Exception("Email not found. $email");
  }

  //use filter
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    // throw new Exception("Email not valid. $email");
  }
  //mail to parent
  $mail_body =
    <<<EOD
        <p>Dear Parent of <b>$student->name</b>,</p>
        <p>Please find attached the report card for your child.</p>
        <p>Click on the link below to download the report card.</p>
        <p><a href="#">Download Report Card</a></p>
        <p>Best regards,</p>
        <p>Admin Team.</p>
        EOD;
  $data['body'] = $mail_body;
  $data['data'] = $data['body'];
  $data['name'] = $student->name;
  $data['email'] = $email;
  $data['subject'] = 'Report Card - ' . env('APP_NAME') . ' - ' . date('Y-m-d') . ".";

  try {
    Utils::mail_sender($data);
  } catch (\Throwable $th) {
    throw $th;
  }

  dd('test');
});

Route::get('preview-verification-email', function () {
  $user = Admin::user();
  if ($user == null) {
    return "Please log in first";
  }

  $verificationUrl = route('verification.verify', [
    'id' => $user->id,
    'token' => 'sample-token-for-preview',
    'hash' => sha1($user->email),
  ]);

  $controller = new \App\Http\Controllers\Auth\EmailVerificationController();
  $reflection = new \ReflectionClass($controller);
  $method = $reflection->getMethod('getVerificationEmailTemplate');
  $method->setAccessible(true);
  $emailContent = $method->invoke($controller, $user, $verificationUrl);

  return $emailContent;
});

Route::get('preview-password-reset-email', function () {
  $user = Admin::user();
  if ($user == null) {
    return "Please log in first";
  }

  $token = 'sample-token-for-preview';
  $resetUrl = route('public.reset-password', ['token' => $token]) . '?email=' . urlencode($user->email);

  $notification = new \App\Notifications\PasswordResetNotification($token);
  $reflection = new \ReflectionClass($notification);
  $method = $reflection->getMethod('getPasswordResetEmailTemplate');
  $method->setAccessible(true);
  $emailContent = $method->invoke($notification, $user, $resetUrl, \App\Models\Utils::app_name());

  return $emailContent;
});

Route::get('student-data-import-do-import', [MainController::class, 'student_data_import_do_import']);
Route::get('process-students-enrollment', [MainController::class, 'process_students_enrollment']);

// Redirect incorrect auth/login to correct auth/login
Route::get('auth/login', function () {
  return redirect('/auth/login');
});

// Enhanced Authentication Routes 
Route::group(['prefix' => config('admin.route.prefix', 'admin')], function () {
  $authController = 'App\Admin\Controllers\AuthController';

  // Basic auth routes (required)
  Route::get('auth/login', $authController . '@getLogin')->name('admin.login');
  Route::post('auth/login', $authController . '@postLogin');
  Route::get('auth/logout', $authController . '@getLogout')->name('admin.logout');

  // Additional enhanced routes
  Route::get('auth/forgot-password', $authController . '@getForgotPassword')->name('admin.forgot-password');
  Route::post('auth/forgot-password', $authController . '@postForgotPassword');

  // Reset password routes  
  Route::get('auth/reset-password/{token}', $authController . '@getResetPassword')->name('admin.reset-password');
  Route::post('auth/reset-password', $authController . '@postResetPassword');

  // Support routes
  Route::get('auth/support', $authController . '@getSupport')->name('admin.support');
  Route::post('auth/support', 'App\Http\Controllers\SupportController@submitSupportForm')->name('admin.support.submit');

  // CAPTCHA route
  Route::get('auth/captcha', 'App\Http\Controllers\SupportController@generateCaptcha')->name('admin.captcha');

  // Admin support management routes (require authentication)
  Route::middleware('admin')->group(function () {
    Route::get('support/messages', 'App\Http\Controllers\SupportController@adminIndex')->name('admin.support.index');
    Route::get('support/messages/{id}', 'App\Http\Controllers\SupportController@adminShow')->name('admin.support.show');
    Route::post('support/messages/{id}/reply', 'App\Http\Controllers\SupportController@adminReply')->name('admin.support.reply');
  });

  // Email verification route
  Route::get('auth/verify-email/{token}', $authController . '@verifyEmail')->name('admin.verify-email');

  // CSRF Token refresh route (for preventing page expiration)
  Route::get('csrf-token', function () {
    return response()->json(['token' => csrf_token()]);
  })->name('csrf.token');
});

// Public Authentication Routes (without admin middleware)
$authController = 'App\Admin\Controllers\AuthController';

Route::get('auth/login', $authController . '@getLogin')->name('public.login');
Route::post('auth/login', $authController . '@postLogin')->name('public.login.post');

Route::get('auth/forgot-password', $authController . '@getForgotPassword')->name('public.forgot-password');
Route::post('auth/forgot-password', $authController . '@postForgotPassword')->name('public.forgot-password.post');

Route::get('auth/reset-password/{token}', $authController . '@getResetPassword')->name('public.reset-password');
Route::post('auth/reset-password', $authController . '@postResetPassword')->name('public.reset-password.post');

Route::get('auth/support', $authController . '@getSupport')->name('public.support');
Route::post('auth/support', 'App\Http\Controllers\SupportController@submitSupportForm')->name('public.support.submit');

Route::get('auth/captcha', 'App\Http\Controllers\SupportController@generateCaptcha')->name('public.captcha');

// Public CSRF Token refresh route
Route::get('csrf-token', function () {
  return response()->json(['token' => csrf_token()]);
})->name('public.csrf.token');

Route::get('reset-marks', function (Request $request) {
  /*   $report = StudentReportCard::find(14505);
  TermlyReportCard::get_teachers_remarks($report);
  $report = StudentReportCard::find(14505);
  dd($report->class_teacher_comment); */
  /* 
      "class_teacher_comment" => "Najib Mugoba's academic achievements are exceptional and set a high standard for his peers."
    "head_teacher_comment" => null
    "class_teacher_commented" => 0
    "head_teacher_commented" => 0
  */
  return "This route is deprecated. Please use the new reset-marks route.";
  $term_id = 52;
  $affected_1 = 0;
  $affected_2 = 0;

  try {
    $affected_1 = TheologyMarkRecord::where('term_id', $term_id)
      ->update([
        'eot_score' => 0,
        'total_score_display' => 0,
        'eot_is_submitted' => 'No',
        'remarks' => '',
      ]);
  } catch (\Throwable $e) {
    echo "Error resetting theology marks: " . $e->getMessage() . "<br>";
  }

  try {
    $affected_2 = MarkRecord::where('term_id', $term_id)
      ->update([
        'eot_score' => 0,
        'total_score_display' => 0,
        'eot_is_submitted' => 'No',
        'remarks' => '',
      ]);
  } catch (\Throwable $e) {
    echo "Error resetting secular marks: " . $e->getMessage() . "<br>";
  }

  echo "$affected_1 theology, $affected_2 secular.";
  die('done');
});
Route::get('process-stock-records', function (Request $request) {



  $u = Admin::user();
  if ($u == null) {
    return "You are not logged in";
  }
  $enterprise = Enterprise::find($u->enterprise_id);
  if ($enterprise == null) {
    return "Enterprise not found";
  }

  $active_term = $enterprise->active_term();
  if ($active_term == null) {
    return "No active term found for this enterprise.";
  }


  //set unlimted time
  set_time_limit(-1);

  //sql that sets all stock_records to be archived if they are not from this term
  $sql = "UPDATE stock_records SET is_archived = 'Yes' WHERE enterprise_id = ? AND due_term_id != ?";
  $recs = DB::update($sql, [$u->enterprise_id, $active_term->id]);
  echo "Archived $recs stock records for enterprise: " . $enterprise->name . "<hr>";

  //set stock_batches is_archived be Yes if they are not from this term
  $sql = "UPDATE stock_batches SET is_archived = 'Yes' WHERE enterprise_id = ? AND term_id != ?";
  $recs = DB::update($sql, [$u->enterprise_id, $active_term->id]);
  echo "Archived $recs stock batches for enterprise: " . $enterprise->name . "<hr>";

  $stokCats = StockItemCategory::where('enterprise_id', $u->enterprise_id)
    ->orderBy('quantity', 'DESC')
    ->get();
  foreach ($stokCats as $key => $value) {
    StockItemCategory::update_category_quantity($value);
    $cat = StockItemCategory::find($value->id);
    echo "UPDATED: " . $cat->name . ", QUANTITY: " . number_format($cat->quantity) . "<br>";
  }

  return "Stock records and batches archived successfully for enterprise: " . $enterprise->name;
});

Route::get('bill-afresh', function (Request $request) {
  $ent_id = $request->get('ent_id', null);
  $ent = Enterprise::find($ent_id);
  if ($ent == null) {
    return "Enterprise not found";
  }

  $studentHasSemeters = StudentHasSemeter::where('enterprise_id', $ent->id)
    ->orderBy('id', 'desc')
    ->get();
  $min = 0;
  $max = 10;

  if ($request->has('min')) {
    $min = (int)$request->get('min');
  }

  if ($request->has('max')) {
    $max = (int)$request->get('max');
  }

  $i = -1;
  foreach ($studentHasSemeters as $key => $studentHasSemeter) {
    $i++;
    //if not in range continue
    if ($i < $min || $i > $max) {
      continue;
    }
    if ($studentHasSemeter->student == null) {
      echo "Student not found for student has semeter: " . $studentHasSemeter->id . "<br>";
      continue;
    }
    echo "<hr>";

    $BALANCE_CHANGED = false;
    $BAL_1 = $studentHasSemeter->student->balance;
    echo "BALANCE BEFORE BILLING: " . $studentHasSemeter->student->balance . "<br>";
    try {
      $studentHasSemeter->student->bill_university_students();
    } catch (\Exception $e) {
      echo "Error billing student: " . $studentHasSemeter->student->name . " for semeter: " . $studentHasSemeter->id . "<br>";
      continue;
    }
    $student = User::find($studentHasSemeter->student->id);
    $BAL_2 = $student->balance;
    if ($BAL_1 != $BAL_2) {
      $BALANCE_CHANGED = true;
    }
    echo "BALANCE AFTER... BILLING: " . $studentHasSemeter->student->balance . "<br>";
    if ($BALANCE_CHANGED) {
      echo "Balance changed for student: " . $studentHasSemeter->student->name . "<br>";
    } else {
      echo "Balance did not change for student: " . $studentHasSemeter->student->name . "<br>";
    }
  }
});
Route::get('reset-a-school', function (Request $request) {



  /* $recs = TheologyMarkRecord::where([
    'term_id' => 52, 
  ])->get();


  //set unlimited time
  set_time_limit(-1);
  foreach ($recs as $key => $value) {
    $value->mot_score = 0;
    $value->remarks = '';
    $value->mot_is_submitted = 'No';
    $value->save();
    echo "Updated record: " . $value->id . "<br>";
  }

  dd("Updated records: " . $recs);
  dd($recs);
  return; */
  $school_name = 'NEBBI SCHOOL OF HEALTH SCIENCES';
  $ent = Enterprise::where('name', $school_name)->first();
  if ($ent == null) {
    throw new \Exception("Enterprise not found: $school_name");
  }

  // deleteing has fees
  $StudentHasFeeTable = (new StudentHasFee())->getTable();
  $DELETED = DB::delete('DELETE FROM ' . $StudentHasFeeTable . ' WHERE enterprise_id = ?', [$ent->id]);
  echo "Deleted $DELETED records from $StudentHasFeeTable for enterprise: " . $ent->name . "<hr>";

  //deleting service subscriptions
  $ServiceSubscriptionTable = (new ServiceSubscription())->getTable();
  $DELETED = DB::delete('DELETE FROM ' . $ServiceSubscriptionTable . ' WHERE enterprise_id = ?', [$ent->id]);
  echo "Deleted $DELETED records from $ServiceSubscriptionTable for enterprise: " . $ent->name . "<hr>";

  //remove enrolments 
  $StudentHasSemeterTable = (new StudentHasSemeter())->getTable();
  $DELETED = DB::delete('DELETE FROM ' . $StudentHasSemeterTable . ' WHERE enterprise_id = ?', [$ent->id]);
  echo "Deleted $DELETED records from $StudentHasSemeterTable for enterprise: " . $ent->name . "<hr>";

  //set all students for this enrolment to have status of 2
  $usersTable = (new User())->getTable();
  $UPDATED = DB::update('UPDATE ' . $usersTable . ' SET is_enrolled = \'No\', status = 2 WHERE user_type = ? AND enterprise_id = ?', ['student', $ent->id]);
  echo "Updated $UPDATED STUDENT records in $usersTable for enterprise: " . $ent->name . "<hr>";

  //delete all transactions for this enterprise
  $transactionTable = (new Transaction())->getTable();
  $DELETED = DB::delete('DELETE FROM ' . $transactionTable . ' WHERE enterprise_id = ?', [$ent->id]);
  echo "Deleted $DELETED records from $transactionTable for enterprise: " . $ent->name . "<hr>";

  //delete bursary subscription
  $TransportSubscriptionTable = (new TransportSubscription())->getTable();
  $DELETED = DB::delete('DELETE FROM ' . $TransportSubscriptionTable . ' WHERE enterprise_id = ?', [$ent->id]);
  echo "Deleted $DELETED records from $TransportSubscriptionTable for enterprise: " . $ent->name . "<hr>";

  die('done');
});
Route::get('import-school-pay-transactions-do-import', function (Request $request) {
  $u = Admin::user();
  if ($u == null) {
    return "You are not logged in";
  }

  $ent = Enterprise::find($u->enterprise_id);
  if ($ent == null) {
    return "Enterprise not found";
  }

  $active_term = $ent->active_term();
  if ($active_term == null) {
    return "No active term found for this enterprise.";
  }


  $feesDataImport = ImportSchoolPayTransaction::find($request->id);
  if ($feesDataImport == null) {
    return "School Pay Transaction Import not found";
  }


  $file_path = public_path('storage/' . $feesDataImport->file_path);
  if (!file_exists($file_path)) {
    return "File not found: $file_path";
  }



  set_time_limit(-1);
  //set unlimited memory
  ini_set('memory_limit', '-1');
  $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
  $spreadsheet = $reader->load($file_path);
  $sheet = $spreadsheet->getActiveSheet();
  $rows = $sheet->toArray();
  $count = 0;
  $success = 0;
  $fail = 0;
  $fail_text = "";

  //firstrow
  if (count($rows) < 2) {
    return "No data found in the file. Please check the file and try again.";
  }
  $firstRow = $rows[0];

  /* 
array:12 [▼
  0 => "Date created"
  1 => "Description"
  2 => "Student name"
  3 => "Class code"
  4 => "Payment code"
  5 => "Registration number"
  6 => "Channel trans id"
  7 => "Reciept number"
  8 => "Channel code"
  9 => "Channel memo"
  10 => "Amount"
  11 => "Bank name"
]

array:12 [▼
  0 => "2025-06-19 18:01:23.190382"
  1 => "PMNT_RECV-AIRTEL_MONEY_UG - 1005183410 - Nagawa Angella"
  2 => "Nagawa Angella"
  3 => "DCM2023"
  4 => "1005183410"
  5 => null
  6 => "125383786036"
  7 => "42392643"
  8 => "AIRTEL_MONEY_UG"
  9 => "1005183410 Nagawa Angella KAMPALA INSTITUTE OF HEALTH DCM2023"
  10 => "50000"
  11 => "Bank Of Africa"
]

  "id" => 1
    "created_at" => "2025-06-26 00:38:38"
    "updated_at" => "2025-06-26 01:04:03"
    "enterprise_id" => 24
    "school_pay_transporter_id" => "G"
    "amount" => "K"
    "description" => "J"
    "payment_date" => "A"
    "schoolpayReceiptNumber" => "H"
    "paymentDateAndTime" => null
    "settlementBankCode" => null
    "sourceChannelTransDetail" => "B"
    "sourceChannelTransactionId" => "G"
    "sourcePaymentChannel" => "I"
    "studentClass" => "D"
    "studentName" => "C"
    "studentPaymentCode" => "D"
    "studentRegistrationNumber" => "F"
    "transactionCompletionStatus" => null
    "file_path" => "files/62e1dcc59d984537a0d5e09ed6d7fc03.xlsx"
    "source" => "school_pay"
*/




  $ent = Enterprise::find($feesDataImport->enterprise_id);
  if ($ent == null) {
    return "Enterprise not found";
  }
  $active_term = $ent->active_term();
  if ($active_term == null) {
    return "No active term found for this enterprise.";
  }

  $header = [];
  if (isset($rows[0])) {
    $header = $rows[0];
  }
  foreach ($rows as $key => $row) {
    $count++;
    if ($count < 2) {
      continue;
    }
    $rawData = [];

    //must be set and not empty $feesDataImport->payment_date)
    if (!isset($feesDataImport->payment_date) || strlen($feesDataImport->payment_date) < 1) {
      $fail++;
      $fail_text .= "Payment Date column is not set or empty.<br>";
      echo "Payment Date column is not set or empty.<br>";
      continue;
    }
    $payment_date_col = Utils::alphabet_to_index($feesDataImport->payment_date);
    //must be set and not empty $feesDataImport->amount)
    if (!isset($feesDataImport->amount) || strlen($feesDataImport->amount) < 1) {
      $fail++;
      $fail_text .= "Amount column is not set or empty.<br>";
      echo "Amount column is not set or empty.<br>";
      continue;
    }
    //must be set and not empty $feesDataImport->schoolpayReceiptNumber)
    if (!isset($feesDataImport->schoolpayReceiptNumber) || strlen($feesDataImport->schoolpayReceiptNumber) < 1) {
      $fail++;
      $fail_text .= "School Pay Receipt Number is not set or empty.<br>";
      echo "School Pay Receipt Number is not set or empty.<br>";
      continue;
    }

    $schoolpayReceiptNumber_col = Utils::alphabet_to_index($feesDataImport->schoolpayReceiptNumber);
    $amount_col = Utils::alphabet_to_index($feesDataImport->amount);
    $description_col = Utils::alphabet_to_index($feesDataImport->description);
    $sourceChannelTransactionId_col = Utils::alphabet_to_index($feesDataImport->sourceChannelTransactionId);

    //studentPaymentCode is required
    if (!isset($feesDataImport->studentPaymentCode) || strlen($feesDataImport->studentPaymentCode) < 1) {
      $fail++;
      $fail_text .= "Student Payment Code column is not set or empty.<br>";
      echo "Student Payment Code column is not set or empty.<br>";
      continue;
    }


    //must be set and not empty $feesDataImport-studentName 
    if (!isset($feesDataImport->studentName) || strlen($feesDataImport->studentName) < 1) {
      $fail++;
      $fail_text .= "Student Name column is not set or empty.<br>";
      echo "Student Name column is not set or empty.<br>";
      continue;
    }
    //must be set and not empty $feesDataImport->school_pay_transporter_id)
    if (!isset($feesDataImport->school_pay_transporter_id) || strlen($feesDataImport->school_pay_transporter_id) < 1) {
      $fail++;
      $fail_text .= "School Pay Transporter ID is not set or empty.<br>";
      echo "School Pay Transporter ID is not set or empty.<br>";
      continue;
    }

    $source_col = Utils::alphabet_to_index($feesDataImport->sourcePaymentChannel);
    $sourceChannelTransDetail_col = Utils::alphabet_to_index($feesDataImport->sourceChannelTransDetail);
    $sourcePaymentChannel_col = Utils::alphabet_to_index($feesDataImport->sourcePaymentChannel);
    $studentClass_col = Utils::alphabet_to_index($feesDataImport->studentClass);
    $studentName_col = Utils::alphabet_to_index($feesDataImport->studentName);
    $studentPaymentCode_col = Utils::alphabet_to_index($feesDataImport->studentPaymentCode);
    $studentRegistrationNumber_col = Utils::alphabet_to_index($feesDataImport->studentRegistrationNumber);

    $school_pay_transporter_id_col = Utils::alphabet_to_index($feesDataImport->school_pay_transporter_id);
    foreach ($header as $_index => $_value) {
      $_row['value'] = $row[$_index];
      $rawData[$_value] = $row[$_index];
    }


    $schoolpayReceiptNumber = $row[$schoolpayReceiptNumber_col];
    if (!isset($schoolpayReceiptNumber) || strlen($schoolpayReceiptNumber) < 1) {
      $fail++;
      $fail_text .= "Row $count: School Pay Receipt Number is not set or empty.<br>";
      echo "<span style='background-color: #ffcccc; color: #a94442; padding: 2px 6px; border-radius: 3px;'>Row $count: School Pay Receipt Number is not set or empty.</span><br>";
      continue;
    }

    $transaction = SchoolPayTransaction::where([
      'schoolpayReceiptNumber' => $schoolpayReceiptNumber,
    ])->first();
    if ($transaction != null) {
      //already exists, skip this row
      echo "<span style='background-color: #dff0d8; color: #3c763d; padding: 2px 6px; border-radius: 3px;'>Row $count: Transaction with School Pay Receipt Number '$schoolpayReceiptNumber' already exists. Skipping this row.</span><br>";
      continue;
    }
    $transaction = new SchoolPayTransaction();
    $transaction->enterprise_id = $ent->id;
    $transaction->school_pay_transporter_id = $row[$school_pay_transporter_id_col];
    $transaction->schoolpayReceiptNumber = $schoolpayReceiptNumber;
    $transaction->created_by_id = $u->id;
    $transaction->amount = trim($row[$amount_col]);
    $transaction->description = trim($row[$description_col]);
    $transaction->payment_date = trim($row[$payment_date_col]);
    $transaction->paymentDateAndTime = trim($row[$payment_date_col]);
    $transaction->source = trim($row[$source_col]);
    $transaction->sourceChannelTransDetail = trim($row[$sourceChannelTransDetail_col]);
    $transaction->type = 'FEES_PAYMENT';
    $transaction->status = 'Pending'; // Default status
    $transaction->data = json_encode($rawData); // Store the raw data of the record
    $transaction->sourceChannelTransactionId = trim($row[$sourceChannelTransactionId_col]);
    $transaction->sourcePaymentChannel = trim($row[$sourcePaymentChannel_col]);
    $transaction->studentClass = trim($row[$studentClass_col]);
    $transaction->studentName = trim($row[$studentName_col]);
    $transaction->studentPaymentCode = trim($row[$studentPaymentCode_col]);
    $transaction->studentRegistrationNumber = trim($row[$studentRegistrationNumber_col]);
    $transaction->transactionCompletionStatus = 'Completed';
    $transaction->academic_year_id = $active_term->academic_year_id;
    $transaction->term_id = $active_term->id;
    $transaction->save();
    echo "<span style='background-color: #dff0d8; color: #3c763d; padding: 2px 6px; border-radius: 3px;'>Row $count: Transaction with School Pay Receipt Number '$schoolpayReceiptNumber' created successfully.</span><br>";
  }
});

// ==================== DEPRECATED FEES DATA IMPORT ROUTES ====================
// NOTE: These routes are DEPRECATED and will be removed in a future version.
// They have been moved to app/Controllers/FeesDataImportController.php
// Please use the new routes under /admin prefix instead.
// ==================== DO NOT USE - USE /admin ROUTES INSTEAD ====================

/**
 * @deprecated Use /fees-data-import-validate instead
 */
Route::get('fees-data-import-validate', function (Request $request) {
  $u = Admin::user();
  if ($u == null) {
    return "You are not logged in";
  }

  $import = FeesDataImport::find($request->id);
  if ($import == null) {
    return "Fees Data Import not found";
  }

  if ($import->enterprise_id != $u->enterprise_id) {
    return "Access denied. This import belongs to a different enterprise.";
  }

  try {

    $service = new \App\Services\FeesImportServiceCSV();
    $validation = $service->validateImport($import);

    echo "<div style='font-family: Arial, sans-serif; padding: 20px; max-width: 900px;'>";
    echo "<h2>Import Validation Results</h2>";
    echo "<h3>Import: " . htmlspecialchars($import->title) . "</h3>";

    if ($validation['valid']) {
      echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
      echo "<strong>✓ Validation Passed!</strong><br>";
      echo "The import file is ready to be processed.";
      echo "</div>";
    } else {
      echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
      echo "<strong>✗ Validation Failed</strong><br>";
      echo "Please fix the errors below before processing the import.";
      echo "</div>";
    }

    // Show statistics
    if (!empty($validation['stats'])) {
      echo "<h4>File Statistics</h4>";
      echo "<table style='border-collapse: collapse; width: 100%; margin: 15px 0;'>";
      foreach ($validation['stats'] as $key => $value) {
        // Skip complex arrays - we'll show them separately
        if (in_array($key, ['student_list', 'services_summary'])) continue;

        $displayKey = ucwords(str_replace('_', ' ', $key));
        echo "<tr style='border-bottom: 1px solid #ddd;'>";
        echo "<td style='padding: 8px; font-weight: bold;'>{$displayKey}:</td>";
        echo "<td style='padding: 8px;'>";

        if (is_array($value)) {
          echo htmlspecialchars(json_encode($value));
        } else {
          echo htmlspecialchars(is_string($value) ? $value : strval($value));
        }

        echo "</td>";
        echo "</tr>";
      }
      echo "</table>";

      // Show services summary if available
      if (!empty($validation['stats']['services_summary'])) {
        $servicesSummary = $validation['stats']['services_summary'];
        echo "<h4>Services Configuration (" . count($servicesSummary) . " service columns)</h4>";
        echo "<table style='border-collapse: collapse; width: 100%; margin: 15px 0; border: 1px solid #ddd;'>";
        echo "<thead style='background: #f8f9fa;'>";
        echo "<tr>";
        echo "<th style='padding: 10px; text-align: left; border: 1px solid #ddd;'>Column</th>";
        echo "<th style='padding: 10px; text-align: left; border: 1px solid #ddd;'>Service Title</th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";

        foreach ($servicesSummary as $service) {
          echo "<tr style='border-bottom: 1px solid #ddd;'>";
          echo "<td style='padding: 8px; border: 1px solid #ddd; text-align: center;'><strong>" . htmlspecialchars($service['column']) . "</strong></td>";
          echo "<td style='padding: 8px; border: 1px solid #ddd;'>" . htmlspecialchars($service['title']) . "</td>";
          echo "</tr>";
        }

        echo "</tbody>";
        echo "</table>";
      }

      // Show student match list if available
      if (!empty($validation['stats']['student_list'])) {
        $studentList = $validation['stats']['student_list'];
        $foundCount = count(array_filter($studentList, function ($s) {
          return $s['found'];
        }));
        $notFoundCount = count($studentList) - $foundCount;

        echo "<h4>Student Match Details (" . count($studentList) . " students checked)</h4>";

        // Filter buttons
        echo "<div style='margin: 15px 0;'>";
        echo "<button onclick='filterRows(\"all\")' class='filter-btn' style='padding: 8px 16px; margin-right: 5px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;'>All (" . count($studentList) . ")</button>";
        echo "<button onclick='filterRows(\"found\")' class='filter-btn' style='padding: 8px 16px; margin-right: 5px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer;'>✓ Found (" . $foundCount . ")</button>";
        echo "<button onclick='filterRows(\"not-found\")' class='filter-btn' style='padding: 8px 16px; background: #dc3545; color: white; border: none; border-radius: 4px; cursor: pointer;'>✗ Not Found (" . $notFoundCount . ")</button>";
        echo "</div>";

        echo "<div style='max-height: 400px; overflow-y: auto; border: 1px solid #ddd; border-radius: 4px;'>";
        echo "<table id='student-table' style='border-collapse: collapse; width: 100%;'>";
        echo "<thead style='position: sticky; top: 0; background: #f8f9fa;'>";
        echo "<tr>";
        echo "<th style='padding: 10px; text-align: left; border-bottom: 2px solid #ddd;'>Row</th>";
        echo "<th style='padding: 10px; text-align: left; border-bottom: 2px solid #ddd;'>CSV Name</th>";
        echo "<th style='padding: 10px; text-align: left; border-bottom: 2px solid #ddd;'>Identifier</th>";
        echo "<th style='padding: 10px; text-align: left; border-bottom: 2px solid #ddd;'>Status</th>";
        echo "<th style='padding: 10px; text-align: left; border-bottom: 2px solid #ddd;'>System Name</th>";
        echo "<th style='padding: 10px; text-align: right; border-bottom: 2px solid #ddd;'>Balance</th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";

        foreach ($studentList as $student) {
          $rowClass = $student['found'] ? 'row-found' : 'row-not-found';
          $rowStyle = $student['found'] ? '' : 'background: #fff3cd;';
          $statusBadge = $student['found']
            ? "<span style='color: #28a745; font-weight: bold;'>✓ Found</span>"
            : "<span style='color: #dc3545; font-weight: bold;'>✗ Not Found</span>";

          echo "<tr class='{$rowClass}' style='{$rowStyle} border-bottom: 1px solid #eee;'>";
          echo "<td style='padding: 8px;'>{$student['row']}</td>";
          echo "<td style='padding: 8px;'>" . htmlspecialchars($student['name']) . "</td>";
          echo "<td style='padding: 8px;'><code>" . htmlspecialchars($student['identifier']) . "</code></td>";
          echo "<td style='padding: 8px;'>{$statusBadge}</td>";
          echo "<td style='padding: 8px;'>" . ($student['db_name'] ? htmlspecialchars($student['db_name']) : '-') . "</td>";
          echo "<td style='padding: 8px; text-align: right;'>" . htmlspecialchars($student['current_balance']) . "</td>";
          echo "</tr>";
        }

        echo "</tbody>";
        echo "</table>";
        echo "</div>";

        // JavaScript for filtering
        echo "<script>
        function filterRows(filter) {
          const rows = document.querySelectorAll('#student-table tbody tr');
          
          rows.forEach(row => {
            if (filter === 'all') {
              row.style.display = '';
            } else if (filter === 'found') {
              row.style.display = row.classList.contains('row-found') ? '' : 'none';
            } else if (filter === 'not-found') {
              row.style.display = row.classList.contains('row-not-found') ? '' : 'none';
            }
          });
          
          // Update button styles
          document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.style.opacity = '0.7';
          });
          event.target.style.opacity = '1';
        }
        </script>";
      }
    }

    // Show errors
    if (!empty($validation['errors'])) {
      echo "<h4 style='color: #dc3545;'>Errors (" . count($validation['errors']) . ")</h4>";
      echo "<ul style='background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px 15px 15px 35px; margin: 15px 0;'>";
      foreach ($validation['errors'] as $error) {
        echo "<li style='margin: 5px 0;'>" . htmlspecialchars($error) . "</li>";
      }
      echo "</ul>";
    }

    // Show warnings
    if (!empty($validation['warnings'])) {
      echo "<h4 style='color: #ff6b35;'>Warnings (" . count($validation['warnings']) . ")</h4>";
      echo "<ul style='background: #fff3cd; border-left: 4px solid #ff9800; padding: 15px 15px 15px 35px; margin: 15px 0;'>";
      foreach ($validation['warnings'] as $warning) {
        echo "<li style='margin: 5px 0;'>" . htmlspecialchars($warning) . "</li>";
      }
      echo "</ul>";
    }

    if ($validation['valid']) {
      $importUrl = url("fees-data-import-do-import-optimized?id={$import->id}");
      echo "<div style='margin-top: 25px;'>";
      echo "<a href='{$importUrl}' class='btn btn-primary' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; display: inline-block;'>";
      echo "Proceed with Import</a>";
      echo " <a href='javascript:history.back()' style='margin-left: 10px; padding: 10px 20px; text-decoration: none; border: 1px solid #ccc; border-radius: 4px; display: inline-block;'>Go Back</a>";
      echo "</div>";
    } else {
      echo "<div style='margin-top: 25px;'>";
      echo "<a href='javascript:history.back()' style='padding: 10px 20px; text-decoration: none; border: 1px solid #ccc; border-radius: 4px; display: inline-block;'>Go Back</a>";
      echo "</div>";
    }

    echo "</div>";
  } catch (\Exception $e) {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 20px; border-radius: 5px;'>";
    echo "<strong>Validation Error:</strong><br>";
    echo htmlspecialchars($e->getMessage());
    echo "</div>";
    Log::error('Fees import validation failed', [
      'import_id' => $import->id,
      'error' => $e->getMessage(),
      'trace' => $e->getTraceAsString()
    ]);
  }
});

/**
 * Process Fees Data Import - Uses Optimized Service
 */
Route::get('fees-data-import-do-import-optimized', function (Request $request) {
  $u = Admin::user();
  if ($u == null) {
    return "You are not logged in";
  }

  $import = FeesDataImport::find($request->id);
  if ($import == null) {
    return "Fees Data Import not found";
  }

  if ($import->enterprise_id != $u->enterprise_id) {
    return "Access denied. This import belongs to a different enterprise.";
  }

  // Set execution limits for large imports
  set_time_limit(-1);
  ini_set('memory_limit', '512M');

  echo "<div style='font-family: Arial, sans-serif; padding: 20px; max-width: 900px;'>";
  echo "<h2>Processing Import: " . htmlspecialchars($import->title) . "</h2>";
  echo "<p>Please wait while the import is being processed...</p>";
  echo "<div style='background: #e9ecef; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
  echo "<div id='progress-info'>Starting import...</div>";
  echo "</div>";

  // Flush output so user sees the above immediately
  if (ob_get_level() > 0) {
    ob_flush();
  }
  flush();

  try {
    $service = new \App\Services\FeesImportServiceOptimized();
    $result = $service->processImport($import, $u);

    if ($result['success']) {
      echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
      echo "<strong>✓ Import Completed Successfully!</strong>";
      echo "</div>";

      echo "<h4>Import Summary</h4>";
      echo "<pre style='background: #f8f9fa; padding: 15px; border-radius: 5px; white-space: pre-wrap;'>";
      echo htmlspecialchars($result['message']);
      echo "</pre>";

      if (!empty($result['stats'])) {
        echo "<h4>Detailed Statistics</h4>";
        echo "<table style='border-collapse: collapse; width: 100%; margin: 15px 0; border: 1px solid #ddd;'>";
        echo "<tr style='background: #f8f9fa;'><th style='padding: 10px; text-align: left; border: 1px solid #ddd;'>Metric</th><th style='padding: 10px; text-align: right; border: 1px solid #ddd;'>Count</th></tr>";
        foreach ($result['stats'] as $key => $value) {
          $displayKey = ucwords(str_replace('_', ' ', $key));
          if ($key == 'errors') continue; // Skip errors array
          echo "<tr style='border-bottom: 1px solid #ddd;'>";
          echo "<td style='padding: 8px; border: 1px solid #ddd;'>{$displayKey}</td>";
          echo "<td style='padding: 8px; text-align: right; border: 1px solid #ddd;'><strong>" . htmlspecialchars($value) . "</strong></td>";
          echo "</tr>";
        }
        echo "</table>";
      }

      $recordsUrl = url("fees-data-import-records?fees_data_import_id={$import->id}");
      echo "<div style='margin-top: 25px;'>";
      echo "<a href='{$recordsUrl}' class='btn btn-success' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; display: inline-block;'>";
      echo "View Import Records</a>";
      echo " <a href='" . url('fees-data-import') . "' style='margin-left: 10px; padding: 10px 20px; text-decoration: none; border: 1px solid #ccc; border-radius: 4px; display: inline-block;'>Back to Imports</a>";
      echo "</div>";
    } else {
      echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
      echo "<strong>✗ Import Failed</strong><br>";
      echo htmlspecialchars($result['message']);
      echo "</div>";

      echo "<div style='margin-top: 25px;'>";
      echo "<a href='javascript:history.back()' style='padding: 10px 20px; text-decoration: none; border: 1px solid #ccc; border-radius: 4px; display: inline-block;'>Go Back</a>";
      echo "</div>";
    }
  } catch (\Exception $e) {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
    echo "<strong>Processing Error:</strong><br>";
    echo htmlspecialchars($e->getMessage());
    echo "</div>";

    Log::error('Fees import processing failed', [
      'import_id' => $import->id,
      'user_id' => $u->id,
      'error' => $e->getMessage(),
      'trace' => $e->getTraceAsString()
    ]);
  }

  echo "</div>";
});

/**
 * Process Fees Data Import V2 - NEW ATOMIC APPROACH
 * Uses FeesImportServiceV2 with bulletproof balance logic
 */
Route::get('fees-data-import-do-import-v2', function (Request $request) {
  $u = Admin::user();
  if ($u == null) {
    return "You are not logged in";
  }

  $import = FeesDataImport::find($request->id);
  if ($import == null) {
    return "Fees Data Import not found";
  }

  if ($import->enterprise_id != $u->enterprise_id) {
    return "Access denied. This import belongs to a different enterprise.";
  }

  // Get term
  $term = $import->term ?? $import->enterprise->active_term();
  if (!$term) {
    return "No active term found for this import";
  }

  // Set execution limits for large imports
  set_time_limit(-1);
  ini_set('memory_limit', '1024M');

  echo "<div style='font-family: Arial, sans-serif; padding: 20px; max-width: 900px;'>";
  echo "<h2>🆕 Processing Import V2: " . htmlspecialchars($import->title) . "</h2>";
  echo "<div style='background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
  echo "<strong>New Import Engine V2</strong><br>";
  echo "Using atomic transactions with bulletproof balance logic.<br>";
  echo "This version ensures 100% accuracy for all balance calculations.";
  echo "</div>";
  echo "<p>Please wait while the import is being processed...</p>";
  echo "<div style='background: #e9ecef; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
  echo "<div id='progress-info'>Starting import...</div>";
  echo "</div>";

  // Flush output
  if (ob_get_level() > 0) {
    ob_flush();
  }
  flush();

  try {
    $service = new \App\Services\FeesImportServiceV2($import->enterprise, $term, $u);
    $result = $service->processImport($import);

    if ($result['success']) {
      echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
      echo "<strong>✓ Import V2 Completed Successfully!</strong>";
      echo "</div>";

      echo "<h4>Import Summary</h4>";
      echo "<pre style='background: #f8f9fa; padding: 15px; border-radius: 5px; white-space: pre-wrap;'>";
      echo htmlspecialchars($result['message']);
      echo "</pre>";

      if (!empty($result['stats'])) {
        echo "<h4>Detailed Statistics</h4>";
        echo "<table style='border-collapse: collapse; width: 100%; margin: 15px 0; border: 1px solid #ddd;'>";
        echo "<tr style='background: #f8f9fa;'><th style='padding: 10px; text-align: left; border: 1px solid #ddd;'>Metric</th><th style='padding: 10px; text-align: right; border: 1px solid #ddd;'>Count</th></tr>";
        foreach ($result['stats'] as $key => $value) {
          $displayKey = ucwords(str_replace('_', ' ', $key));
          echo "<tr style='border-bottom: 1px solid #ddd;'>";
          echo "<td style='padding: 8px; border: 1px solid #ddd;'>{$displayKey}</td>";
          echo "<td style='padding: 8px; text-align: right; border: 1px solid #ddd;'><strong>" . htmlspecialchars($value) . "</strong></td>";
          echo "</tr>";
        }
        echo "</table>";
      }

      $recordsUrl = url("fees-data-import-records?fees_data_import_id={$import->id}");
      echo "<div style='margin-top: 25px;'>";
      echo " <a href='{$recordsUrl}' style='padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 4px; display: inline-block;'>View Import Records</a>";
      echo " <a href='" . url('fees-data-imports') . "' style='margin-left: 10px; padding: 10px 20px; text-decoration: none; border: 1px solid #ccc; border-radius: 4px; display: inline-block;'>Back to Imports</a>";
      echo "</div>";
    } else {
      echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
      echo "<strong>✗ Import Failed</strong><br>";
      echo htmlspecialchars($result['message']);
      echo "</div>";

      echo "<div style='margin-top: 25px;'>";
      echo " <a href='" . url('fees-data-imports/' . $import->id . '/edit') . "' style='padding: 10px 20px; background: #ffc107; color: black; text-decoration: none; border-radius: 4px; display: inline-block;'>Edit Import Settings</a>";
      echo " <a href='" . url('fees-data-imports') . "' style='margin-left: 10px; padding: 10px 20px; text-decoration: none; border: 1px solid #ccc; border-radius: 4px; display: inline-block;'>Back to Imports</a>";
      echo "</div>";
    }
  } catch (\Exception $e) {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
    echo "<strong>✗ Error:</strong> " . htmlspecialchars($e->getMessage());
    echo "</div>";

    echo "<div style='margin-top: 25px;'>";
    echo " <a href='" . url('fees-data-imports/' . $import->id . '/edit') . "' style='padding: 10px 20px; background: #ffc107; color: black; text-decoration: none; border-radius: 4px; display: inline-block;'>Edit Import Settings</a>";
    echo " <a href='" . url('fees-data-imports') . "' style='margin-left: 10px; padding: 10px 20px; text-decoration: none; border: 1px solid #ccc; border-radius: 4px; display: inline-block;'>Back to Imports</a>";
    echo "</div>";
  }

  echo "</div>";
});

/**
 * Retry Failed Records - Uses Optimized Service
 */
Route::get('fees-data-import-retry', function (Request $request) {
  $u = Admin::user();
  if ($u == null) {
    return "You are not logged in";
  }

  $import = FeesDataImport::find($request->id);
  if ($import == null) {
    return "Fees Data Import not found";
  }

  if ($import->enterprise_id != $u->enterprise_id) {
    return "Access denied. This import belongs to a different enterprise.";
  }

  set_time_limit(-1);
  ini_set('memory_limit', '512M');

  echo "<div style='font-family: Arial, sans-serif; padding: 20px; max-width: 900px;'>";
  echo "<h2>Retrying Failed Records</h2>";
  echo "<h3>Import: " . htmlspecialchars($import->title) . "</h3>";
  echo "<p>Attempting to retry failed records...</p>";

  try {
    // Use CSV service for retry (same as main import)
    $service = new \App\Services\FeesImportServiceCSV();
    $result = $service->retryFailedRecords($import);

    if ($result['success']) {
      echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
      echo "<strong>✓ Retry Completed</strong><br>";
      echo htmlspecialchars($result['message']);
      echo "</div>";

      if (!empty($result['stats'])) {
        echo "<h4>Retry Statistics</h4>";
        echo "<table style='border-collapse: collapse; width: 100%; margin: 15px 0;'>";
        foreach ($result['stats'] as $key => $value) {
          $key = ucwords(str_replace('_', ' ', $key));
          echo "<tr style='border-bottom: 1px solid #ddd;'>";
          echo "<td style='padding: 8px; font-weight: bold;'>{$key}:</td>";
          echo "<td style='padding: 8px;'>" . htmlspecialchars($value) . "</td>";
          echo "</tr>";
        }
        echo "</table>";
      }
    } else {
      echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
      echo "<strong>✗ Retry Failed</strong><br>";
      echo htmlspecialchars($result['message']);
      echo "</div>";
    }

    $recordsUrl = url("fees-data-import-records?fees_data_import_id={$import->id}");
    echo "<div style='margin-top: 25px;'>";
    echo "<a href='{$recordsUrl}' class='btn btn-primary' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; display: inline-block;'>";
    echo "View All Records</a>";
    echo " <a href='" . url('fees-data-import') . "' style='margin-left: 10px; padding: 10px 20px; text-decoration: none; border: 1px solid #ccc; border-radius: 4px; display: inline-block;'>Back to Imports</a>";
    echo "</div>";
  } catch (\Exception $e) {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 20px; border-radius: 5px;'>";
    echo "<strong>Retry Error:</strong><br>";
    echo htmlspecialchars($e->getMessage());
    echo "</div>";

    Log::error('Fees import retry failed', [
      'import_id' => $import->id,
      'user_id' => $u->id,
      'error' => $e->getMessage()
    ]);
  }

  echo "</div>";
});

/**
 * Duplicate Import - Creates a copy with reset status
 */
Route::get('fees-data-import-duplicate', function (Request $request) {
  $u = Admin::user();
  if ($u == null) {
    admin_error('Authentication Required', 'You are not logged in');
    return redirect('auth/login');
  }

  $import = FeesDataImport::find($request->id);
  if ($import == null) {
    admin_error('Not Found', 'Fees Data Import not found');
    return redirect('fees-data-imports');
  }

  if ($import->enterprise_id != $u->enterprise_id) {
    admin_error('Access Denied', 'This import belongs to a different enterprise.');
    return redirect('fees-data-imports');
  }

  try {
    // Create a duplicate with reset status
    $duplicate = $import->replicate();

    // Reset fields that should not be copied
    $duplicate->status = 'Pending';
    $duplicate->batch_identifier = null; // Will be auto-generated
    $duplicate->file_hash = null; // Will be recalculated on processing
    $duplicate->total_rows = 0;
    $duplicate->processed_rows = 0;
    $duplicate->success_count = 0;
    $duplicate->failed_count = 0;
    $duplicate->skipped_count = 0;
    $duplicate->started_at = null;
    $duplicate->completed_at = null;
    $duplicate->processed_at = null;
    $duplicate->summary = null;
    $duplicate->validation_errors = null;
    $duplicate->validation_warnings = null;
    $duplicate->is_locked = false;
    $duplicate->locked_by = null;
    $duplicate->locked_at = null;

    // Update metadata
    $duplicate->created_by_id = $u->id;
    $duplicate->title = $import->title . ' (Copy)';
    $duplicate->created_at = now();
    $duplicate->updated_at = now();

    // Keep the same file path (don't duplicate the file)
    // Keep the same configuration settings:
    // - identify_by
    // - reg_number_column / school_pay_column
    // - services_columns
    // - current_balance_column
    // - previous_fees_term_balance_column
    // - cater_for_balance
    // - term_id
    // - enterprise_id

    $duplicate->save();

    admin_success('Success', 'Import duplicated successfully! You can now modify settings and process this import.');
    return redirect('fees-data-imports/' . $duplicate->id . '/edit');
  } catch (\Exception $e) {
    Log::error('Failed to duplicate fees import', [
      'import_id' => $import->id,
      'user_id' => $u->id,
      'error' => $e->getMessage()
    ]);

    admin_error('Duplication Failed', 'Failed to duplicate import: ' . $e->getMessage());
    return redirect('fees-data-import');
  }
});

// ==================== END DEPRECATED ROUTES ====================

/*
 * ==================== DEPRECATED ROUTE ====================
 * The route below has been deprecated in favor of 'fees-data-import-do-import-optimized'
 * which uses the FeesImportServiceOptimized class with:
 * - Duplicate file/row prevention (file_hash, row_hash)
 * - Batch processing with transactions (50 rows per transaction)
 * - Comprehensive validation before processing
 * - Better error handling and retry mechanism
 * - Progress tracking and locking
 * - Automatic cache management
 * 
 * This old route is kept temporarily for reference but should NOT be used.
 * To remove it completely, delete from line 1151 to line 1551.
 * Date deprecated: 2025-01-12
 * ==================== DEPRECATED ROUTE ====================
 */

// DEPRECATED - DO NOT USE - Use 'fees-data-import-do-import-optimized' instead
/* 
Route::get('fees-data-import-do-import', function (Request $request) {
  $u = Admin::user();
  if ($u == null) {
    return "You are not logged in";
  }
  //show errors for php
  ini_set('display_errors', '1');
  ini_set('display_startup_errors', '1');
  error_reporting(E_ALL);

  $importedServiceCategory = null;
  try {
    $importedServiceCategory = ServiceCategory::getOrCreateImportedCategory($u->enterprise_id);
  } catch (\Throwable $th) {
    $importedServiceCategory = null;
    throw $th;
  }
  if ($importedServiceCategory == null) {
    return "Failed to get or create imported service category.";
  }
  $ent = Enterprise::find($u->enterprise_id);
  if ($ent == null) {
    return "Enterprise not found";
  }

  $active_term = $ent->active_term();
  if ($active_term == null) {
    return "No active term found for this enterprise.";
  }


  $feesDataImport = FeesDataImport::find($request->id);
  if ($feesDataImport == null) {
    return "Fees Data Import not found";
  }

  $file_path = public_path('storage/' . $feesDataImport->file_path);
  if (!file_exists($file_path)) {
    return "File not found: $file_path";
  }


  if (!in_array($feesDataImport->identify_by, ['school_pay_account_id', 'reg_number'])) {
    return "Invalid identify_by value: " . htmlspecialchars($feesDataImport->identify_by);
  }

  set_time_limit(-1);
  //set unlimited memory
  ini_set('memory_limit', '-1');
  $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
  $spreadsheet = $reader->load($file_path);
  $sheet = $spreadsheet->getActiveSheet();
  $rows = $sheet->toArray();
  $count = 0;
  $success = 0;
  $fail = 0;
  $fail_text = "";

  //firstrow
  if (count($rows) < 2) {
    return "No data found in the file. Please check the file and try again.";
  }
  $firstRow = $rows[0];

  $services = [];
  if (is_array($feesDataImport->services_columns)) {
    try {
      $index = 0;
      foreach ($feesDataImport->services_columns as $col) {
        $col = trim($col);
        if (strlen($col) > 0) {
          $index = Utils::alphabet_to_index($col);

          //check if $firstRow index is set and has some value
          if (!isset($firstRow[$index]) || strlen($firstRow[$index]) < 1) {
            $fail++;
            $fail_text .= "Column '$col' not found in the first row.<br>";
            echo "Column '$col' not found in the first row.<br>";
            continue;
          }
          $my_col = [];
          $my_col['index'] = $index;
          $my_col['name'] = $firstRow[$index];
          $my_col['column'] = $col;
          $services[] = $my_col;
        }
      }
    } catch (\Exception $e) {
      $services = [];
    }
  }

  $services_indexes = [];
  foreach ($services as $service) {
    $services_indexes[] = $service['index'];
  }


  $balance_column_index = Utils::alphabet_to_index($feesDataImport->current_balance_column);

  foreach ($rows as $key => $row) {
    $count++;
    if ($count < 1) {
      continue;
    }
    $record = FeesDataImportRecord::where([
      'fees_data_import_id' => $feesDataImport->id,
      'index' => $count,
    ])->first();
    if ($record == null) {
      $record = new FeesDataImportRecord();
      $record->fees_data_import_id = $feesDataImport->id;
      $record->enterprise_id = $feesDataImport->enterprise_id;
      $record->index = $count;
    }


    $student_identifier = null;
    $student = null;
    if ($feesDataImport->identify_by == 'school_pay_account_id') {
      // Check if the column index is set before accessing
      $colIndex = Utils::alphabet_to_index($feesDataImport->school_pay_column);
      if (!isset($row[$colIndex])) {
        $fail++;
        $fail_text .= "Row $count: School Pay Account column '$colIndex' not set.<br>";
        echo "Row $count: School Pay Account column '$colIndex' not set.<br>";
        $record->error_message = "Row $count: School Pay Account column '$colIndex' not set.";
        $record->status = 'Failed';
        $record->save();
        continue;
      }
      $student_identifier = $row[$colIndex];

      if ($student_identifier == null || strlen($student_identifier) < 1) {
        $fail++;
        $fail_text .= "Row $count: School Pay Account ID is empty.<br>";
        echo "Row $count: School Pay Account ID is empty.<br>";
        $record->error_message = "Row $count: School Pay Account ID is empty. (School Pay ID: {$student_identifier})";
        $record->status = 'Failed';
        $record->save();
        continue;
      }
      $student = User::where([
        'school_pay_payment_code' => $student_identifier,
      ])->first();
    } else if ($feesDataImport->identify_by == 'reg_number') {
      $colIndex = Utils::alphabet_to_index($feesDataImport->reg_number_column);
      if (!isset($row[$colIndex])) {
        $fail++;
        $fail_text .= "Row $count: Registration Number column '$colIndex' not set.<br>";
        echo "Row $count: Registration Number column '$colIndex' not set.<br>";
        $record->error_message = "Row $count: Registration Number column '$colIndex' not set. (Reg Number: " . (isset($row[$colIndex]) ? $row[$colIndex] : 'N/A') . ")";
        $record->status = 'Failed';
        $record->save();
        continue;
      }
      $student_identifier = $row[$colIndex];
      if ($student_identifier == null || strlen($student_identifier) < 1) {
        $fail++;
        $fail_text .= "Row $count: Registration Number is empty.<br>";
        echo "Row $count: Registration Number is empty.<br>";
        $record->error_message = "Row $count: Registration Number is empty. (Reg Number: {$student_identifier})";
        $record->status = 'Failed';
        $record->save();
        continue;
      }
      $student = User::where([
        'user_number' => $student_identifier,
      ])->first();
    }

    if ($student == null) {
      $fail++;
      $fail_text .= "Row $count: Student with identifier '$student_identifier' not found.<br>";
      echo "Row $count: Student with identifier '$student_identifier' not found.<br>";
      $record->error_message = "Row $count: Student with identifier '$student_identifier' not found.";
      $record->status = 'Failed';
      continue;
    }
    $account = $student->account;
    $record->identify_by = $feesDataImport->identify_by;
    $record->reg_number = $student->id; // Assuming reg_number is the student ID
    $record->school_pay = $student_identifier;
    $record->enterprise_id = $feesDataImport->enterprise_id;
    $record->index = $count;
    $record->identify_by = $feesDataImport->identify_by;
    $record->reg_number = $student_identifier; // Assuming reg_number is the student identifier
    //add data
    $record->data = json_encode($row); // Store the raw data of the record
    $record->services_data = json_encode($services); // Store the services data if applicable
    $record->status = 'Processing'; // Set status to processing
    //save
    try {
      $record->save();
    } catch (\Throwable $th) {
      throw new \Exception("Failed to save record for row $count: " . $th->getMessage());
    }

    if ($account == null) {
      $fail++;
      $fail_text .= "Row $count: Student with identifier '$student_identifier' has no account.<br>";
      echo "Row $count: Student with identifier '$student_identifier' has no account.<br>";
      $record->error_message = "Row $count: Student with identifier '$student_identifier' has no account.";
      $record->status = 'Failed';
      continue;
    }

    $last_term_balance = 0;
    $previous_fees_term_balance_column = Utils::alphabet_to_index($feesDataImport->previous_fees_term_balance_column);
    if (isset($row[$previous_fees_term_balance_column]) && strlen($row[$previous_fees_term_balance_column]) > 0) {
      $last_term_balance =  preg_replace('/[^\d.]/', '', $row[$previous_fees_term_balance_column]);
    } else {
      $last_term_balance = 0; // Default to 0 if not set or not numeric
    }
    if ($last_term_balance != 0) {
      $last_term_balance = abs((int)$last_term_balance);
      $last_term_balance = $last_term_balance * -1; // Make it negative as it's a balance owed

      $action_done = '';
      $last_term_balance_record = Transaction::where([
        'account_id' => $account->id,
        'term_id' => $active_term->id,
        'is_last_term_balance' => 'Yes',
      ])->first();

      if ($last_term_balance_record == null) {
        $last_term_balance_record = new Transaction();
        $last_term_balance_record->enterprise_id = $feesDataImport->enterprise_id;
        $last_term_balance_record->account_id = $account->id;
        $last_term_balance_record->created_by_id = $feesDataImport->created_by_id;
        $last_term_balance_record->amount = $last_term_balance;
        $last_term_balance_record->description = "School fees balance for previous term.";
        $last_term_balance_record->is_last_term_balance = 'Yes';
        $last_term_balance_record->academic_year_id = $active_term->academic_year_id;
        $last_term_balance_record->term_id = $active_term->id;
        $last_term_balance_record->type = 'FEES_BILL';
        $last_term_balance_record->payment_date = Carbon::now();
        $last_term_balance_record->source = 'GENERATED'; // Assuming this is a manual entry
        $last_term_balance_record->save();
        $action_done .= "Row $count: Created new last term balance record for student '{$student->name}' with amount UGX " . number_format($last_term_balance) . ".<br>";
      } else {
        // Update existing record if it exists
        $last_term_balance_record->amount = $last_term_balance;
        $last_term_balance_record->is_last_term_balance = 'Yes';
        $last_term_balance_record->description = "Previous term balance adjustment for {$student->name} ({$student->school_pay_payment_code})";
        $last_term_balance_record->save();
        $action_done .= "Row $count: Updated existing last term balance record for student '{$student->name}' with amount UGX " . number_format($last_term_balance) . ".<br>";
      }
    }

    //loop through the services and see if there is any value sent in index
    foreach ($services as $service) {
      $service_index = $service['index'];
      if (!isset($row[$service_index]) || strlen($row[$service_index]) < 1) {
        //skip this service
        continue;
      }
      $service_amount = $row[$service_index];

      //clean $service_amount to numeric
      $service_amount = preg_replace('/[^\d.]/', '', $service_amount);
      if (!is_numeric($service_amount) || $service_amount <= 0) {
        //skip this service
        $action_done .= "Row $count: Service amount for service '{$service['name']}' is not a valid number or is less than or equal to zero. Skipping this service.<br>";
        continue;
      }
      $service_name = trim($service['name']) . ' - ' . $service_amount;

      //add details for this service to the record
      $description = "Service: $service_name, Amount: $service_amount";

      $serviceObject = Service::createIfNotExists([
        'name' => $service_name,
        'fee' => $service_amount,
        'service_category_id' => $importedServiceCategory->id,
        'enterprise_id' => $feesDataImport->enterprise_id,
        'description' => $description,
      ]);

      if ($serviceObject == null) {
        $fail++;
        $fail_text .= "Row $count: Failed to create or find service '$service_name'.<br>";
        echo "Row $count: Failed to create or find service '$service_name'.<br>";
        $record->error_message = "Row $count: Failed to create or find service '$service_name'.";
        $record->status = 'Failed';
        $action_done .= "Row $count: Failed to create or find service '$service_name'.<br>";
        continue;
      }

      //check if already have this service for this term 
      $serviceSubscription = ServiceSubscription::where([
        'service_id' => $serviceObject->id,
        'administrator_id' => $student->id,
        'due_term_id' => $active_term->id,
      ])->first();

      if ($serviceSubscription != null) {
        $action_done .= "Row $count: Service '$service_name' already exists for student '{$student->name}' in the current term. Skipping this service.<br>";
      } else {

        $serviceSubscription = new ServiceSubscription();
        $serviceSubscription->enterprise_id = $feesDataImport->enterprise_id;
        $serviceSubscription->ref_id = $feesDataImport->id;
        $serviceSubscription->service_id = $serviceObject->id;
        $serviceSubscription->administrator_id = $student->id;
        $serviceSubscription->quantity = 1; // Assuming quantity is always 1 for this import
        $serviceSubscription->total = $serviceObject->fee * 1; // Assuming quantity is always 1 for this import
        $serviceSubscription->due_academic_year_id = $active_term->academic_year_id;
        $serviceSubscription->due_term_id = $active_term->id;
        $serviceSubscription->is_processed = 'No'; // Assuming we want to process it later

        try {
          $serviceSubscription->save();
          $action_done .= "Row $count: Creating new service subscription for student '{$student->name}' with service '$service_name'.<br>";
        } catch (\Throwable $th) {
          $fail++;
          $fail_text .= "Row $count: Failed to create service subscription for student '{$student->name}' with service '$service_name'. Error: " . $th->getMessage() . "<br>";
          echo "Row $count: Failed to create service subscription for student '{$student->name}' with service '$service_name'. Error: " . $th->getMessage() . "<br>";
          $record->error_message = "Row $count: Failed to create service subscription for student '{$student->name}' with service '$service_name'. Error: " . $th->getMessage();
          $record->status = 'Failed';
          continue;
        }
      }
    }


    $current_balance = 0;
    $current_balance_column = Utils::alphabet_to_index($feesDataImport->current_balance_column);
    if (isset($row[$current_balance_column]) && strlen($row[$current_balance_column]) > 0) {
      $current_balance =  preg_replace('/[^\d.]/', '', $row[$current_balance_column]);
    } else {
      $current_balance = 0; // Default to 0 if not set or not numeric
    }
    $current_balance = ((int)$current_balance);

    if ($current_balance != 0) {
      //cater_for_balance 
      if ($feesDataImport->cater_for_balance != 'Yes') {
        $current_balance = abs($current_balance); // Make it positive if not catering for negative sign
        $current_balance = $current_balance * -1; // Make it negative as it's a balance owed
      }
      $ACCOUNT_BALANCE = (int)$account->balance;
      if ($ACCOUNT_BALANCE != $current_balance) {
        $account->new_balance_amount = $current_balance;
        $account->new_balance = 1;
        try {
          $account->save();
          $action_done .= "Row $count: Updated account balance for student '{$student->name}' to UGX " . number_format($current_balance) . ".<br>";
        } catch (\Throwable $th) {
          $fail++;
          $fail_text .= "Row $count: Failed to update account balance for student '{$student->name}'. Error: " . $th->getMessage() . "<br>";
          echo "Row $count: Failed to update account balance for student '{$student->name}'. Error: " . $th->getMessage() . "<br>";
          $record->error_message = "Row $count: Failed to update account balance for student '{$student->name}'. Error: " . $th->getMessage();
          $record->status = 'Failed';
          $record->udpated_balance = $current_balance;
          $record->current_balance = $current_balance;
          $record->previous_fees_term_balance = $last_term_balance;
          $record->summary = $action_done;
          $record->data = json_encode($row); // Store the raw data of the record
          $record->services_data = json_encode($services); // Store the services data if applicable
          $record->save();
          continue;
        }
      }
    }
    $record->identify_by = $feesDataImport->identify_by;
    $record->reg_number = $student->id; // Assuming reg_number is the student ID 
    $record->udpated_balance = $current_balance;
    $record->school_pay = $student_identifier;
    $record->current_balance = $current_balance;
    $record->previous_fees_term_balance = $last_term_balance;
    $record->status = 'Completed'; // Mark as completed if no errors
    $record->summary = $action_done;
    $record->data = json_encode($row); // Store the raw data of the record
    $record->services_data = json_encode($services); // Store the services data if applicable
    try {
      $record->save();
      $success++;
      echo "Row $count: Successfully processed student '{$student->name}' with identifier '$student_identifier'.<br>";
      echo "<hr>";
    } catch (\Throwable $th) {
      $fail++;
      $fail_text .= "Row $count: Failed to save record for student '{$student->name}' with identifier '$student_identifier'. Error: " . $th->getMessage() . "<br>";
      echo "Row $count: Failed to save record for student '{$student->name}' with identifier '$student_identifier'. Error: " . $th->getMessage() . "<br>";
      $record->error_message = "Row $count: Failed to save record for student '{$student->name}' with identifier '$student_identifier'. Error: " . $th->getMessage();
      $record->status = 'Failed';
      echo "<hr>";
      continue;
    }
  }
  $feesDataImport->status = 'Completed';
  $feesDataImport->summary = "Total Rows: $count, Success: $success, Fail: $fail<br>" . $fail_text;
  try {
    $feesDataImport->save();
    echo "Fees Data Import completed successfully. Total Rows: $count, Success: $success, Fail: $fail<br>" . $fail_text;
  } catch (\Throwable $th) {
    echo "Failed to update Fees Data Import status. Error: " . $th->getMessage() . "<br>";
  }
  return '';
});
*/
// ==================== END DEPRECATED ROUTE ====================


Route::get('generate-school-report', function (Request $request) {
  // ## 1. Validation and Context Setup ##
  $request->validate(['id' => 'required|integer']);
  $report = SchoolReport::findOrFail($request->id);

  // Use firstOrFail() to ensure relationships exist or fail gracefully.
  $term = $report->term()->firstOrFail();
  $enterprise = $report->enterprise()->firstOrFail();

  $enterpriseId = $enterprise->id;
  $termId = $term->id;
  $academicYearId = $term->academic_year_id;

  // ## 2. Optimized Data Aggregation ##
  // Fetch all relevant transactions for the term in one go.
  $transactions = DB::table('transactions')
    ->where('enterprise_id', $enterpriseId)
    ->where('term_id', $termId)
    ->get();

  // Separate transactions into payments (positive) and bills (negative).
  $payments = $transactions->where('amount', '>', 0);
  $bills = $transactions->where('amount', '<', 0);

  // Map accounts to users to link transactions to students.
  $accountToUserMap = Account::whereIn('id', $transactions->pluck('account_id')->unique())
    ->pluck('administrator_id', 'id');

  // Get a definitive list of users who have associated accounts.
  $validUserIds = User::whereIn('id', $accountToUserMap->values()->unique())->pluck('id')->all();

  // ## 3. Bulk-Load Relational Data ##
  // Eager-load 'user' and 'class' to prevent N+1 queries and "property on null" errors.
  $studentClasses = StudentHasClass::whereIn('administrator_id', $validUserIds)
    ->where('academic_year_id', $academicYearId)
    ->with('class', 'user') // CRITICAL: Eager-load both relationships
    ->get()->keyBy('administrator_id');

  // Get the standard fee amount for each class.
  $classFeeTotals = AcademicClassFee::whereIn('academic_class_id', $studentClasses->pluck('academic_class_id')->unique())
    ->where('due_term_id', $termId)
    ->get()->groupBy('academic_class_id')->map->sum('amount');

  // ## 4. Process Metrics for Each Student ##
  $studentMetrics = [];
  $totalFeesBilled = 0;
  $totalOutstanding = 0;
  $totalFeesCollected = 0;
  foreach ($validUserIds as $userId) {
    $accountId = $accountToUserMap->search($userId);
    if (!$accountId || !isset($studentClasses[$userId])) continue;

    $classAssignment = $studentClasses[$userId];

    // CRITICAL FIX: Check for null relationships due to deleted records.
    if (!$classAssignment->user || !$classAssignment->class) {
      continue;
    }

    $expected = $classFeeTotals->get($classAssignment->class->id, 0);
    // $balance = $payments->where('account_id', $accountId)->sum('amount');
    $balance = DB::table('transactions')
      ->where('account_id', $accountId)
      ->where('term_id', $termId)
      ->sum('amount');
    $paid = $expected + $balance;

    $totalFeesBilled += $expected;
    $totalOutstanding += $balance;
    $totalFeesCollected += $paid;




    // This array will hold both raw numbers for calculation and names for display.
    $studentMetrics[$userId] = [
      'name'            => $classAssignment->user->name,
      'class_name'      => $classAssignment->class->name,
      'class_id'        => $classAssignment->class->id,
      'raw_expected'    => $expected,
      'raw_paid'        => $paid,
      'raw_outstanding' => $expected - $paid,
    ];
  }

  // ## 5. Prepare Final Data Structures for the View ##

  /*  $totalFeesBilled = collect($studentMetrics)->sum('raw_expected');
  $totalOutstanding = collect($studentMetrics)->sum('raw_outstanding');
  $totalFeesCollected = $totalFeesBilled + $totalOutstanding;  */

  // 5a. High-Level Summary (KPIs)
  $summary = [
    'totalFeesBilled'     => number_format(collect($studentMetrics)->sum('raw_expected')),
    'totalFeesCollected'  => number_format($totalFeesCollected),
    'totalOutstanding'    => number_format(collect($studentMetrics)->sum('raw_outstanding')),
    'totalSchoolPay'      => number_format($payments->where('source', 'SCHOOL_PAY')->sum('amount')),
    'totalManualEntry'    => number_format($payments->where('source', 'MANUAL_ENTRY')->sum('amount')),
    'totalGenerated'      => number_format($bills->where('source', 'GENERATED')->sum('amount')),
  ];

  // 5b. Class-Level Breakdown
  $classBreakdown = collect($studentMetrics)->groupBy('class_name')
    ->map(function ($group) use ($classFeeTotals) {
      $classId = $group->first()['class_id'];
      return [
        'name'                    => $group->first()['class_name'],
        'students'                => $group->count(),
        'fees_bill_per_student'   => number_format($classFeeTotals->get($classId, 0)),
        'billed'                  => number_format($group->sum('raw_expected')),
        'collected'               => number_format($group->sum('raw_paid')),
        'outstanding'             => number_format($group->sum('raw_outstanding')),
      ];
    })->sortBy('name')->values()->all();

  // 5c. Student data, grouped by class ID and sorted descending.
  $studentsByClass = collect($studentMetrics)
    ->groupBy('class_id')
    ->sortByDesc(fn($group, $classId) => $classId);

  // 5d. Assemble all data to be passed to the view.
  $data = [
    'ent'             => $enterprise,
    'term'            => $term,
    'summary'         => $summary,
    'classBreakdown'  => $classBreakdown,
    'studentsByClass' => $studentsByClass,
    'date'            => now()->format('d-M-Y'),
  ];

  // ## 6. Generate and Stream the PDF ##
  $pdf = App::make('dompdf.wrapper');
  $pdf->loadView('reports.school_fees_condensed', $data)->setPaper('a4', 'portrait');

  $report->total_students = collect($studentMetrics)->count();
  $report->expected_fees = collect($studentMetrics)->sum('raw_expected');
  $report->fees_collected_manual_entry = $payments->where('source', 'MANUAL_ENTRY')->sum('amount');
  $report->fees_collected_schoolpay = $payments->where('source', 'SCHOOL_PAY')->sum('amount');
  $report->fees_collected_total = $payments->sum('amount');
  $report->fees_collected_other = $payments->whereNotIn('source', ['MANUAL_ENTRY', 'SCHOOL_PAY'])->sum('amount');
  $report->save();

  $fileName = 'School-Fees-Report-' . str_replace(' ', '-', $term->name) . '.pdf';
  return $pdf->stream($fileName);
});

Route::get('process-transport', function (Request $r) {
  return;
  $subs = TransportSubscription::where([])->get();
  foreach ($subs as $sub) {
    $sub->description = $sub->description . '.';
    $sub->save();
    echo $sub->id . " => " . $sub->description . "<br>";
  }
  die("done");
});
Route::get('send-report-card', function (Request $r) {
  $reportCard = StudentReportCard::find($r->id);
  if ($reportCard == null) {
    return "Report card not found";
  }
  $task = $r->task;
  if ($task == null) {
    return "Task not found";
  }
  //if task not email or sms
  if ($task != 'email' && $task != 'sms') {
    return "Task not found";
  }
  $url = url('storage/files/' . $reportCard->pdf_url);
  $student = $reportCard->owner;
  if ($student == null) {
    return "Student not found";
  }
  $email = $student->email;
  //validate email $email


  try {
    if ($task == 'email') {
      if ($email == null || strlen($email) < 5) {
        return "Email not found";
      }

      $rep = $reportCard->send_mail_to_parent();
      die($rep);
    } else if ($task == 'sms') {

      $rep = $reportCard->send_sms_to_parent();

      /* 
          "current_address" => "Kikumbi"
    "phone_number_1" => null
    "phone_number_2" => null
    "email" => "RauhunKasule3015"
    "nationality" => "Ugandan"
    "religion" => "Islam"
    "spouse_name" => null
    "spouse_phone" => null
    "father_name" => null
    "father_phone" => null
    "mother_name" => "Haitham Mohammed Juma"
    "mother_phone" => "0708608228"
    "languages" => null
    "emergency_person_name" => "Kasuke Joseph"
    "emergency_person_phone" => null
    "national_id_number" => null
    "passport_number" => null
    "tin" => null
      */
      dd($student);
      $phone = $student->phone_number_1;
      if ($phone == null || strlen($phone) < 5) {
        return "Phone number not found";
      }
      //use filter
      if (!filter_var($phone, FILTER_VALIDATE_INT)) {
        return "Phone number not valid";
      }
      $rep = $reportCard->send_sms_to_parent();
      die($rep);
    } else {
      return "Task not found";
    }
  } catch (\Throwable $th) {
    return "Failed to send email because: " . $th->getMessage() . " Email: " . $email;
  }

  dd($email);

  /* 
    "phone_number_1" => null
    "phone_number_2" => null
    "email" => "RauhunKasule3015"
    "nationality" => "Ugandan"
    "religion" => "Islam"
    "spouse_name" => null
    "spouse_phone" => null
    "father_name" => null
    "father_phone" => null
    "mother_name" => "Haitham Mohammed Juma"
    "mother_phone" => "0708608228"
    "languages" => null
    "emergency_person_name" => "Kasuke Joseph"
    "emergency_person_phone" => null
  */

  dd($student->email);
});

Route::get('termly-report', function (Request $r) {
  $termlyReport = TermlyReportCard::find($r->id);
  dd($termlyReport);
  /*
  http://localhost/schools/generate-report-card?id=10963
  */
});

Route::get('/', function (Request $request) {
  $admin = Admin::user();
  $company = \App\Models\Utils::company(); // Get company data for dynamic branding

  //if user already logged in, redirect to dashboard
  if ($admin != null) {
    $dashboard = admin_url('dashboard');
    return redirect($dashboard);
  }

  // If user is not logged in, show landing page
  return view('landing.index', compact('company'));
});

Route::get('/access-system', function () {
  $company = \App\Models\Utils::company();
  return view('landing.access-system', compact('company'));
});

// SEO Routes
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');

// SEO Redirects for common issues
Route::redirect('/home', '/', 301);
Route::redirect('/index', '/', 301);
Route::redirect('/index.php', '/', 301);
Route::redirect('/index.html', '/', 301);
Route::redirect('/login', '/access-system', 301);
Route::redirect('/register', '/access-system', 301);
Route::redirect('/help', '/knowledge-base', 301);
Route::redirect('/support', '/knowledge-base', 301);
Route::redirect('/faq', '/knowledge-base', 301);
Route::redirect('/docs', '/knowledge-base', 301);
Route::redirect('/documentation', '/knowledge-base', 301);

// Knowledge Base public routes
Route::get('/knowledge-base', [KnowledgeBaseController::class, 'index'])->name('knowledge-base.index');
Route::get('/knowledge-base/search', [KnowledgeBaseController::class, 'search'])->name('knowledge-base.search');
Route::get('/knowledge-base/{categorySlug}', [KnowledgeBaseController::class, 'category'])->name('knowledge-base.category');
Route::get('/knowledge-base/{categorySlug}/{articleSlug}', [KnowledgeBaseController::class, 'article'])->name('knowledge-base.article');

Route::get('temp-import', function () {
  // set unlimited time & memory
  set_time_limit(-1);
  set_time_limit(-1);

  //set memory unlimited
  ini_set('memory_limit', '-1');

  //set error reporting
  error_reporting(E_ALL);
  ini_set('display_errors', 1);
  // include necessary classes

  // get table names dynamically
  $enterpriseTable     = (new Enterprise)->getTable();
  $academicClassTable  = (new AcademicClass)->getTable();
  $userTable           = (new User)->getTable();

  // fetch enterprise id=24
  $last_ent = DB::table($enterpriseTable)
    ->where('id', 24)
    ->first();

  // load spreadsheet
  $file = public_path('KAMPALA_INSTITUTE_OF_HEALTH_PROFESSIONALS.xlsx');
  if (! file_exists($file)) {
    return "File not found";
  }
  $reader      = new Xlsx();
  $spreadsheet = $reader->load($file);
  $sheet       = $spreadsheet->getActiveSheet();
  $rows        = $sheet->toArray();

  // preload classes (even if unused)
  $classes = DB::table($academicClassTable)
    ->where('enterprise_id', $last_ent->id)
    ->get();

  $start_from = 0;
  $end_from = 10;
  if (isset($_GET['start_from']) && is_numeric($_GET['start_from'])) {
    $start_from = (int)$_GET['start_from'];
  }
  if ($start_from < 0) {
    $start_from = 0;
  }

  //end_from
  if (isset($_GET['end_from']) && is_numeric($_GET['end_from'])) {
    $end_from = (int)$_GET['end_from'];
  }

  echo "<h3>Processing from row: $start_from to $end_from</h3>";

  $count = 0;
  foreach ($rows as $row) {
    $count++;
    if ($count < 2) {
      continue;
    }
    if ($count > 50001) {
      break;
    }

    if ($count < $start_from) {
      continue;
    }
    if ($count > $end_from) {
      break;
    }

    echo "<hr> Processing row: $count <br>";

    // extract columns
    $first_name       = trim($row[1]);
    $middle_name      = trim($row[2]);
    $last_name        = trim($row[3]);
    $student_account  = $row[4];
    $schoo_pay_code   = $row[7];

    // build full name for error messages
    $name = trim(preg_replace(
      '/\s+/',
      ' ',
      $first_name
        . ($middle_name ? ' ' . $middle_name : '')
        . ($last_name   ? ' ' . $last_name   : '')
    ));

    // base search conditions
    $conds = [
      'first_name'       => $first_name,
      'given_name'       => $last_name,
      'enterprise_id'    => $last_ent->id,
      'has_account_info' => 'No',
      'user_type'        => 'student',
    ];

    // try multiple permutations until we find users
    $users = DB::table($userTable)->where($conds)->get();
    if ($users->count() < 1) {
      // swap first/given
      $conds['first_name'] = $last_name;
      $conds['given_name'] = $first_name;
      $users = DB::table($userTable)->where($conds)->get();
    }
    if ($users->count() < 1) {
      // swap to last_name column if exists
      $conds['last_name']  = $first_name;
      unset($conds['given_name']);
      $users = DB::table($userTable)->where($conds)->get();
    }
    if ($users->count() < 1) {
      // revert and try again
      $conds['first_name'] = $first_name;
      $conds['last_name']  = $last_name;
      $users = DB::table($userTable)->where($conds)->get();
    }
    if ($users->count() < 1) {
      $name = trim(preg_replace(
        '/\s+/',
        ' ',
        $first_name
          . ($middle_name ? ' ' . $middle_name : '')
          . ($last_name   ? ' ' . $last_name   : '')
      ));
      // revert and try again
      $conds['name'] = $name;
      unset($conds['first_name']);
      unset($conds['last_name']);

      $users = DB::table($userTable)->where($conds)->get();
    }

    // no match
    if ($users->count() < 1) {
      echo "<br><span style='background-color: #ffcccc; color: #a94442; padding: 2px 6px; border-radius: 3px;'>"
        . "No user found for: " . htmlspecialchars($name) . ", ROW: " . $count
        . "</span><br>";
      continue;
    }

    // multiple matches
    if ($users->count() > 1) {
      $ids_of_students = $users->map(function ($u) {
        return $u->id . " NAME: " . htmlspecialchars($u->name);
      })->implode(', ');

      echo "<br><span style='background-color: #ffeeba; color: #856404; padding: 2px 6px; border-radius: 3px;'>"
        . "Multiple users found for: " . htmlspecialchars($name)
        . ", ROW: " . $count . ", IDs: " . $ids_of_students
        . "</span><br>";
      continue;
    }

    // exactly one
    $user = $users->first();
    $user = User::find($user->id); // Ensure we have a User model instance
    $user->school_pay_payment_code = $schoo_pay_code;
    $user->has_account_info = 'Yes'; // Mark as having account info
    $user->school_pay_account_id = $student_account;
    $user->save();
    echo "<br><span style='background-color: #d4edda; color: #155724; padding: 2px 6px; border-radius: 3px;'>"
      . "Updated user: " . htmlspecialchars($user->name) . " (ID: " . $user->id . ")"
      . "</span><br>";

    // ... you can still reference $user->id, $user->name, etc.
  }

  return "Done";
});
Route::get('assessment-sheets-generate', [ReportCardsPrintingController::class, 'assessment_sheets_generate']);
Route::get('report-card-printings', [ReportCardsPrintingController::class, 'index']);
Route::get('report-card-individual-printings', [ReportCardsPrintingController::class, 'report_card_individual_printings']);
Route::get('data-import', [ReportCardsPrintingController::class, 'data_import']);
Route::get('process-termly-school-fees-balancings', [MainController::class, 'process_termly_school_fees_balancings']);
Route::get('clear', function () {

  Artisan::call('config:clear');
  Artisan::call('cache:clear');
  Artisan::call('route:clear');
  Artisan::call('view:clear');
  Artisan::call('optimize');
  Artisan::call('cache:clear');
  Artisan::call('view:clear');
  Artisan::call('route:clear');
  Artisan::call('config:clear');
  Artisan::call('optimize:clear');
  exec('composer dump-autoload -o');
  return Artisan::output();
});
//migration
Route::get('send-message', function (Request $request) {
  $directMessage = DirectMessage::find($request->id);
  $directMessage->status = 'Pending';


  try {
    DirectMessage::send_message($directMessage);
  } catch (\Throwable $th) {
    return "Failed to send message because: " . $th->getMessage();
  }

  $directMessage = DirectMessage::find($request->id);

  return <<<EOF
  <div style="font-family: Arial, sans-serif; margin: 20px;">
    <h2>Message Status</h2>
    <p><strong>Status:</strong> {$directMessage->status}</p>
    <p><strong>Error:</strong> {$directMessage->error_message_message}</p>
    <p><strong>Receiver:</strong> {$directMessage->receiver_number}</p>
    <p><strong>Message:</strong> {$directMessage->message_body}</p>
    <p><strong>ID:</strong> {$directMessage->id}</p>
  </div>
  EOF;
});

//migration
Route::get('sms-test', function () {

  $lastDirectMessage = DirectMessage::where([])->orderBy('id', 'desc')->first();
  $lastDirectMessage->status = 'Pending';
  DirectMessage::send_message($lastDirectMessage);
  die("done");
  dd($lastDirectMessage);
  /* 
      "id" => 19552
    "created_at" => "2025-10-25 00:36:50"
    "updated_at" => "2025-10-25 00:37:44"
    "enterprise_id" => 19
    "bulk_message_id" => 84
    "administrator_id" => 15437
    "receiver_number" => ""
    "message_body" => """
      BRIGHT FUTURE SS KALIRO.
      Dear Parent, you are invited to attend the ACADEMIC VISITATION on sat 25/10/25. this will be deadline for fees clearance.
      Admin. 0781917795
      """
    "status" => "Failed"
    "is_scheduled" => "No"
    "delivery_time" => "2025-10-24 16:36:50"
    "error_message_message" => "Message sending failed. Info:  URL: https://www.socnetsolutions.com/projects/bulk/amfphp/services/blast.php?spname=mubaraka&sppass=Mub4r4k4@2025&type=json&numbe ▶"
    "response" => null
    "balance" => 0
    "STUDENT_NAME" => "CHEPTOEK SHAKUWA"
    "PARENT_NAME" => "Parent NAMULEMO"
    "STUDENT_CLASS" => null
    "TEACHER_NAME" => "CHEPTOEK SHAKUWA"
  ]
  */

  $url = "https://www.socnetsolutions.com/projects/bulk/amfphp/services/blast.php?username=mubaraka&passwd=Mub4r4k4@2025";
  //$m->receiver_number = '+256706638494';
  $url .= "&msg=" . trim("Hello muhindo.");
  $url .= "&numbers=" . '+256706638494';

  try {
    $result = file_get_contents($url, false, stream_context_create([
      'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        /* 'content' => json_encode($m), */
      ],
    ]));
    echo "<pre>";
    print_r($result);
    echo "</pre>";
  } catch (\Throwable $th) {
    echo "failed";
    dd($th);
  }
});
Route::get('migrate', function () {
  Artisan::call('migrate');
  return Artisan::output();
});
Route::get('roll-calling-close-session', function (Request $request) {
  $session = Session::find($request->roll_call_session_id);
  if ($session == null) {
    return "Session not found";
  }
  $session->is_open = 0;
  // $session->process_attendance();
  $session->save();
  return redirect(admin_url('sessions'));
});
Route::get('roll-calling', function (Request $request) {
  if (isset($request->roll_call_session_id)) {
    $session = Session::find($request->roll_call_session_id);
    if ($session == null) {
      return "Session not found";
    }
    session(['roll_call_session_id' => $session->id]);
    return redirect('roll-calling');
  }
  $session = Session::find(session('roll_call_session_id'));
  if ($session == null) {
    return "No active session";
  }
  return view('roll-calling.roll-calling', [
    'session' => $session,
  ]);
});

Route::get('process-fees', function (Request $request) {
  return;
  $ent_id = 19;
  $recs = StudentHasFee::where([
    'enterprise_id' => $ent_id,
  ])->delete();
  echo "Fees Deleted: " . ($recs) . "<br>";
  Transaction::where([
    'enterprise_id' => $ent_id,
  ])->delete();
  echo "Transactions Deleted: " . ($recs) . "<br>";
  $subs = ServiceSubscription::where([
    'enterprise_id' => $ent_id,
  ])->delete();
  echo "Subscriptions Deleted: " . ($subs) . "<br>";

  set_time_limit(-1);
  ini_set('memory_limit', '-1');
  $fees = AcademicClassFee::where([
    'enterprise_id' => $ent_id,
  ])->get();
  foreach ($fees as $x) {
    AcademicClassFee::process_bill($x);
  }
});
Route::get('process-theology-report-cards', function (Request $request) {
  $termlyReport = TheologyTermlyReportCard::find($request->id);

  $grading_scale = $termlyReport->grading_scale;
  $ranges = $grading_scale->grade_ranges;
  if (is_array($termlyReport->classes)) {
    $classes = $termlyReport->classes;
    foreach ($classes as $key => $class_id) {
      $class = TheologyClass::find($class_id);

      $student_reports = TheologryStudentReportCard::where([
        'enterprise_id' => $termlyReport->enterprise_id,
        'term_id' => $termlyReport->term_id,
        'theology_class_id' => $class_id,
        'theology_termly_report_card_id' => $termlyReport->id,
      ])->get();
      foreach ($student_reports as $student_report) {
        // dd($student_report);
        //process marks
        $marks = TheologyMarkRecord::where([
          'enterprise_id' => $termlyReport->enterprise_id,
          'term_id' => $termlyReport->term_id,
          'theology_class_id' => $class_id,
          'theology_termly_report_card_id' => $termlyReport->id,
          'administrator_id' => $student_report->student_id,
        ])->get();
        $student_report->average_aggregates = 0;
        $number_of_exams = 0;
        $student_report->total_aggregates = 0;
        $student_report->total_marks = 0;
        $student_report->total_students = $student_reports->count();
        foreach ($marks as $mark) {
          $number_of_exams = 0;
          $total_score = 0;
          if ($termlyReport->reports_include_bot == 'Yes') {
            $number_of_exams++;
            $total_score += $mark->bot_score;
            $mark->bot_grade = Utils::generateAggregates($grading_scale, $mark->bot_score)['aggr_name'];
          }
          if ($termlyReport->reports_include_mot == 'Yes') {
            $number_of_exams++;
            $total_score += $mark->mot_score;
            $mark->mot_grade = Utils::generateAggregates($grading_scale, $mark->mot_score)['aggr_name'];
          }
          if ($termlyReport->reports_include_eot == 'Yes') {
            $number_of_exams++;
            $total_score += $mark->eot_score;
            $mark->eot_grade = Utils::generateAggregates($grading_scale, $mark->eot_score)['aggr_name'];
          }
          if ($number_of_exams < 1) {
            throw new Exception("You must include at least one exam.", 1);
          }

          if ($number_of_exams == 1) {
            $average_mark = $total_score;
          } else {
            $average_mark = ((int)(($total_score) / $number_of_exams));
          }
          $mark->total_score = $total_score;
          $mark->total_score_display = $average_mark;
          $mark->remarks = Utils::get_automaic_mark_remarks($mark->total_score_display);

          $mark->aggr_value = null;
          $mark->aggr_name = null;
          $rangeFound = false;
          foreach ($ranges as $range) {
            if ($mark->total_score_display >= $range->min_mark && $mark->total_score_display <= $range->max_mark) {
              $mark->aggr_value = $range->aggregates;
              $mark->aggr_name = $range->name;
              $student_report->average_aggregates += $mark->aggr_value;
              $student_report->total_aggregates += $mark->aggr_value;
              $rangeFound = true;
              $student_report->total_marks += $mark->total_score_display;
              echo "$mark->id. " . $mark->total_score_display . " => " . $mark->aggr_name . "<br>";
              break;
            }
          }
          if (!$rangeFound) {
            throw new Exception("No range found for mark: " . $mark->total_score_display, 1);
          }
          $mark->save();
        }

        echo "$class->name. $student_report->id. {$student_report->owner->name}.   TOTAL MARKS: " . $student_report->total_marks . " => AGGR: " . $student_report->average_aggregates . "<br><hr>";
      }
      /* 
      die("done");

          "id" => 9639
    "created_at" => "2024-07-20 01:20:39"
    "updated_at" => "2024-08-21 13:39:24"
    "enterprise_id" => 7
    "academic_year_id" => 14
    "term_id" => 41
    "student_id" => 12839
    "theology_class_id" => 63
    "theology_termly_report_card_id" => 14
    "total_students" => 0
    "total_aggregates" => 6
    "total_marks" => 372.0
    "position" => 0
    "class_teacher_comment" => "MAWANDA RIZIK's academic performance needs significant improvement. she should work closely with teachers to address her weaknesses."
    "head_teacher_comment" => null
    "class_teacher_commented" => 0
    "head_teacher_commented" => 0
    "average_aggregates" => 6.0
    "grade" => "1"
    "stream_id" => null


      ===
    
    
      "id" => 14
    "created_at" => "2024-07-16 23:36:01"
    "updated_at" => "2024-08-21 15:51:43"
    "grading_scale_id" => 14
    "enterprise_id" => 7
    "academic_year_id" => 14
    "term_id" => 41
    "has_beginning_term" => null
    "has_mid_term" => null
    "has_end_term" => null
    "report_title" => "END OF TERM 2 REPORT CARD 20241........................1"
    "do_update" => 1
    "generate_marks" => "No"
    "delete_marks_for_non_active" => "No"
    "bot_max" => 100
    "mot_max" => 100
    "eot_max" => 100
    "display_bot_to_teachers" => "Yes"
    "display_mot_to_teachers" => "No"
    "display_eot_to_teachers" => "No"
    "display_bot_to_others" => "Yes"
    "display_mot_to_others" => "No"
    "display_eot_to_others" => "Yes"
    "can_submit_bot" => "Yes"
    "can_submit_mot" => "No"
    "can_submit_eot" => "No"
    "reports_generate" => "No"
    "reports_delete_for_non_active" => "No"
    "reports_include_bot" => "Yes"
    "reports_include_mot" => "No"
    "reports_include_eot" => "No"
    "reports_template" => "Template_4"
    "reports_who_fees_balance" => "No"
    "reports_display_report_to_parents" => "No"
    "hm_communication" => "Whichever results you get, there's always room for improvement. Continue working harder."
    "classes" => "["63","62","61","60","59","58","57"]"
    "generate_class_teacher_comment" => "No"
    "generate_head_teacher_comment" => "No"
    "generate_positions" => "No"
    "display_positions" => "Yes"
    "bottom_message" => null
    "positioning_type" => "Stream"
    "positioning_method" => "Specific"
    "positioning_exam" => "bot"
    "generate_marks_for_classes" => "["63","62","61","60","59","58","57"]"
    "bot_name" => "SET 1"
    "mot_name" => "SET 2"
    "eot_name" => "SET 3"
*/
    }
  }
  die("done");
});
Route::get('test-1', function (Request $request) {
  $ent = Enterprise::find(7);
  $x = null;

  $ent->name = $x ?? null;

  die($ent->name);

  $ents = Enterprise::where([
    'type' => 'Primary',
  ])->get();
  //set unlimited time
  set_time_limit(-1);


  foreach ($ents as $key => $ent) {
    $active_term = $ent->active_term();
    $classes = TheologyClass::where([
      'enterprise_id' => $ent->id,
      'academic_year_id' => $active_term->academic_year_id,
    ])->get();
    foreach ($classes as $key => $class) {
      $studentHasClasses = StudentHasTheologyClass::where([
        'enterprise_id' => $ent->id,
        'theology_class_id' => $class->id,
      ])
        ->orderBy('id', 'desc')
        ->get();
      foreach ($studentHasClasses as $key => $studentHasClass) {
        $stud = Administrator::find($studentHasClass->administrator_id);
        if ($stud == null) {
          continue;
        }
        /* $stream = TheologyStream::find($studentHasClass->theology_stream_id);
        if ($stream == null) {
          continue;
        } 
        if ($stud->theology_stream_id != null && strlen($stud->theology_stream_id) > 0) {
          if ($stud->theology_stream_id == $studentHasClass->theology_stream_id) {
            continue;
          }
        } */

        $stud->theology_stream_id = $studentHasClass->theology_stream_id;
        $stud->current_theology_class_id = $class->id;

        $stud->save();
        echo "$stud->id. ==> $stud->name!<br>";
      }
    }
  }
  die("done");

  dd($ents);

  $pos = 110;
  $id = 9710;

  if (isset($request->pos)) {
    $pos = $request->pos;
  }
  if (isset($request->id)) {
    $id = $request->id;
  }
  $rep = TheologryStudentReportCard::find($id);
  $rep->average_aggregates = $pos;
  // $rep->class_teacher_comment .= '.';
  $rep->save();

  $rep = TheologryStudentReportCard::find($id);
  echo $rep->owner->name . ",  =>$pos<= AGRR: " . $rep->average_aggregates . "<br>";
});
Route::get('app', function (Request $request) {
  return view('app');
});
Route::get('test', function (Request $request) {

  return view('test');
  $url = "https://www.socnetsolutions.com/projects/bulk/amfphp/services/blast.php?username=mubaraka&passwd=Mub4r4k4@2025";
  //$m->receiver_number = '+256706638494';
  $url .= "&msg=" . trim('$m->message_body');
  $url .= "&numbers=" . '+256783204665';

  try {
    $result = file_get_contents($url, false, stream_context_create([
      'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        /* 'content' => json_encode($m), */
      ],
    ]));
    dd($result);
  } catch (\Throwable $th) {
    dd($th);
  }

  die("done");

  $ent = Enterprise::find(7);
  $ent = Utils::fetchDataFromRequest($ent, $request);
  dd($ent->name);

  dd($request->all());

  die("tome to test");

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

    // Handle inventory management
    $inventoryMode = $rep->to_be_managed_by_inventory ?? 'No';
    if ($inventoryMode === 'Yes') {
      $sub->to_be_managed_by_inventory = 'Yes';
      // Don't set items_to_be_offered here — we'll create tracking records manually after save
    } else {
      $sub->to_be_managed_by_inventory = 'No';
    }

    $sub->is_service_offered = 'No';
    $sub->is_completed = 'No';
    $error_text = null;
    try {
      $sub->save();
      
      // Create ServiceItemToBeOffered records from batch items (hasMany relationship)
      if ($inventoryMode === 'Yes') {
        $batchItems = $rep->batchItems;
        if ($batchItems && $batchItems->count() > 0) {
          foreach ($batchItems as $batchItem) {
            if (empty($batchItem->stock_item_category_id)) continue;
            $exists = \App\Models\ServiceItemToBeOffered::where('service_subscription_id', $sub->id)
              ->where('stock_item_category_id', $batchItem->stock_item_category_id)
              ->exists();
            if (!$exists) {
              \App\Models\ServiceItemToBeOffered::create([
                'service_subscription_id' => $sub->id,
                'stock_item_category_id' => $batchItem->stock_item_category_id,
                'quantity' => $batchItem->quantity ?? 1,
                'is_service_offered' => 'No',
                'user_id' => $user->id,
                'enterprise_id' => $rep->enterprise_id,
              ]);
            }
          }
        }
      }
      
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

Route::get('meal-cards', function (Request $r) {
  if (!isset($r->type)) {
    return "ID not set";
  }
  set_time_limit(-1);
  //set memory limit
  ini_set('memory_limit', '-1');

  $idCard = SchoolFeesDemand::find($_GET['id']);
  $pdf = App::make('dompdf.wrapper');
  $ent = Enterprise::find($idCard->enterprise_id);
  $recs = $idCard->get_meal_card_records();


  $IS_GATE_PASS = false;
  if (isset($r->type) && $r->type == 'GATE_PASS') {
    $IS_GATE_PASS = true;
  }

  /* 
  foreach ($recs as $key => $class) {
    foreach ($class as $key => $student) {
      echo  $student->id . "<br>" . $student->owner->name . "<br>" . $student->balance . "<hr>"; 
    }
  }
  die("done");
 */

  /* 
         $form->radio('has_range', 'Has Range')->options([
            'Yes' => 'Yes',
            'No' => 'No',
        ])->default('No')->required()
            ->when('Yes', function (Form $form) {
                $form->decimal('min_range', 'Min Range')->rules('required');
                $form->decimal('max_range', 'Max Range')->rules('required');
            })
            ->rules('required');
 */

  $min = 0;
  $max  = 100000;

  if ($idCard->has_range == 'Yes') {
    $min = $idCard->min_range;
    $max = $idCard->max_range;
  }

  if (isset($_GET['html'])) {
    return view('fees.meal-cards', [
      'recs' => $recs,
      'ent' => $ent,
      'type' => $r->type,
      'demand' => $idCard,
      'IS_GATE_PASS' => $IS_GATE_PASS,
      'min' => $min,
      'max' => $max,
    ]);
  }

  try {
    $pdf->loadHTML(view('fees.meal-cards', [
      'recs' => $recs,
      'ent' => $ent,
      'type' => $r->type,
      'demand' => $idCard,
      'IS_GATE_PASS' => $IS_GATE_PASS,
      'min' => $min,
      'max' => $max,
    ]));
  } catch (\Throwable $th) {
    echo $th->getMessage();
    echo "<hr>";
    throw $th;
  }

  // $pdf->render();
  // return $pdf->stream();

  $rand = rand(1, 100000) . time();

  $store_file_path = public_path('storage/files/' . $idCard->id . '-' . $rand . '.pdf');

  //check if file exists and delete
  if (file_exists($idCard->file_link)) {
    unlink($idCard->file_link);
  }
  file_put_contents($store_file_path, $pdf->output());
  $idCard->pdf_generated = 'Yes';
  $idCard->file_link = $store_file_path;
  if (isset($r->type)) {
    $idCard->file_type = $r->type;
  }
  $idCard->save();
  //reutn the link as text
  $file_url = url('storage/files/' . $idCard->id . '-' . $rand . '.pdf');
  $RESP = <<<EOD
  GENERATED: <a href='$file_url' target='_blank'>Download PDF File</a>
  <br/>OR COPY THIS LINK:
  <input type="text" id="clipLink" value="$file_url" readonly style="width: 500px;" />
  <button onclick="copyLink()">Copy Link</button>
  <script>
    function copyLink() {
      var copyText = document.getElementById("clipLink");
      copyText.select();
      document.execCommand("copy");
      alert("Link copied to clipboard");
    }
  </script>
  EOD;
  return $RESP;
  return redirect('identification-cards-print?id=' . $idCard->id . '&rand=' . $rand);
});

Route::get('generate-demand-notice', function () {
  //set unlimited time
  set_time_limit(-1);
  $idCard = SchoolFeesDemand::find($_GET['id']);
  $pdf = App::make('dompdf.wrapper');
  $ent = Enterprise::find($idCard->enterprise_id);
  $recs = $idCard->get_demand_records();

  if (!isset($_GET['html'])) {
    $pdf->loadHTML(view('fees.demand-notice', [
      'recs' => $recs,
      'ent' => $ent,
      'demand' => $idCard
    ]));
    $pdf->render();
    return $pdf->stream();
  }

  return view('fees.demand-notice', [
    'recs' => $recs,
    'ent' => $ent,
    'demand' => $idCard
  ]);

  $store_file_path = public_path('storage/files/' . $idCard->id . '.pdf');
  file_put_contents($store_file_path, $output);
  $idCard->pdf_generated = 'Yes';
  $idCard->file_link = $store_file_path;
  $idCard->save();
  //redirect to the print
  $rand = rand(1, 100000) . time();
  return redirect('identification-cards-print?id=' . $idCard->id . '&rand=' . $rand);
});


Route::get('bulk-photo-uploads-process', function () {
  $id = ($_GET['id']);
  $class_error = " background-color: #ff0000; color: #fff; padding: 0px; margin: 0px; ";
  $class_success = " background-color: green; color: #fff; padding: 0px; margin: 0px; ";
  $blk = BulkPhotoUpload::where([
    'id' => $id,
  ])->first();
  if ($blk == null) {
    return "Bulk Upload not found";
  }
  $ent = Enterprise::find($blk->enterprise_id);
  if ($ent == null) {
    return "Enterprise not found";
  }


  if ($blk->file_type == 'images') {
    //if not array
    if (!is_array($blk->images)) {
      return "File path is not an array";
    }

    //if empty
    if (count($blk->images) < 1) {
      return "No files to process";
    }


    //for images
    foreach ($blk->images as $key => $file) {
      $path = public_path('storage/' . $file);
      if (!file_exists($path)) {
        echo '<p style="background-color: #ff0000; color: #fff; padding: 5px; margin: 5px;">File not found: ' . htmlspecialchars($file) . '</p>';
        continue;
      }

      $ext = pathinfo($file, PATHINFO_EXTENSION);
      $new_name = Utils::get_unique_text() . '.' . $ext;
      $destination_path = public_path('storage/images/' . $new_name);
      $moved = rename($path, $destination_path);
      if (!$moved) {
        echo '<p style="background-color: #ff0000; color: #fff; padding: 5px; margin: 5px;">Failed to move file: ' . htmlspecialchars($file) . '</p>';
        continue;
      }
      $photo = new BulkPhotoUploadItem();
      $photo->enterprise_id = $ent->id;
      $photo->bulk_photo_upload_id = $blk->id;
      $photo->academic_class_id = $blk->academic_class_id;
      $photo->new_image_path = 'images/' . $new_name;
      $photo->file_name = $file;
      $photo->naming_type = $blk->naming_type;
      $photo->error_message = null;
      $photo->status = 'Pending';
      try {
        $photo->save();
        //echo context
        echo '<p style="background-color: green; color: #fff; padding: 5px; margin: 5px;">' . $file . ' saved as ' . $new_name . '</p>';
      } catch (\Throwable $th) {
        echo '<p style="background-color: #ff0000; color: #fff; padding: 5px; margin: 5px;">Failed to save file: ' . htmlspecialchars($file) . '</p>';
        continue;
      }
    }
    $blk->status = 'Completed';
    $blk->save();

    $stats_success = BulkPhotoUploadItem::where([
      'bulk_photo_upload_id' => $blk->id,
      'status' => 'Success',
    ])->count();

    $stats_failed = BulkPhotoUploadItem::where([
      'bulk_photo_upload_id' => $blk->id,
      'status' => 'Failed',
    ])->count();

    $stats_pending = BulkPhotoUploadItem::where([
      'bulk_photo_upload_id' => $blk->id,
      'status' => 'Pending',
    ])->count();


    $items = BulkPhotoUploadItem::where([
      'bulk_photo_upload_id' => $blk->id,
    ])->get();

    foreach ($items as $key => $item) {
      if ($item->status == 'Success') {
        $sudent = Administrator::find($item->student_id);
        if ($sudent == null) {
          $item->status = 'Failed';
          $item->error_message = 'Student not found';

          $item->save();
          //error display
          echo '<p style="background-color: #ff0000; color: #fff; padding: 0px; margin: 0px;">Failed to process: Student not found in the system.</p>';
          continue;
        }
        //display success message
        echo '<p style="background-color: green; color: #fff; padding: 0px; margin: 0px;">Photo successfully updated for student: ' . $sudent->name . '</p>';
        continue;
      }


      $student = $item->get_student();

      if ($student != null) {
        $old = $student->avatar;
        $old_explode = explode('/', $old);
        $old_file_name = end($old_explode);

        if ($old_file_name != 'user.jpeg') {
          $old = public_path('storage/' . $old);
          if (file_exists($old)) {
            unlink($old);
          }
        }

        $item->student_id = $student->id;
        $item->error_message = null;
        $item->status = 'Success';
        $item->save();
        $student->avatar = $item->new_image_path;
        $student->save();
        //success display
        echo '<p style="background-color: green; color: #fff; padding: 0px; margin: 0px;">Photo successfully updated for student: ' . $student->name . '</p>';
      } else {
        $item->status = 'Failed';
        $item->error_message = 'Student not found';
        $item->save();
        //error display
        echo '<p style="background-color: #ff0000; color: #fff; padding: 0px; margin: 0px;">Failed to process: Student not found in the system. File: ' . $item->file_name . '</p>';
      }
    }


    echo '<p style="background-color: green; color: #fff; padding: 5px; margin: 5px;">Success: ' . $stats_success . '</p>';
    echo '<p style="background-color: #ff0000; color: #fff; padding: 5px; margin: 5px;">Failed: ' . $stats_failed . '</p>';
    echo '<p style="background-color: #0000ff; color: #fff; padding: 5px; margin: 5px;">Pending: ' . $stats_pending . '</p>';


    return "done";
  }

  $file_path = public_path('storage/' . $blk->file_path);
  if (!file_exists($file_path)) {
    return "File not found";
  }
  //size of zip in mb
  $size = filesize($file_path);
  $size = $size / 1024 / 1024;
  $size = number_format($size, 2);
  $blk->file_name = $size;
  $blk->save();




  //time to unzip and get images in file

  //create_temp_dir

  //check if directory was created

  if ($blk->status != 'Completed') {
    try {
      $temp_dir_name = 'temp_' . Utils::get_unique_text();
      $temp_dir = public_path('storage/' . $temp_dir_name);
      mkdir($temp_dir);

      if (!file_exists($temp_dir)) {
        return "Failed to create temp directory";
      }
      $zip = new \ZipArchive();
      $zip->open($file_path);
      $zip->extractTo($temp_dir);
      $zip->close();
      $blk->status = 'Completed';
      $blk->error_message = $temp_dir;
      $blk->save();
    } catch (\Throwable $th) {
      return "Failed to extract zip file because: " . $th->getMessage();
    }
  }
  $temp_dir_name = $blk->error_message;
  //check if $blk->error_messag is directory'
  if (is_dir($blk->error_message)) {

    //get all files in the directory
    $files = scandir($blk->error_message);

    $count = 0;
    $success_count = 0;

    foreach ($files as $file) {

      if ($file == '.' || $file == '..') {
        continue;
      }
      $count++;
      $path = $temp_dir_name . '/' . $file;
      $file_exists = file_exists($path);
      if (!$file_exists) {
        echo '<p style="' . $class_error . '">' . $count . '. ' . $file . ' SKIPPED BECAUSE: ' . $path . ' does not exist.</P>';
        continue;
      }
      $file_size = filesize($path);
      $file_size = $file_size / 1024 / 1024;
      $file_size = number_format($file_size, 2);

      if ($file_size > 1) {
        $thumb = Utils::create_thumbnail($path);
        if (file_exists($thumb)) {
          $path = $thumb;
        }
      }


      //ceck if file is exist
      $file_exists = file_exists($path);
      if (!$file_exists) {
        echo '<p style="' . $class_error . '">' . $count . '. ' . $file . ' SKIPPED BECAUSE: ' . $path . ' does not exist.</P>';
        continue;
      }
      //last of explode of path by /
      $explode = explode('/', $path);
      $file_name = end($explode);
      $images_folder = public_path('storage/images');
      $ext = pathinfo($file_name, PATHINFO_EXTENSION);
      $new_name = Utils::get_unique_text() . '.' . $ext;
      $destination_path = $images_folder . '/' . $new_name;
      //move
      try {
        $moved = rename($path, $destination_path);
      } catch (\Throwable $th) {
        echo '<p style="' . $class_error . '">' . $count . '. ' . $file . ' SKIPPED BECAUSE: ' . $th->getMessage() . '</P>';
        continue;
      }


      $photo = new BulkPhotoUploadItem();
      $photo->enterprise_id = $ent->id;
      $photo->bulk_photo_upload_id = $blk->id;
      $photo->academic_class_id = $blk->academic_class_id;
      $photo->new_image_path = 'images/' . $new_name;
      $photo->file_name = $file_name;
      $photo->naming_type = $blk->naming_type;
      $photo->error_message = null;
      $photo->status = 'Pending';
      try {
        $photo->save();
      } catch (\Throwable $th) {
        echo '<p style="' . $class_error . '">' . $count . '. ' . $file . ' SKIPPED BECAUSE: ' . $th->getMessage() . '</P>';
        continue;
      }
    }
  }

  //check if folder exists
  if (is_dir($temp_dir_name)) {
    array_map('unlink', glob("$temp_dir_name/*.*"));
    rmdir($temp_dir_name);
  }


  $items = BulkPhotoUploadItem::where([
    'bulk_photo_upload_id' => $blk->id,
  ])->get();

  foreach ($items as $key => $item) {
    if ($item->status == 'Success') {
      $sudent = Administrator::find($item->student_id);
      if ($sudent == null) {
        $item->status = 'Failed';
        $item->error_message = 'Student not found';

        $item->save();
        //error display
        echo '<p style="background-color: #ff0000; color: #fff; padding: 0px; margin: 0px;">Failed to process: Student not found in the system.</p>';
        continue;
      }
      //display success message
      echo '<p style="background-color: green; color: #fff; padding: 0px; margin: 0px;">Photo successfully updated for student: ' . $sudent->name . '</p>';
      continue;
    }


    $student = $item->get_student();

    if ($student != null) {
      $old = $student->avatar;
      $old_explode = explode('/', $old);
      $old_file_name = end($old_explode);

      if ($old_file_name != 'user.jpeg') {
        $old = public_path('storage/' . $old);
        if (file_exists($old)) {
          unlink($old);
        }
      }

      $item->student_id = $student->id;
      $item->error_message = null;
      $item->status = 'Success';
      $item->save();
      $student->avatar = $item->new_image_path;
      $student->save();
      //success display
      echo '<p style="background-color: green; color: #fff; padding: 0px; margin: 0px;">Photo successfully updated for student: ' . $student->name . '</p>';
    } else {
      $item->status = 'Failed';
      $item->error_message = 'Student not found';
      $item->save();
      //error display
      echo '<p style="background-color: #ff0000; color: #fff; padding: 0px; margin: 0px;">Failed to process: Student not found in the system. File: ' . $item->file_name . '</p>';
    }
  }

  $stats = BulkPhotoUploadItem::where([
    'bulk_photo_upload_id' => $blk->id,
  ])->groupBy('status')->select('status', DB::raw('count(*) as total'))->get();
  foreach ($stats as $key => $stat) {
    echo '<p style="background-color: #000; color: #fff; padding: 0px; margin: 0px;">' . $stat->status . ': ' . $stat->total . '</p>';
  }
  $failed = BulkPhotoUploadItem::where([
    'bulk_photo_upload_id' => $blk->id,
    'status' => 'Failed',
  ])->get();
  foreach ($failed as $key => $fail) {
    echo '<p style="background-color: #ff0000; color: #fff; padding: 0px; margin: 0px;">File: ' . $fail->file_name . ' - ' . $fail->error_message . '</p>';
  }

  return "Done";
});

//make bulk-photo-upload-item-process
Route::get('bulk-photo-upload-item-process', function () {
  $id = ($_GET['id']);
  $item = BulkPhotoUploadItem::find($id);
  if ($item == null) {
    return "Item not found";
  }
  $ent = Enterprise::find($item->enterprise_id);
  if ($ent == null) {
    return "Enterprise not found";
  }
  $student = $item->get_student();
  if ($student == null) {
    $item->status = 'Failed';
    $item->error_message = 'Student not found';
    $item->save();
    return "Student not found";
  }
  $old = $student->avatar;
  $old_explode = explode('/', $old);
  $old_file_name = end($old_explode);

  if ($old_file_name != 'user.jpeg') {
    $old = public_path('storage/' . $old);
    if (file_exists($old)) {
      unlink($old);
    }
  }

  $item->student_id = $student->id;
  $item->error_message = null;
  $item->status = 'Success';
  $item->save();
  $student->avatar = $item->new_image_path;
  $student->save();
  return "Success";
});

Route::get('photos-zip-generation', function () {
  $idCard = DataExport::find($_GET['id']);
  $ent = Enterprise::find($idCard->enterprise_id);
  $users = $idCard->get_users();

  //if $users is empty
  if (count($users) < 1) {
    return "No users found";
  }

  $count = 0;
  $pics_links = [];
  $class_error = " background-color: #ff0000; color: #fff; padding: 0px; margin: 0px; ";
  $class_success = " background-color: green; color: #fff; padding: 0px; margin: 0px; ";
  $success_count = 0;
  $zip = new \ZipArchive();
  $zip_file = public_path('storage/files/' . $idCard->id . '.zip');
  $zip->open($zip_file, \ZipArchive::CREATE);

  foreach ($users as $key => $user) {
    $count++;
    $segs = explode('/', $user->avatar);
    if (count($segs) < 1) {
      echo '<p style="' . $class_error . '">' . $count . '. ' . $user->name . ' SKIPPED BECAUSE: ' . $user->avatar . ' is empty</P>';
      continue;
    }
    $last_seg = end($segs);
    if (strlen($last_seg) < 4) {
      echo '<p style="' . $class_error . '">' . $count . '. ' . $user->name . ' SKIPPED BECAUSE: ' . $user->avatar . ' is empty.</P>';
      continue;
    }

    //if user.jpeg
    if ($last_seg == 'user.jpeg') {
      echo '<p style="' . $class_error . '">' . $count . '. ' . $user->name . ' SKIPPED BECAUSE: ' . $user->avatar . ' is default.</P>';
      continue;
    }

    $path = public_path('storage/images/' . $last_seg);
    $file_exists = file_exists($path);
    if (!$file_exists) {
      echo '<p style="' . $class_error . '">' . $count . '. ' . $user->name . ' SKIPPED BECAUSE: ' . $path . ' does not exist.</P>';
      continue;
    }
    $url = url('storage/images/' . $last_seg);
    echo '<p style="' . $class_success . '">' . $count . '. ' . $user->name . ', SUCCESS, ADDED TO ZIP. <a href="' . $url . '" target="_blank">View Image</a></P>';
    $pics_links[] = $path;
    $success_count++;
    $zip_photo_name = $user->id . '-';

    if ($user->school_pay_account_id != null && strlen($user->school_pay_account_id) > 3) {
      $zip_photo_name .= $user->school_pay_account_id . '-';
    } else if ($user->school_pay_payment_code != null && strlen($user->school_pay_payment_code) > 3) {
      $zip_photo_name .= $user->school_pay_payment_code . '-';
    }
    $zip_photo_name .= $user->name . '-';
    $zip_photo_name .= $last_seg;
    $zip->addFile($path, $zip_photo_name);
    if ($count > 5) {
      // break;
    }
  }
  $zip->close();
  echo '<p style="background-color: #000; color: #fff; padding: 0px; margin: 0px;">TOTAL: ' . $count . ', SUCCESS: ' . $success_count . '</P>';

  $zip_url = url('storage/files/' . $idCard->id . '.zip');
  $idCard->pdf_generated = 'Yes';

  //check if file exists
  if (!file_exists($zip_file)) {
    return "ZIP File not found fro " . $zip_file;
  }

  //zip file size
  $size = filesize($zip_file);
  //convert to mb
  $size = $size / 1024 / 1024;
  //to 2 decimal places
  $size = number_format($size, 2);
  $idCard->template = $size;
  $idCard->file_link = 'files/' . $idCard->id . '.zip';
  $idCard->save();
  return "<br><br><a 
    style='background-color: #000; color: #fff; padding: 10px; margin: 10px; text-decoration: none; border-radius: 5px;'
   href='$zip_url' target='_blank'>Download Photos ZIP File ($size MB)</a><br><br><br><br><br><br>";
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
Route::get('/gen-list', function () {
  die(Gen::find($_GET['id'])->make_list());
})->name("gen-list");


Route::get('create-streams', [Utils::class, 'create_streams']);
Route::get('generate-variables', [MainController::class, 'generate_variables']);
Route::get('process-photos', [MainController::class, 'process_photos']);
Route::get('student-data-import', [MainController::class, 'student_data_import']);
Route::get('prepare-things', [Utils::class, 'prepare_things']);
Route::get('generate-report-card', function () {
  $rep = StudentReportCard::find($_GET['id']);
  if ($rep == null) {
    throw new \Exception("Report not found");
  }
  $rep->download_self();
  $url = url('storage/files/' . $rep->pdf_url . '?rand=' . rand(1, 100000));
  return redirect($url);


  echo '<h1>Generated Successfully</h1>';
  echo "<a href='$url' target='_blank'>Download</a>";
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
  ])->orderBy('week', 'asc')->orderBy('period', 'asc')->get();
  $pdf = App::make('dompdf.wrapper');
  $class = AcademicClass::find($sub->academic_class_id);
  $pdf->setPaper('A4', 'landscape');
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

Route::get('university-programmes-fees-structure-all', function (Request $request) {
  $ent = Admin::user()->ent;
  $programmes = UniversityProgramme::where('enterprise_id', $ent->id)
    ->orderBy('name')
    ->get();

  // render blade to HTML
  $html = view('print.university-programmes-fees-structure-all', [
    'ent'        => $ent,
    'programmes' => $programmes,
  ])->render();

  // generate PDF
  $pdf = app('dompdf.wrapper');
  $pdf->loadHTML($html)
    ->setPaper('a4', 'portrait');

  return $pdf->stream("all-fees-structures.pdf");
});


Route::get('university-programmes-fees-structure', function (Request $request) {
  $id = $request->get('id');
  $programme = UniversityProgramme::findOrFail($id);

  // force enterprise scoping
  if ($programme->enterprise_id !== Admin::user()->enterprise_id) {
    abort(403, 'Unauthorized');
  }

  $ent = Enterprise::findOrFail($programme->enterprise_id);

  $pdf = App::make('dompdf.wrapper');
  $pdf->loadHTML(view('print.university-programme-fees-structure', [
    'programme' => $programme,
    'ent'       => $ent,
  ]));
  // stream with a sensible filename
  return $pdf->stream("Fees-Structure-{$programme->code}.pdf");
});


Route::get('print-admission-letter', function () {
  //return view('print/print-admission-letter');
  $pdf = App::make('dompdf.wrapper');
  //$pdf->setOption(['DOMPDF_ENABLE_REMOTE' => false]);

  $pdf->loadHTML(view('print/print-admission-letter'));
  return $pdf->stream();
});

Route::get('bulk-messages-sending', function (Request $request) {
  //set unlimited time
  set_time_limit(-1);
  //set unlimited memory
  ini_set('memory_limit', '-1');
  $bulkMsg = BulkMessage::find($request->id);
  BulkMessage::do_prepare_messages($bulkMsg);
  $messages = DirectMessage::where(['bulk_message_id' => $bulkMsg->id])->get();

  $output = '<div style="font-family: sans-serif; margin: 20px; line-height: 1.6; max-width: 800px; margin: 0 auto;">';

  if ($bulkMsg->send_action != 'Send') {
    $output .= '<h1 style="color: #333; border-bottom: 2px solid #eee; padding-bottom: 10px; margin-bottom: 20px;">NOTE: These messages will not be sent because the bulk message is not marked as \'Send\'.</h1>';
  }

  $id = 0;
  $sent_count = 0;
  $fail_count = 0;
  $isOdd = true; // For striped rows

  foreach ($messages as $key => $msg) {
    $id++;
    $rowBgColor = $isOdd ? '#f9f9f9' : '#ffffff'; // Striped row background

    $output .= '<div style="border-bottom: 1px solid #eee; padding: 10px 0; background-color: ' . $rowBgColor . ';">';
    $output .= '<span style="display:inline;">' . $id . '. ' . htmlspecialchars($msg->receiver_number) . ' => ' . htmlspecialchars($msg->message_body) . '</span>';

    if ($bulkMsg->send_action != 'Send') {
      $output .= '<span style="display:inline; margin-left:5px; padding: 3px 6px; border-radius: 3px; background-color: #ffe0e0; color: #d32f2f;">NOT SENT</span><span style="display:inline; margin-left:5px;">Because bulk message is not marked as \'Send\'.</span>';
      $fail_count++;
      $output .= '</div>';
      $isOdd = !$isOdd;
      continue;
    }

    if ($msg->status == 'Sent') {
      $output .= '<span style="display:inline; margin-left:5px; padding: 3px 6px; border-radius: 3px; background-color: #e0ffe0; color: #388e3c;">SKIPPED</span><span style="display:inline; margin-left:5px;">Because already sent.</span>';
      $fail_count++;
      $output .= '</div>';
      $isOdd = !$isOdd;
      continue;
    }

    $msg->status = 'Pending';
    try {
      DirectMessage::send_message($msg);
      $output .= '<span style="display:inline; margin-left:5px; padding: 3px 6px; border-radius: 3px; background-color: #e0ffe0; color: #388e3c;">SENT</span>';
      $sent_count++;
    } catch (\Exception $e) {
      $output .= '<span style="display:inline; margin-left:5px; padding: 3px 6px; border-radius: 3px; background-color: #ffe0e0; color: #d32f2f;">FAILED</span><span style="display:inline; margin-left:5px;">Because: ' . htmlspecialchars($e->getMessage()) . '</span>';
      $fail_count++;
    }
    $output .= '</div>';
    $isOdd = !$isOdd;
  }

  $output .= '<div style="margin-top: 30px; padding: 15px; background-color: #f9f9f9; border: 1px solid #eee;">';
  $output .= '<h1 style="color: #333; border-bottom: 2px solid #eee; padding-bottom: 10px; margin-bottom: 20px;">SUMMARY</h1>';
  $output .= 'Total: ' . $id . ', Sent: ' . $sent_count . ', Failed: ' . $fail_count;
  $output .= '</div>';

  $output .= '</div>'; // Close the main container div

  echo $output;
  die('');
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

    $account = $trans->account;
    $msg = "Transaction already imported. Account: " . $account->name . ", BALANCE: UGX " . Utils::number_format($account->balance, null);

    //display message inlucing the link to the account. click here to view account details
    $link = admin_url('students/' . $account->administrator_id);
    $msg .= ", <a href='$link' >Click here to view account details</a>";

    $style = 'background-color: red; color: white; padding: 10px;';
    return "<h1 style='$style'>$msg</h1>";
  }
  try {
    $schoo_pay->doImport();
  } catch (\Exception $e) {
    $msg = $e->getMessage();
    $style = 'background-color: red; color: white; padding: 10px;';
    return "<h1 style='$style'>$msg</h1>";
  }

  $trans = Transaction::where([
    'school_pay_transporter_id' => $schoo_pay->school_pay_transporter_id
  ])->first();

  if ($trans == null) {
    $trans = Transaction::where([
      'school_pay_transporter_id' => $schoo_pay->schoolpayReceiptNumber
    ])->first();
  }

  if ($trans == null) {
    $style = 'background-color: red; color: white; padding: 10px;';
    return "<h1 style='$style'>Transaction not found.</h1>";
  }

  $style = 'background-color: green; color: white; padding: 10px;';

  $msg = "Transaction imported successfully. Account: " . $trans->account->name . ", BALANCE: UGX " . Utils::number_format($trans->account->balance, null);
  //display message inlucing the link to the account. click here to view account details
  $link = admin_url('students/' . $trans->account->administrator_id);
  $msg .= ", <a href='$link' >Click here to view account details</a>";
  return "<h1 style='$style'>$msg</h1>";
});

// Onboarding Routes - Step-by-step school registration wizard
Route::group(['prefix' => 'onboarding'], function () {
  Route::get('step1', [OnboardingController::class, 'step1'])->name('onboarding.step1');
  Route::get('step2', [OnboardingController::class, 'step2'])->name('onboarding.step2');
  Route::post('step2', [OnboardingController::class, 'processStep2'])->name('onboarding.process2');
  Route::get('step3', [OnboardingController::class, 'step3'])->name('onboarding.step3');
  Route::post('step3', [OnboardingController::class, 'processStep3'])->name('onboarding.process3');
  Route::get('step4', [OnboardingController::class, 'step4'])->name('onboarding.step4');
  Route::post('step4', [OnboardingController::class, 'processStep4'])->name('onboarding.process4');
  Route::get('step5', [OnboardingController::class, 'step5'])->name('onboarding.step5');
  Route::post('complete', [OnboardingController::class, 'complete'])->name('onboarding.complete');

  // Email Verification Routes for Onboarding
  Route::get('email-verification', [EmailVerificationController::class, 'show'])->name('onboarding.email.verification');
  Route::post('email-verification/send', [EmailVerificationController::class, 'send'])->name('onboarding.email.send');
  Route::post('email-verification/resend', [EmailVerificationController::class, 'resend'])->name('onboarding.email.resend');
  Route::get('email-verification/verify/{token}', [EmailVerificationController::class, 'verify'])->name('onboarding.email.verify');
  Route::post('email-verification/complete', [EmailVerificationController::class, 'markCompleted'])->name('onboarding.email.complete');
  Route::get('email-verification/status', [EmailVerificationController::class, 'checkStatus'])->name('onboarding.email.status');

  // Session management endpoint for auto-saving progress
  Route::post('save-session', [OnboardingController::class, 'saveSession'])->name('onboarding.save-session');

  // AJAX validation endpoints
  Route::get('validate-email', [OnboardingController::class, 'validateEmail'])->name('onboarding.validate.email');
  Route::get('validate-phone', [OnboardingController::class, 'validatePhone'])->name('onboarding.validate.phone');
  Route::get('validate-school-name', [OnboardingController::class, 'validateSchoolName'])->name('onboarding.validate.school.name');
  Route::get('validate-school-email', [OnboardingController::class, 'validateSchoolEmail'])->name('onboarding.validate.school.email');
});

// Update the enterprises/create route to redirect to onboarding
Route::get('enterprises/create', function () {
  return redirect('onboarding/step1');
});
