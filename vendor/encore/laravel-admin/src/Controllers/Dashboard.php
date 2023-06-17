<?php

namespace Encore\Admin\Controllers;

use App\Models\AcademicClass;
use App\Models\AcademicClassFee;
use App\Models\Account;
use App\Models\AccountParent;
use App\Models\Enterprise;
use App\Models\FinancialRecord;
use App\Models\Mark;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceSubscription;
use App\Models\StudentHasClass;
use App\Models\TheologyExam;
use App\Models\TheologyMark;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Utils;
use Carbon\Carbon;
use Encore\Admin\Admin;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Dashboard
{

    public static function bursarServices()
    {
        $u = Auth::user();
        $classes = [];
        $labels = [];
        $amounts = [];
        $total = 0;

        foreach (ServiceCategory::where([
            'enterprise_id' => $u->enterprise_id
        ])->orderBy('id', 'Asc')->get() as $key => $value) {
            $value->amount = 0;
            if ($value->name == 'Others') {
                continue;
            }
            foreach (Service::where([
                'service_category_id' => $value->id
            ])->get() as $s) {
                $value->amount = ServiceSubscription::where([
                    'service_id' => $value->id
                ])->sum('total');
            }

            $value->amount = $value->amount;
            $labels[] = $value->name;
            $classes[] = $value;
            $amounts[] = $value->amount;
            $total += $value->amount;
        }

        return view('dashboard.bursarServices', [
            'classes' => $classes,
            'labels' => $labels,
            'amounts' => $amounts,
            'total' => $total,
        ]);


        $s = new AcademicClass();
        $s->name = "Services";
        foreach (Service::all() as $key => $val) {
            $amount = $val->fee;
            $subs = count($val->subs);
            $s->amount += ($amount * $subs);
        }
        $labels[] = 'Services';
        $classes[] = $s;
        $amounts[] = $s->amount;

        return view('dashboard.bursarServices', [
            'classes' => $classes,
            'labels' => $labels,
            'amounts' => $amounts,
            'total' => $total,
        ]);
        /* foreach (AcademicClass::where([
            'enterprise_id' => $u->enterprise_id
        ])->orderBy('id', 'Asc')->get() as $key => $value) {
            $value->amount = Account::where([
                'academic_class_id' => $value->id
            ])->sum('balance');
            $labels[] = $value->name;
            $classes[] = $value;
            $amounts[] = $value->amount;
            $total += $value->amount;
        }

        return view('dashboard.bursarFeesExpected', [
            'classes' => $classes,
            'labels' => $labels,
            'amounts' => $amounts,
            'total' => $total,
        ]); */
    }

    public static function bursarFeesExpected()
    {
        $u = Auth::user();
        $classes = [];
        $labels = [];
        $amounts = [];
        $total = 0;

        $academic_year_id = 0;

        $active_academic_year = $u->ent->active_academic_year();
        if ($active_academic_year != null) {
            $academic_year_id = $active_academic_year->id;
        }

        foreach (AcademicClass::where([
            'enterprise_id' => $u->enterprise_id,
            'academic_year_id' => $academic_year_id
        ])->orderBy('id', 'Asc')->get() as $key => $value) {
            $value->amount = AcademicClassFee::where([
                'academic_class_id' => $value->id
            ])->sum('amount');
            $value->amount = $value->amount * count($value->students);
            $labels[] = $value->name;
            $classes[] = $value;
            $amounts[] = $value->amount;
            $total += $value->amount;
        }

        $s = new AcademicClass();
        /*  $s->name = "Services";
        foreach (Service::where([
            'enterprise_id' => $u->enterprise_id
        ])->get() as $key => $val) {
            $amount = $val->fee;
            $subs = count($val->subs);
            $s->amount += ($amount * $subs);
        } */
        /*   $labels[] = 'Services'; */
        /*    $classes[] = $s; */
        /* $amounts[] = $s->amount; */

        return view('dashboard.bursarFeesExpected', [
            'classes' => $classes,
            'labels' => $labels,
            'amounts' => $amounts,
            'total' => $total,
        ]);
        /* foreach (AcademicClass::where([
            'enterprise_id' => $u->enterprise_id
        ])->orderBy('id', 'Asc')->get() as $key => $value) {
            $value->amount = Account::where([
                'academic_class_id' => $value->id
            ])->sum('balance');
            $labels[] = $value->name;
            $classes[] = $value;
            $amounts[] = $value->amount;
            $total += $value->amount;
        }

        return view('dashboard.bursarFeesExpected', [
            'classes' => $classes,
            'labels' => $labels,
            'amounts' => $amounts,
            'total' => $total,
        ]); */
    }


    public static function bursarFeesServices()
    {

        $u = Auth::user();
        $year = $u->ent->dpYear();
        if ($year == null) {
            die("DP Year not found.");
        }
        $cats = [];

        $labels = [];
        $accounts = [];
        $amounts = [];
        $total = 0;
        foreach (AccountParent::where([
            'enterprise_id' => $u->enterprise_id,
        ])->get() as $key => $acc) {
            $acc->total = $acc->getSum($year);
            $labels[] = $acc->name;
            $accounts[] = $acc;
            $amounts[] = $acc->total;
            $total += $acc->total;
        }


        return view('dashboard.bursarBedgets', [
            'accounts' => $accounts,
            'labels' => $labels,
            'amounts' => $amounts,
            'total' => $total,
        ]);
    }



    public static function bursarFeesPaid()
    {
        $u = Auth::user();
        $classes = [];
        $labels = [];
        $amounts = [];
        $total = 0;
        $academic_year_id = 0;

        $active_academic_year = $u->ent->active_academic_year();
        if ($active_academic_year != null) {
            $academic_year_id = $active_academic_year->id;
        }

        foreach (AcademicClass::where([
            'enterprise_id' => $u->enterprise_id,
            'academic_year_id' => $academic_year_id,
        ])->orderBy('id', 'Asc')->get() as $key => $value) {
            $value->amount = Account::where([
                'academic_class_id' => $value->id
            ])->sum('balance');
            $labels[] = $value->name;
            $classes[] = $value;
            $amounts[] = $value->amount;
            $total += $value->amount;
        }

        return view('dashboard.bursarFeesPaid', [
            'classes' => $classes,
            'labels' => $labels,
            'amounts' => $amounts,
            'total' => $total,
        ]);
    }


    public static function help_videos()
    {
        return view('widgets.help-videos');
    }

    public static function all_users()
    {
        $u = Auth::user();
        $all_students = User::count();

        $male_students = User::where([
            'user_type' => 'Student',
            'sex' => 'Male',
        ])->count();
        $female_students = $all_students - $male_students;
        $sub_title = number_format($male_students) . ' Males, ';
        $sub_title .= number_format($female_students) . ' Females.';
        return view('widgets.box-5', [
            'is_dark' => false,
            'title' => 'All system users',
            'sub_title' => $sub_title,
            'number' => number_format($all_students),
            'link' => admin_url('auth/users')
        ]);
    }
    public static function all_teachers()
    {
        $all_students = User::where([
            'user_type' => 'employee',
        ])->count();

        $male_students = User::where([
            'user_type' => 'employee',
            'sex' => 'Male',
        ])->count();


        $female_students = $all_students - $male_students;
        $sub_title = number_format($male_students) . ' Males, ';
        $sub_title .= number_format($female_students) . ' Females.';
        return view('widgets.box-5', [
            'is_dark' => false,
            'title' => 'All teachers',
            'sub_title' => $sub_title,
            'number' => number_format($all_students),
            'link' => admin_url('auth/users')
        ]);
    }


    public static function all_students()
    {
        $all_students = User::where([
            'user_type' => 'Student',
        ])->count();

        $male_students = User::where([
            'user_type' => 'Student',
            'sex' => 'Male',
        ])->count();

        $female_students = $all_students - $male_students;

        $sub_title = number_format($male_students) . ' Males, ';
        $sub_title .= number_format($female_students) . ' Females.';
        return view('widgets.box-5', [
            'is_dark' => false,
            'title' => 'All students',
            'sub_title' => $sub_title,
            'number' => number_format($all_students),
            'link' => admin_url('auth/users')
        ]);
    }




    public static function theology_teacher_marks()
    {
        $u = Auth::user();

        $number_main =  (TheologyMark::where([
            'enterprise_id' => $u->enterprise_id,
            'teacher_id' => $u->id,
        ])->count());

        $number_1 =  (TheologyMark::where([
            'enterprise_id' => $u->enterprise_id,
            'teacher_id' => $u->id,
            'is_submitted' => true,
        ])->count());

        $number_2 = $number_main - $number_1;

        $sub_title = number_format($number_1) . ' Submitted, ';
        $sub_title .= number_format($number_2) . ' Not Submitted.';

        $style = 'success';
        if ($number_2 > 0) {
            $style = 'danger';
        }

        return view('widgets.box-5', [
            'is_dark' => false,
            'style' => $style,
            'title' => 'Theology Marks',
            'sub_title' => $sub_title,
            'number' => $number_main,
            'link' => admin_url('theology-marks')
        ]);
    }



    public static function teacher_marks()
    {
        $u = Auth::user();

        $number_main =  (Mark::where([
            'enterprise_id' => $u->enterprise_id,
            'teacher_id' => $u->id,
        ])->count());

        $number_1 =  (Mark::where([
            'enterprise_id' => $u->enterprise_id,
            'teacher_id' => $u->id,
            'is_submitted' => true,
        ])->count());


        $number_2 = ((int)($number_main)) - ((int)($number_1));

        $sub_title = number_format($number_1) . ' Submitted, ';
        $sub_title .= number_format($number_2) . ' Not Submitted.';

        $style = 'success';
        if ($number_2 > 0) {
            $style = 'danger';
        }

        return view('widgets.box-5', [
            'is_dark' => false,
            'style' => $style,
            'title' => 'Marks submission',
            'sub_title' => $sub_title,
            'number' => $number_main,
            'link' => admin_url('marks')
        ]);
    }




    public static function budget()
    {

        $u = Auth::user();
        $term = $u->ent->dpTerm();
        $data = [];
        $data['title'] = 'Budget for Term: ' . $term->name_text;
        $data['values'] = [];
        $data['labels'] = [];
        $data['data'] = [];
        foreach (AccountParent::where([
            'enterprise_id' => $u->enterprise_id,
        ])->get() as $key => $parent) {
            $tot = $parent->getBudget($term);
            if ($tot < 1) {
                continue;
            }
            $data['data'][] = $tot;
            $data['labels'][] = $parent->name;
            $data['values'][] = [
                'text' => $parent->name,
                'value' => number_format($tot),
            ];
        }
        return view('dashboard.budget', $data);
    }




    public static function expenditure()
    {
        $u = Auth::user();
        $data = [];
        for ($i = 29; $i >= 0; $i--) {
            $min = new Carbon();
            $max = new Carbon();
            $max->subDays($i);
            $min->subDays(($i + 1));
            $count = FinancialRecord::whereBetween('payment_date', [$min, $max])
                ->where([
                    'enterprise_id' => $u->enterprise_id,
                    'type' => 'EXPENDITURE',
                ])
                ->sum('amount');
            $data['data'][] = -1 * $count;
            $data['labels'][] = Utils::my_date($max);
        }

        return view('dashboard.expenditure', $data);
    }



    public static function count_expected_fees()
    {
        $u = Auth::user();
        $term = $u->ent->dpTerm();

        $fees_to_be_collected = Transaction::where([
            'enterprise_id' => $u->enterprise_id,
            'term_id' => $term->id
        ])
            ->where('amount', '<', 0)->sum('amount');
        $fees_paid = Transaction::where([
            'enterprise_id' => $u->enterprise_id,
            'term_id' => $term->id
        ])
            ->where('amount', '>', 0)->sum('amount');

        $bal = $fees_to_be_collected - $fees_paid;
        $sub_title =  "NOT PAID SCHOOL FEES: " . number_format($bal);
        return view('widgets.box-5', [
            'is_dark' => false,
            'title' => 'Expected school fees',
            'sub_title' => $sub_title,
            'number' => "UGX " . number_format(-1 * $fees_to_be_collected),
            'link' => admin_url('students')
        ]);
    }



    public static function count_percentage_paid_fees()
    {
        $u = Auth::user();
        $term = $u->ent->dpTerm();

        $fees_to_be_collected = Transaction::where([
            'enterprise_id' => $u->enterprise_id,
            'term_id' => $term->id
        ])
            ->where('amount', '<', 0)->sum('amount');
        $fees_paid = Transaction::where([
            'enterprise_id' => $u->enterprise_id,
            'term_id' => $term->id
        ])
            ->where('amount', '>', 0)->sum('amount');

        $bal = $fees_to_be_collected + $fees_paid;

        $percentage = ($fees_paid / $fees_to_be_collected) * 100;
        if ($percentage < 0) {
            $percentage = -1 * $percentage;
        }
        $sub_title =  "PAID SCHOOL FEES: " . number_format($fees_paid) . "";
        return view('widgets.box-5', [
            'is_dark' => false,
            'title' => 'Pecentage Paid',
            'sub_title' => $sub_title,
            'is_dark' => true,
            'number' => round($percentage, 2) . "%",
            'link' => admin_url('students')
        ]);
    }







    public static function count_paid_fees()
    {
        $man = Utils::manifest(Auth::user()->ent);

        $sub_title =  "To be paid by $man->active_students active students.";
        return view('widgets.box-5', [
            'is_dark' => false,
            'title' => 'Paid school fees',
            'sub_title' => $sub_title,
            'number' => "UGX " . number_format($man->paid_fees),
            'link' => admin_url('students')
        ]);
    }






    public static function count_unpaid_fees()
    {

        $man = Utils::manifest(Auth::user()->ent);
        $sub_title =  "To be paid by $man->active_students active students.";
        return view('widgets.box-5', [
            'is_dark' => false,
            'title' => 'Unpaid school fees',
            'sub_title' => $sub_title,
            'number' => "UGX " . number_format($man->unpaid_fees),
            'link' => admin_url('students')
        ]);
    }



    public static function students()
    {
        $u = Auth::user();
        $all_students = User::where([
            'enterprise_id' => $u->enterprise_id,
            'user_type' => 'Student',
            'status' => 1
        ])->count();

        $male_students = User::where([
            'enterprise_id' => $u->enterprise_id,
            'user_type' => 'Student',
            'sex' => 'Male',
            'status' => 1
        ])->count();
        $parents = User::where([
            'enterprise_id' => $u->enterprise_id,
            'user_type' => 'Parent',
        ])->count();

        $female_students = $all_students - $male_students;

        $sub_title = number_format($male_students) . ' Males, ';
        $sub_title .= number_format($female_students) . ' Females, ';
        $sub_title .= number_format($parents) . ' Parents.';
        return view('widgets.box-5', [
            'is_dark' => false,
            'title' => 'Students',
            'sub_title' => $sub_title,
            'number' => number_format($all_students),
            'link' => admin_url('students')
        ]);
    }

    public static function teachers()
    {
        $u = Auth::user();
        $all_students = User::where([
            'enterprise_id' => $u->enterprise_id,
            'user_type' => 'employee',
        ])->count();

        $male_students = User::where([
            'enterprise_id' => $u->enterprise_id,
            'user_type' => 'employee',
            'sex' => 'Male',
        ])->count();

        $female_students = $all_students - $male_students;

        $sub_title = number_format($male_students) . ' Males, ';
        $sub_title .= number_format($female_students) . ' Females.';
        return view('widgets.box-5', [
            'is_dark' => false,
            'title' => 'Teachers & Staff',
            'sub_title' => $sub_title,
            'number' => number_format($all_students),
            'link' => admin_url('employees')
        ]);
    }


    public static function school_population()
    {
        $u = Auth::user();
        $all_students = User::where([
            'enterprise_id' => $u->enterprise_id,
        ])->count();

        $male_students = User::where([
            'enterprise_id' => $u->enterprise_id,
            'user_type' => 'employee',
        ])->count();


        $female_students = $all_students - $male_students;

        $sub_title = number_format($male_students) . ' Males, ';
        $sub_title .= number_format($female_students) . ' Females.';
        return view('widgets.box-5', [
            'is_dark' => true,
            'title' => 'School population',
            'sub_title' => $sub_title,
            'number' => number_format($all_students),
            'link' => admin_url('employees')
        ]);
    }


    public static function staff()
    {
        $u = Auth::user();
        $all_students = User::where([
            'enterprise_id' => $u->enterprise_id,
        ])
            ->where(
                'user_type',
                '!=',
                'employee'
            )
            ->where(
                'user_type',
                '!=',
                'student'
            )
            ->count();


        $male_students =  User::where([
            'enterprise_id' => $u->enterprise_id,
        ])
            ->where(
                'user_type',
                '!=',
                'employee'
            )
            ->where(
                'user_type',
                '!=',
                'student'
            )
            ->where(
                'sex',
                '=',
                'Male',
            )
            ->count();


        $female_students = $all_students - $male_students;

        $sub_title = number_format($male_students) . ' Males, ';
        $sub_title .= number_format($female_students) . ' Females.';
        return view('widgets.box-5', [
            'is_dark' => false,
            'title' => 'Parents',
            'sub_title' => $sub_title,
            'number' => number_format($all_students),
            'link' => admin_url('parents')
        ]);
    }



    public static function parents()
    {
        $u = Auth::user();
        $all_students = User::where([
            'enterprise_id' => $u->enterprise_id,
            'user_type' => 'employee',
        ])->count();

        $male_students = User::where([
            'enterprise_id' => $u->enterprise_id,
            'user_type' => 'employee',
            'sex' => 'Male',
        ])->count();

        $female_students = $all_students - $male_students;

        $sub_title = number_format($male_students) . ' Males, ';
        $sub_title .= number_format($female_students) . ' Females.';
        return view('widgets.box-5', [
            'is_dark' => false,
            'title' => 'Parents',
            'sub_title' => $sub_title,
            'number' => number_format($all_students),
            'link' => admin_url('employees')
        ]);
    }




    public static function enterprises()
    {
        $enterprises = Enterprise::count();

        return view('widgets.box-5', [
            'is_dark' => true,
            'title' => 'All Enterprises',
            'sub_title' => 'Lifetime',
            'number' => number_format($enterprises),
            'link' => admin_url('employees')
        ]);
    }




    public static function fees()
    {
        $ent = Utils::ent();

        $u = Auth::user();
        $all_students = Transaction::where([
            'enterprise_id' => $u->enterprise_id,
        ])->where('academic_year_id', '!=', $ent->administrator_id)->sum('amount');

        $fees_to_be_collected = Transaction::where([
            'enterprise_id' => $u->enterprise_id,
        ])
            ->where('amount', '<', 0)
            ->where('academic_year_id', '!=', $ent->administrator_id)->sum('amount');

        $fees_collected = Transaction::where([
            'enterprise_id' => $u->enterprise_id,
        ])
            ->where('amount', '>', 0)
            ->where('is_contra_entry', false)

            ->where('academic_year_id', '!=', $ent->administrator_id)->sum('amount');
        //dd($all_students);

        $male_students = User::where([
            'enterprise_id' => $u->enterprise_id,
            'user_type' => 'employee',
            'sex' => 'Male',
        ])->count();

        $female_students = $all_students - $male_students;

        $fees_to_be_collected = (-1) * ($fees_to_be_collected);
        $sub_title = number_format($male_students) . ' Males, ';
        $sub_title .= number_format($female_students) . ' Females.';
        $sub_title = number_format($fees_to_be_collected) . " School fees to be collected";
        return view('widgets.box-5', [
            'is_dark' => true,
            'title' => 'School fees',
            'sub_title' => $sub_title,
            'number' => number_format($fees_collected),
            'link' => admin_url('employees')
        ]);
    }



    public static function income_vs_expenses()
    {
        return view('admin.charts.bar', [
            'is_dark' => true
        ]);
    }

    public static function fees_collected()
    {
        /*         return view('admin.charts.pie', [
            'is_dark' => true
        ]); */
    }



    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public static function title()
    {
        return view('admin::dashboard.title');
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public static function environment()
    {
        $envs = [
            ['name' => 'PHP version',       'value' => 'PHP/' . PHP_VERSION],
            ['name' => 'Laravel version',   'value' => app()->version()],
            ['name' => 'CGI',               'value' => php_sapi_name()],
            ['name' => 'Uname',             'value' => php_uname()],
            ['name' => 'Server',            'value' => Arr::get($_SERVER, 'SERVER_SOFTWARE')],

            ['name' => 'Cache driver',      'value' => config('cache.default')],
            ['name' => 'Session driver',    'value' => config('session.driver')],
            ['name' => 'Queue driver',      'value' => config('queue.default')],

            ['name' => 'Timezone',          'value' => config('app.timezone')],
            ['name' => 'Locale',            'value' => config('app.locale')],
            ['name' => 'Env',               'value' => config('app.env')],
            ['name' => 'URL',               'value' => config('app.url')],
        ];

        return view('admin::dashboard.environment', compact('envs'));
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public static function extensions()
    {
        $extensions = [
            'helpers' => [
                'name' => 'laravel-admin-ext/helpers',
                'link' => 'https://github.com/laravel-admin-extensions/helpers',
                'icon' => 'gears',
            ],
            'log-viewer' => [
                'name' => 'laravel-admin-ext/log-viewer',
                'link' => 'https://github.com/laravel-admin-extensions/log-viewer',
                'icon' => 'database',
            ],
            'backup' => [
                'name' => 'laravel-admin-ext/backup',
                'link' => 'https://github.com/laravel-admin-extensions/backup',
                'icon' => 'copy',
            ],
            'config' => [
                'name' => 'laravel-admin-ext/config',
                'link' => 'https://github.com/laravel-admin-extensions/config',
                'icon' => 'toggle-on',
            ],
            'api-tester' => [
                'name' => 'laravel-admin-ext/api-tester',
                'link' => 'https://github.com/laravel-admin-extensions/api-tester',
                'icon' => 'sliders',
            ],
            'media-manager' => [
                'name' => 'laravel-admin-ext/media-manager',
                'link' => 'https://github.com/laravel-admin-extensions/media-manager',
                'icon' => 'file',
            ],
            'scheduling' => [
                'name' => 'laravel-admin-ext/scheduling',
                'link' => 'https://github.com/laravel-admin-extensions/scheduling',
                'icon' => 'clock-o',
            ],
            'reporter' => [
                'name' => 'laravel-admin-ext/reporter',
                'link' => 'https://github.com/laravel-admin-extensions/reporter',
                'icon' => 'bug',
            ],
            'redis-manager' => [
                'name' => 'laravel-admin-ext/redis-manager',
                'link' => 'https://github.com/laravel-admin-extensions/redis-manager',
                'icon' => 'flask',
            ],
        ];

        foreach ($extensions as &$extension) {
            $name = explode('/', $extension['name']);
            $extension['installed'] = array_key_exists(end($name), Admin::$extensions);
        }

        return view('admin::dashboard.extensions', compact('extensions'));
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public static function dependencies()
    {
        $json = file_get_contents(base_path('composer.json'));

        $dependencies = json_decode($json, true)['require'];

        return Admin::component('admin::dashboard.dependencies', compact('dependencies'));
    }
}
