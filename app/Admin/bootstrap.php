<?php

/**
 * Laravel-admin - admin builder based on Laravel.
 * @author z-song <https://github.com/z-song>
 *
 * Bootstraper for Admin.
 *
 * Here you can remove builtin form field:
 * Encore\Admin\Form::forget(['map', 'editor']);
 *
 * Or extend custom form field:
 * Encore\Admin\Form::extend('php', PHPEditor::class);
 *
 * Or require js and css assets:
 * Admin::css('/packages/prettydocs/css/styles.css');
 * Admin::js('/packages/prettydocs/js/main.js');
 *
 */

use Encore\Admin\Facades\Admin;
use App\Admin\Extensions\Nav\Shortcut;
use App\Admin\Extensions\Nav\Dropdown;
use App\Models\Enterprise;
use App\Models\MainCourse;
use App\Models\ParentCourse;
use App\Models\Term;
use App\Models\Transaction;
use App\Models\Utils;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;





Encore\Admin\Form::forget(['map', 'editor']);

$u = Auth::user();


if ($u != null) {
    Utils::system_boot($u);
}


Admin::navbar(function (\Encore\Admin\Widgets\Navbar $navbar) {



    $u = Auth::user(); 


    if ($u != null) {
        if (isset($_GET['change_dpy_to'])) {
            $t_id = ((int)(trim($_GET['change_dpy_to'])));
            $t = Term::find($t_id);
            if ($t != null) {
                DB::update(
                    "update enterprises set dp_year = ?, dp_term_id = ? where id = ? ",
                    [
                        $t->academic_year_id,
                        $t->id,
                        $t->enterprise_id,
                    ]
                );
                Admin::script('window.location.replace("' . url('/') . '");');
                return 'Loading...';
            }
        }
    }

    $navbar->left(view('admin.search-bar', [
        'u' => $u
    ]));
    $links = [];

    if ($u != null) {

        if ($u->isRole('super-admin')) {
            $links = [
                'Create new user' => admin_url('auth/users/create'),
                'Create new enterprise' => admin_url('enterprises/create'),
            ];
        }
        if ($u->isRole('admin')) {
            $links = [
                'Add new staff' => 'employees/create',
            ];
        }
        if ($u->isRole('bursar')) {
            $links = [
                'School fees payment' => 'school-fees-payment/create',
                'Transaction' => 'transactions/create',
            ];
        }

        if ($u->isRole('dos')) {
            $links = [
                'Admit new student' => 'students/create',
            ];
        }

        $navbar->left(Shortcut::make($links, 'fa-plus')->title('ADD NEW'));

        $navbar->left(new Dropdown());

        $check_list = [];
        $u = Auth::user();

        if ($u != null) {
            $check_list = Utils::system_checklist($u);
            $terms = Term::where([
                'enterprise_id' => $u->enterprise_id
            ])
                ->orderBy('id', 'desc')
                ->get();
            $navbar->right(view('widgets.admin-links', [
                'items' => $check_list,
                'u' => $u,
                'terms' => $terms
            ]));
        }
    }
});

Admin::css('/css/jquery-confirm.min.css');
Admin::js('/js/charts.js');

Admin::css(url('/assets/bootstrap.css'));
Admin::css('/assets/styles.css');
