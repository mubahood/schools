<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ReportsFinance
{

    public  $ent = null;
    public  $term = null;
    public  $year = null;
    public  $total_expected_tuition = 0;
    public  $total_expected_service_fees = 0;
    public  $total_payment_school_pay = 0;
    public  $total_payment_manual_pay = 0;
    public  $total_payment_mobile_app = 0;
    public  $total_payment_total = 0;
    public  $total_school_fees_balance = 0;
    public  $total_budget = 0;
    public  $total_expense = 0;
    public  $total_stock_value = 0;
    public  $messages = '';
    public  $date = '';
    public  $classes = [];
    public  $active_studentes = [];
    public  $active_studentes_ids = [];
    public  $bursaries = [];
    public  $total_bursaries_funds = 0;
    public  $services = [];
    public  $services_sub_category = [];
    public  $budget_vs_expenditure = [];
    public  $stocks = [];

    protected $tabel = 'report_finances';
    public function __construct($ent)
    { {

            $this->ent = $ent;

            $this->term = Term::where([
                'id' => $ent->dp_term_id,
            ])->first();
            if ($this->term == null) {
                throw new Exception("No term selected");
            }
            $this->date = Utils::my_date_time(now());

            $stocks = StockItemCategory::where([
                'enterprise_id' => $ent->id,
            ])->get();

            foreach ($stocks as $key => $stock) {
                $_stock = $stock;
                $_stock->available_quantity = Utils::number_format($stock->quantity, $stock->measuring_unit);
                $_stock->total_worth =  StockBatch::where([
                    'enterprise_id' => $ent->id,
                    'stock_item_category_id' => $stock->id,
                ])->sum('worth');
                $this->stocks[] = $_stock;
            }

            foreach (AccountParent::where([
                'enterprise_id' => $ent->id,
            ])->get() as $key => $acc) {
                $_acc = $acc;
                $_acc->total_budget = $acc->getBudget($this->term);
                $_acc->total_expense = $acc->getExpenditure($this->term);
                $this->budget_vs_expenditure[] = $_acc;
            }

            $this->total_stock_value = StockBatch::where([
                'enterprise_id' => $ent->id,
            ])->sum('worth');

            $this->total_budget = FinancialRecord::where([
                'enterprise_id' => $ent->id,
                'term_id' => $this->term->id,
                'type' => 'BUDGET',
            ])->sum('amount');
            $this->total_expense = FinancialRecord::where([
                'enterprise_id' => $ent->id,
                'term_id' => $this->term->id,
                'type' => 'EXPENDITURE',
            ])->sum('amount');

            $this->year = $this->term->academic_year;
            $classes = AcademicClass::where([
                'enterprise_id' => $ent->id,
                'academic_year_id' => $this->year->id,
            ])->get();

            foreach ($classes as $c) {
                $verified_studentes = [];
                $class_verified_studentes_ids = [];
                foreach ($c->students as $key => $value) {
                    if ($value->student == null) {
                        continue;
                    }
                    if ($value->student->status != 1) {
                        continue;
                    }
                    if (in_array($value->student->id, $this->active_studentes_ids)) {
                        $this->messages .= "Student " . $value->student->name . " - #{$value->student->id}, is in two classes.<br> ";
                        //throw new Exception($this->messages);
                        continue;
                    }
                    $this->active_studentes[] = $value->student;
                    $this->active_studentes_ids[] = $value->student->id;
                    $class_verified_studentes_ids[] = $value->student->id;
                    $verified_studentes[] = $value->student;
                }
                $_c = $c;
                $_c->individual_fees = $c->academic_class_fees->where('due_term_id', $this->term->id)->sum('amount');
                $_c->verified_studentes = $verified_studentes;
                $_c->total_bills = (count($_c->verified_studentes)) * $_c->individual_fees;
                $_c->total_balance = Account::where([
                    'enterprise_id' => $ent->id,
                ])
                    ->whereIn('administrator_id', $class_verified_studentes_ids)
                    ->sum('balance');

                $this->total_expected_tuition += $_c->total_bills;
                $this->classes[$c->id] = $_c;
            }

            $this->services = [];
            $this->services_sub_category = [];
            foreach (ServiceCategory::where([
                'enterprise_id' => $ent->id,
            ])->get() as $key => $serviceCat) {
                $_serviceCat = $serviceCat;
                $_serviceCat->subscriptions_total = 0;
                foreach ($serviceCat->services as $key => $subCat) {
                    $_subCat = $subCat;
                    $_subCat->subsList = ServiceSubscription::where([
                        'enterprise_id' => $ent->id,
                        'due_term_id' => $this->term->id,
                        'service_id' =>  $subCat->id,
                    ])
                        ->whereIn('administrator_id', $this->active_studentes_ids)
                        ->get();
                    $_subCat->subscriptions_total = $_subCat->subsList->sum('total');
                    $_serviceCat->subscriptions_total += $_subCat->subscriptions_total;
                    $this->services_sub_category[] = $_subCat;
                }
                $this->services[] = $_serviceCat;
            }



            foreach (Bursary::where([
                'enterprise_id' => $ent->id,
            ])->get() as $key => $bursary) {
                $benefiaries = BursaryBeneficiary::where([
                    'enterprise_id' => $ent->id,
                    'due_term_id' => $this->term->id,
                    'bursary_id' => $bursary->id,
                ])
                    ->whereIn('administrator_id', $this->active_studentes_ids)
                    ->get();
                $_bursary = $bursary;
                $_bursary->active_benefiaries = count($benefiaries);
                $_bursary->total_fund = $benefiaries->sum('bursary.fund');
                $this->bursaries[] = $_bursary;
            }

            $bursaries = BursaryBeneficiary::where([
                'enterprise_id' => $ent->id,
                'due_term_id' => $this->term->id,
            ])
                ->whereIn('administrator_id', $this->active_studentes_ids)
                ->get();
            foreach ($bursaries as $key => $bar) {
                if ($bar->bursary == null) {
                    $this->messages .= "Bursary not found for bursary beneficiary #{$bar->id} <br>";
                    continue;
                }
                $this->total_bursaries_funds += $bar->bursary->fund;
            }


            $this->total_expected_service_fees = ServiceSubscription::where([
                'enterprise_id' => $ent->id,
                'due_term_id' => $this->term->id,
            ])
                ->whereIn('administrator_id', $this->active_studentes_ids)
                ->sum('total');


            $this->total_payment_school_pay = Transaction::where([
                'enterprise_id' => $ent->id,
                'term_id' => $this->term->id,
                'source' => 'SCHOOL_PAY',
            ])
                ->where('amount', '>', 0)
                ->sum('amount');
            $this->total_payment_manual_pay = Transaction::where([
                'enterprise_id' => $ent->id,
                'term_id' => $this->term->id,
                'source' => 'MANUAL_ENTRY',
            ])
                ->where('amount', '>', 0)
                ->sum('amount');
            $this->total_payment_mobile_app = Transaction::where([
                'enterprise_id' => $ent->id,
                'term_id' => $this->term->id,
                'source' => 'MOBILE_APP',
            ])
                ->where('amount', '>', 0)
                ->sum('amount');
            $this->total_payment_total = $this->total_payment_school_pay + $this->total_payment_manual_pay + $this->total_payment_mobile_app;
            //$this->total_school_fees_balance = ($this->total_expected_tuition + $this->total_expected_service_fees) - $this->total_payment_total;
            $this->total_school_fees_balance = Account::where([
                'enterprise_id' => $ent->id,
            ])
                ->whereIn('administrator_id', $this->active_studentes_ids)
                ->sum('balance');
            $rep = ReportFinanceModel::where([
                'term_id' => $this->term->id,
            ])->first();
            if ($rep != null) {
                $rep->total_expected_tuition = $this->total_expected_tuition;
                $rep->total_expected_service_fees = $this->total_expected_service_fees;
                $rep->total_payment_school_pay = $this->total_payment_school_pay;
                $rep->total_payment_manual_pay = $this->total_payment_manual_pay;
                $rep->total_payment_mobile_app = $this->total_payment_mobile_app;
                $rep->total_payment_total = $this->total_payment_total;
                $rep->total_school_fees_balance = $this->total_school_fees_balance;
                $rep->total_budget = $this->total_budget;
                $rep->total_expense = $this->total_expense;
                $rep->total_stock_value = $this->total_stock_value;
                $rep->messages = $this->messages;
                $rep->total_bursaries_funds = $this->total_bursaries_funds;
                $rep->save(); 
            }
            //$this->save();
        }
    }
}
