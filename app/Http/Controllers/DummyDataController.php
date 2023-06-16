<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AccountParent;
use App\Models\Demo;
use App\Models\Enterprise;
use App\Models\FinancialRecord;
use App\Models\StudentHasClass;
use App\Models\Term;
use App\Models\Utils;
use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Excel;
use Faker\Factory as Faker;


use function PHPUnit\Framework\fileExists;

class DummyDataController extends Controller
{
    public static function budget_and_expenses($ent_id)
    {
        // $faker = Faker::create();
        $ent = Enterprise::find($ent_id);
        $id = '1686931392';
        echo "<hr>========$id Budget and Expenses========";
        $m = Demo::where([
            'enterprise_id' => $ent_id,
            'generate_teachers' => $id,
        ])->first();
        if ($m != null) {
            return;
        }

        $terms = [];
        foreach (Term::where(
            'enterprise_id',
            $ent_id,
        )->get() as $key => $term) {
            $terms[] = $term;
        }
        $accs = Account::where([
            'enterprise_id' => $ent_id,
            'type' => 'OTHER_ACCOUNT',
        ])->get();
        $ex = 1;
        $bu = 1;
        for ($i = 0; $i < 1000; $i++) {
            foreach ($accs as $key => $acc) {
                shuffle($terms);
                $t = $terms[0];
                $rec = new FinancialRecord();
                $rec->enterprise_id = $ent->id;
                $rec->account_id = $acc->id;
                $rec->academic_year_id = $t->academic_year_id;
                $rec->parent_account_id = $acc->account_parent_id;
                $rec->term_id = $t->id;
                $rec->created_by_id = $ent->administrator_id;
                $rec->payment_date = Carbon::now()->addDays(rand(-50, 100));
                $rec->unit_price =  rand(1, 7000);
                $rec->quantity =  rand(1, 25);
                $mul = [100, 10];
                $types = ['BUDGET', 'EXPENDITURE'];
                $rec->unit_price *= $mul[rand(0, 1)];
                if ($types[rand(0, 1)] == 'BUDGET') {
                    $rec->type = 'BUDGET';
                    $rec->description = "Budget $bu record example.";
                    $bu++;
                } else {
                    $rec->type = 'EXPENDITURE';
                    $rec->description = "Expenditure $ex record example.";
                    $ex++;
                }
                $rec->save();
                echo "<br> $rec->id. $rec->description " . number_format($rec->amount);
            }
        }
        $m = new Demo();
        $m->enterprise_id = $ent_id;
        $m->generate_teachers = $id;
        $m->save();
    }

    public static function accounts($ent_id)
    {
        //dd(time());
        $ent = Enterprise::find($ent_id);
        $id = '1686929260';
        $m = Demo::where([
            'enterprise_id' => $ent_id,
            'generate_teachers' => $id,
        ])->first();
        if ($m != null) {
            return;
        }

        echo "<hr>========$id Account========";
        foreach (AccountParent::where([
            'enterprise_id' => $ent_id
        ])->get() as $key => $parent) {
            if ($parent->name == 'Stationary') {
                DummyDataController::_accounts([
                    'Pens',
                    'Erasers',
                    'Highlighters',
                    'Notebooks',
                    'Loose-Leaf Paper',
                    'Binders and Folders',
                    'Rulers',
                    'Compasses',
                    'Pencil Cases',
                    'Correction Fluid or Tape',
                    'Index Cards',
                ], $parent, $ent);
            } else if ($parent->name == 'Administration') {
                DummyDataController::_accounts([
                    'Principal\'s Office',
                    'Assistant Principal\'s Office',
                    'Administrative Assistants\'s Office',
                    'Registrar\'s Office',
                    'Admissions Officer\'s Office',
                    'Facilities Manager\'s Office',
                    'IT Coordinator\'s Office',
                ], $parent, $ent);
            } else if ($parent->name == 'Kitchen') {
                DummyDataController::_accounts([
                    'Principal\'s Office',
                    'Assistant Principal\'s Office',
                    'Administrative Assistants\'s Office',
                    'Registrar\'s Office',
                    'Admissions Officer\'s Office',
                    'Facilities Manager\'s Office',
                    'IT Coordinator\'s Office',
                ], $parent, $ent);
            } else if ($parent->name == 'FOOD & KITCHEN') {
                DummyDataController::_accounts([
                    "Cafeteria/Canteen",
                    "Menu Planning",
                    "Food Preparation",
                    "Dietary Accommodations",
                    "Meal Service",
                    "Snack and Beverage Options",
                    "Cleanliness and Sanitation",
                    "Food Safety Compliance",
                    "Allergen Management",
                    "Nutrition Education",
                    "Kitchen Equipment and Maintenance",
                    "Food Procurement"
                ], $parent, $ent);
            } else if ($parent->name == 'SALARY & WAGES') {
                DummyDataController::_accounts([
                    "Salary Administration",
                    "Payroll Processing",
                    "Employee Compensation",
                    "Hourly Wages",
                    "Overtime Payments",
                    "Bonuses and Incentives",
                    "Deductions and Withholdings",
                    "Leave Management",
                    "Tax Compliance",
                    "Salary Scales and Structures",
                    "Salary Adjustments",
                    "Employee Benefits",
                    "Pension Plans",
                    "Payroll Taxes",
                    "Salary Disbursement",
                    "Payroll Reports"
                ], $parent, $ent);
            } else if ($parent->name == 'UTILITIES') {
                DummyDataController::_accounts([
                    "Electricity",
                    "Water",
                    "Natural Gas",
                    "Heating",
                    "Cooling",
                    "Internet",
                    "Telephone",
                    "Trash Collection",
                    "Sewage",
                    "Maintenance and Repairs",
                    "Utility Bills",
                    "Energy Conservation",
                    "Renewable Energy",
                    "Utility Budgeting",
                    "Utility Service Providers",
                    "Meter Readings"
                ], $parent, $ent);
            } else if ($parent->name == 'UNIFORMS') {
                DummyDataController::_accounts([
                    "School Uniforms",
                    "Uniform Policy",
                    "Uniform Design",
                    "Uniform Sizing",
                    "Uniform Ordering",
                    "Uniform Distribution",
                    "Uniform Fittings",
                    "Uniform Alterations",
                    "Uniform Maintenance",
                    "Uniform Replacements",
                    "Uniform Storage",
                    "Uniform Compliance",
                    "Uniform Accessories",
                    "Uniform Guidelines",
                    "Uniform Code Enforcement",
                    "Uniform Identification"
                ], $parent, $ent);
            }
        }
        $m = new Demo();
        $m->enterprise_id = $ent_id;
        $m->generate_teachers = $id;
        $m->save();
    }


    public static function _accounts($names, $parent, $ent)
    {
        foreach ($names as $name) {
            $acc = new Account();
            $acc->name = $name;
            $acc->account_parent_id = $parent->id;
            $acc->enterprise_id = $ent->id;
            $acc->administrator_id = $ent->administrator_id;
            $acc->type = "OTHER_ACCOUNT";
            $acc->save();
            echo "$acc->id. $acc->name<br>";
        }
    }
    public static function account_parents($ent_id)
    {
        $id = '1686928195';
        $m = Demo::where([
            'enterprise_id' => $ent_id,
            'generate_teachers' => $id,
        ])->first();
        if ($m != null) {
            return;
        }

        echo "<hr>========$id Account Parents========";
        foreach ([
            'Stationary',
            'Administration',
            'Kitchen',
            'FOOD & KITCHEN',
            'SALARY & WAGES',
            'UTILITIES',
            'UNIFORMS',
        ] as $key => $name) {
            $ap = new AccountParent();
            $ap->enterprise_id = $ent_id;
            $ap->name = $name;
            $ap->description = $name;
            $ap->save();
            echo "$ap->id. $ap->name<br>";
        }
        $m = new Demo();
        $m->enterprise_id = $ent_id;
        $m->generate_teachers = $id;
        $m->save();
    }
}
