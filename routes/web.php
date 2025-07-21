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
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

Route::get('student-data-import-do-import', [MainController::class, 'student_data_import_do_import']);
Route::get('process-students-enrollment', [MainController::class, 'process_students_enrollment']);

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

Route::get('reset-a-school', function (Request $request) {

  $studentHasSemeters = StudentHasSemeter::where('enterprise_id', 24)
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

  die('done');
  dd($studentHasSemeters);
  $u = User::find(20034);
  $transactions = Transaction::where('account_id', $u->account->id)
    ->orderBy('id', 'desc')
    ->get();
  $u->bill_university_students();
  echo "Transactions for user: " . $u->name . "<br>";
  return 'done';
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
  if (isset($_SERVER['HTTP_HOST'])) {
    if (
      $_SERVER['HTTP_HOST'] === 'tusometech.com' ||
      $_SERVER['HTTP_HOST'] === 'localhost'
    ) {
      return view('landing.index');
    } else {
      //redurect to dashboard
      $dashboard = admin_url('dashboard');
      header("Location: $dashboard");
    }
  }
  return view('landing.index');
});

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
  echo <<<EOF
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
  $url = "https://www.socnetsolutions.com/projects/bulk/amfphp/services/blast.php?username=mubaraka&passwd=muh1nd0@2023";
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
  $url = "https://www.socnetsolutions.com/projects/bulk/amfphp/services/blast.php?username=mubaraka&passwd=muh1nd0@2023";
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

  $pdf->loadHTML(view('fees.demand-notice', [
    'recs' => $recs,
    'ent' => $ent,
    'demand' => $idCard
  ]));
  $pdf->render();
  return $pdf->stream();

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
