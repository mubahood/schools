<?php

namespace App\Admin\Controllers;

use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Tab;
use Illuminate\Support\Facades\Hash;


class EmployeesController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Teaching & non teaching staff';

    protected function title()
    {
        //check if url contains 'not-active-employees'
        if (strpos(url()->current(), 'not-active-employees') !== false) {
            return "All employees";
        }
        return "Teaching & non teaching staff.";
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Administrator());
        $grid->actions(function ($actions) {
            $actions->disableDelete();
        });

        $status = 1;
        if (strpos(url()->current(), 'not-active-employees') !== false) {
            $status = 0;
        }
 
        /*         $ git add  .git/MERGE_MSG -f


        UW PICO 5.09                 File: /home4/schooics/public_html/.git/MERGE_MSG                 Modified   */

        $cons = [
            'enterprise_id' => Admin::user()->enterprise_id,
            'user_type' => 'employee',
        ];
        if ($status == 1) {
            $cons['status'] = 1;
        }

        $grid->model()
            ->orderBy('id', 'Desc')
            ->where(
                $cons
            );
        $grid->actions(function ($actions) {
            //$actions->disableView();
        });


        $grid->filter(function ($filter) {

            $roleModel = config('admin.database.roles_model');
            $filter->equal('main_role_id', 'Filter by role')
                ->select($roleModel::where('slug', '!=', 'super-admin')
                    ->where('slug', '!=', 'student')
                    ->get()
                    ->pluck('name', 'id'));
        });



        $grid->quickSearch('name')->placeholder('Search by name');
        $grid->disableBatchActions();
        $grid->column('id', __('Id'))->sortable();
        $grid->column('name', __('Name'))->sortable();
        $grid->column('main_role_id', __('Main role'))
            ->display(function ($x) {
                if ($this->main_role == null) {
                    return $x;
                }
                return $this->main_role->name;
            })
            ->sortable()
            ->label('success');
        $grid->column('roles', 'Roles')->pluck('name')->label()->hide();
        $grid->column('phone_number_1', __('Phone number'));
        $grid->column('phone_number_2', __('Phone number 2'))->hide();
        $grid->column('email', __('Email'));
        $grid->column('date_of_birth', __('D.O.B'))->sortable();
        $grid->column('nationality', __('Nationality'))->sortable();
        $grid->column('sex', __('Gender'));
        $grid->column('place_of_birth', __('Place of birth'))->sortable();
        $grid->column('home_address', __('Home address'))->hide();
        $grid->column('current_address', __('Current address'))->hide();
        $grid->column('religion', __('Religion'))->hide();
        $grid->column('spouse_name', __('Spouse name'))->hide();
        $grid->column('spouse_phone', __('Spouse phone'))->hide();
        $grid->column('father_name')->hide();
        $grid->column('father_phone')->hide();
        $grid->column('mother_name')->hide();
        $grid->column('mother_phone')->hide();
        $grid->column('languages')->hide();
        $grid->column('emergency_person_name')->hide();
        $grid->column('emergency_person_phone')->hide();
        $grid->column('national_id_number', 'N.I.N')->hide();
        $grid->column('passport_number')->hide();
        $grid->column('tin', 'TIN')->hide();
        $grid->column('nssf_number')->hide();
        $grid->column('bank_name')->hide();
        $grid->column('bank_account_number')->hide();
        $grid->column('primary_school_name')->hide();
        $grid->column('primary_school_year_graduated')->hide();
        $grid->column('seconday_school_name')->hide();
        $grid->column('seconday_school_year_graduated')->hide();
        $grid->column('high_school_name')->hide();
        $grid->column('high_school_year_graduated')->hide();
        $grid->column('degree_university_name')->hide();
        $grid->column('degree_university_year_graduated')->hide();
        $grid->column('masters_university_name')->hide();
        $grid->column('masters_university_year_graduated')->hide();
        $grid->column('phd_university_name')->hide();
        $grid->column('phd_university_year_graduated')->hide();
        $grid->column('status', __('Status'))
            ->display(function ($status) {
                if ($status) {
                    return "Active";
                } else {
                    return "Not Active";
                }
            })
            ->sortable()
            ->label([
                1 => 'success',
                0 => 'danger',
            ]);

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {

        $u = Administrator::findOrFail($id);
        $tab = new Tab();
        $tab->add('Bio', view('admin.dashboard.show-user-profile-bio', [
            'u' => $u
        ]));
        return $tab;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $u = Admin::user();

        $form = new Form(new Administrator());


        $form->divider('BIO DATA');

        $u = Admin::user();
        $form->hidden('enterprise_id')->rules('required')->default($u->enterprise_id)
            ->value($u->enterprise_id);
        $form->hidden('user_type')->default('employee')->value('employee');
        $form->text('first_name')->rules('required');
        $form->text('last_name')->rules('required');
        $form->date('date_of_birth');
        $form->text('place_of_birth');
        $form->radioCard('sex', 'Gender')->options(['Male' => 'Male', 'Female' => 'Female'])->rules('required');
        $form->text('phone_number_1', 'Mobile phone number')->rules('required');
        $form->text('phone_number_2', 'Home phone number');

        $form->divider('PERSONAL INFORMATION');

        $form->radioCard('has_personal_info', 'Does this user have personal information?')
            ->options([
                'Yes' => 'Yes',
                'No' => 'No',
            ])->when('Yes', function ($form) {
                $form->text('religion');
                $form->text('nationality');
                $form->text('home_address');
                $form->text('current_address');

                $form->text('spouse_name', "Spouse's name");
                $form->text('spouse_phone', "Spouse's phone number");
                $form->text('father_name', "Father's name");
                $form->text('father_phone', "Father's phone number");
                $form->text('mother_name', "Mother's name");
                $form->text('mother_phone', "Mother's phone number");

                $form->text('languages', "Languages/Dilect");
                $form->text('emergency_person_name', "Emergency person to contact name");
                $form->text('emergency_person_phone', "Emergency person to contact phone number");
            });


        $form->divider('EDUCATIONAL INFORMATION');
        $form->radioCard('has_educational_info', 'Does this user have education information?')
            ->options([
                'Yes' => 'Yes',
                'No' => 'No',
            ])->when('Yes', function ($form) {

                $form->text('primary_school_name');
                $form->year('primary_school_year_graduated');
                $form->text('seconday_school_name');
                $form->year('seconday_school_year_graduated');
                $form->text('high_school_name');
                $form->year('high_school_year_graduated');

                $form->text('certificate_school_name');
                $form->year('certificate_year_graduated');

                $form->text('diploma_school_name');
                $form->year('diploma_year_graduated');

                $form->text('degree_university_name');
                $form->year('degree_university_year_graduated');
                $form->text('masters_university_name');
                $form->year('masters_university_year_graduated');
                $form->text('phd_university_name');
                $form->year('phd_university_year_graduated');
            });

        $form->divider('ACCOUNT NUMBERS');
        $form->radioCard('has_account_info', 'Does this user have account information?')
            ->options([
                'Yes' => 'Yes',
                'No' => 'No',
            ])->when('Yes', function ($form) {
                $form->text('national_id_number', 'National ID number');
                $form->text('passport_number', 'Passport number');
                $form->text('tin', 'TIN Number');
                $form->text('nssf_number', 'NSSF number');
                $form->text('bank_name');
                $form->text('bank_account_number');
            });

        $form->divider('USER ROLES');
        $roleModel = config('admin.database.roles_model');
        $form->multipleSelect('roles', trans('admin.roles'))
            ->attribute([
                'autocomplete' => 'off'
            ])
            ->options(
                $roleModel::where('slug', '!=', 'super-admin')
                    ->where('slug', '!=', 'student')
                    ->where('slug', '!=', 'librarian')
                    ->where('slug', '!=', 'supplier')
                    ->get()
                    ->pluck('name', 'id')
            );

        $form->divider('SYSTEM ACCOUNT');
        $form->image('avatar', trans('admin.avatar'));

        $form->email('email', 'Email address')
            ->creationRules(["unique:admin_users"]);
        $form->text('username', 'Username')
            ->creationRules(["unique:admin_users"])
            ->updateRules(['required', "unique:admin_users,username,{{id}}"]);

        $form->password('password', trans('admin.password'))->rules('confirmed');
        $form->password('password_confirmation', trans('admin.password_confirmation'))
            ->default(function ($form) {
                return $form->model()->password;
            });

        $form->ignore(['password_confirmation']);
        $form->saving(function (Form $form) {
            if ($form->password && $form->model()->password != $form->password) {
                $form->password = Hash::make($form->password);
            }
        });



        $form->radio('status')->options([
            1 => 'Active',
            0 => 'Not active',
        ])
            ->rules('required')->default(2);

        $form->disableReset();
        $form->disableViewCheck();
        return $form;
    }
}
