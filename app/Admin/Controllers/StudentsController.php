<?php

namespace App\Admin\Controllers;

use App\Models\AcademicClass;
use App\Models\AcademicYear;
use App\Models\AdminRole;
use App\Models\AdminRoleUser;
use App\Models\StudentHasClass;
use App\Models\Utils;
use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Tab;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;

/*

`first_name` TEXT DEFAULT NULL,
`last_name` TEXT DEFAULT NULL,
`date_of_birth` TEXT DEFAULT NULL,
`place_of_birth` TEXT DEFAULT NULL,
`sex` TEXT DEFAULT NULpL,
`home_address` TEXT DEFAULT NULL,
`current_address` TEXT DEFAULT NULL,
`phone_number_1` TEXT DEFAULT NULL,
`phone_number_2` TEXT DEFAULT NULL,
`email` TEXT DEFAULT NULL,
`nationality` TEXT DEFAULT NULL,
`religion` TEXT DEFAULT NULL,
`spouse_name` TEXT DEFAULT NULL,
`spouse_phone` TEXT DEFAULT NULL,
`father_name` TEXT DEFAULT NULL,
`father_phone` TEXT DEFAULT NULL,
`mother_name` TEXT DEFAULT NULL,
`mother_phone` TEXT DEFAULT NULL,
`languages` TEXT DEFAULT NULL,
`emergency_person_name` TEXT DEFAULT NULL,
`emergency_person_phone` TEXT DEFAULT NULL,
`national_id_number` TEXT DEFAULT NULL,
`passport_number` TEXT DEFAULT NULL,
`tin` TEXT DEFAULT NULL,
`nssf_number` TEXT DEFAULT NULL,
`bank_name` TEXT DEFAULT NULL,
`bank_account_number` TEXT DEFAULT NULL,
`primary_school_name` TEXT DEFAULT NULL,
`primary_school_year_graduated` TEXT DEFAULT NULL,
`seconday_school_name` TEXT DEFAULT NULL,
`seconday_school_year_graduated` TEXT DEFAULT NULL,
`high_school_name` TEXT DEFAULT NULL,
`high_school_year_graduated` TEXT DEFAULT NULL,
`degree_university_name` TEXT DEFAULT NULL,
`degree_university_year_graduated` TEXT DEFAULT NULL,
`masters_university_name` TEXT DEFAULT NULL,
`masters_university_year_graduated` TEXT DEFAULT NULL,
`phd_university_name` TEXT DEFAULT NULL,
`phd_university_year_graduated` TEXT DEFAULT NULL,
 


===================================
===================================
===================================
===================================
===================================
===================================
===================================
===================================
PERSONAL INFORMATION
--------------------
- First name
- Last name
- Date of birth
- Place of birth
- Sex
- Home Address
- Current Address
- Telephone
- Cell phone
- Email address
- Cival status
- Citizineship
- Religion
- Spource name
- Spource phone
- Farther's name
- Farther's phone
- Mothers's name
- Mothers's phone
- Languages
- Contact for emergency name
- Contact for emergency phone
- Mothers's phone
- 

ACCOUNT NUMBERS
--------------------
- National ID Number
- Passport number
- TIN Number
- NSSF NUMBER
- Bank name
- Bank account number
- 

EDUCATIONAL INFORMATION
--------------------
- Primary school name
- Primary school year graduated
- Seconday school name
- Seconday school year graduated
- High school name
- High school year graduated
- Degree university name
- Degree university year graduated
- Masters university name
- Masters university year graduated
- PHD university name
- PHD university year graduated
- 

ACCOUNT INFORMATION
--------------------
- Usename
- Password


*/

class StudentsController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Students';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {

        Administrator::update_current_classes(Admin::user()->enterprise_id);
        $grid = new Grid(new Administrator());
        $grid->disableBatchActions();
        $grid->actions(function ($actions) {
            $actions->disableDelete();
            $actions->disableView();
        });

        
        Utils::display_checklist(Utils::students_checklist(Admin::user()));
        Utils::display_checklist(Utils::students_optional_subjects_checklist(Admin::user()));



        $grid->filter(function ($filter) {

            $filter->between('created_at', 'Admitted')->date();


            $u = Admin::user();
            $filter->equal('current_class_id', 'Filter by class')->select(AcademicClass::where([
                'enterprise_id' => $u->enterprise_id
            ])->orderBy('id', 'Desc')->get()->pluck('name_text', 'id'));

            // Remove the default id filter
            $filter->disableIdFilter();

            // Add a column filter

            //$filter->like('name', 'Search by name');

            $u = Admin::user();
            /* $ajax_url = url(
                '/api/ajax?'
                    . 'enterprise_id=' . $u->enterprise_id
                    . "&search_by_1=name"
                    . "&search_by_2=id"
                    . "&model=User"
            );

            $filter->equal('id','Name')->select()->ajax($ajax_url); */
            /*  $filter->whereIn(function ($query) {

                $ids = StudentHasClass::where([
                    'academic_class_id' => ((int)($this->input))
                ])->get()->pluck('id');

                $query->whereIn('id', $ids);
            }, 'Filter by class')->select(
                AcademicClass::where([
                    'enterprise_id' => $u->enterprise_id
                ])->get()->pluck('name_text', 'id')
            ); */




            //$filter->expand();
        });

        $grid->quickSearch('name')->placeholder("Search by name...");

        if (!Admin::user()->isRole('dos')) {
            $grid->disableExport();
            $grid->disableCreateButton();
        }

        $grid->model()->where([
            'enterprise_id' => Admin::user()->enterprise_id,
            'user_type' => 'student'
        ]);


        if (Admin::user()->isRole('dos')) {
            $states = [
                'on' => ['value' => 1, 'text' => 'Verified', 'color' => 'success'],
                'off' => ['value' => 0, 'text' => 'Pending', 'color' => 'danger'],
            ];
            $grid->column('verification', 'Verification')
                ->filter([
                    0 => 'Pending',
                    1 => 'Verified',
                ])
                ->switch($states)
                ->sortable();
        } else {
            $grid->column('verification', __('Verification'))
                ->filter([0 => 'Pending', 1 => 'Verified'])
                ->using([0 => 'Pending', 1 => 'Verified'])
                ->width(100)
                ->label([
                    0 => 'danger',
                    1 => 'success',
                ])
                ->filter([
                    0 => 'Pending',
                    1 => 'Verified',
                ])
                ->sortable();
        }


        $grid->column('id', __('ID'))
            ->sortable();


        $grid->column('avatar', __('Photo'))
            ->lightbox(['width' => 60, 'height' => 60])
            ->sortable();


        $grid->column('name', __('Name'))->sortable();
        $grid->column('current_class_id', __('Current class'))
            ->display(function () {
                if ($this->current_class == null) {
                    return '<span class="badge bg-danger">No class</span>';
                }
                return $this->current_class->name_text;
            })
            ->filter([
                0 => 'No class'
            ])
            ->sortable();

        $grid->column('current_theology_class_id', __('Theology class'))
            ->display(function () {
                if ($this->current_theology == null) {
                    return '<span class="badge bg-danger">No class</span>';
                }
                return $this->current_theology->name;
            })
            ->filter([
                0 => 'No class'
            ])
            ->sortable();



        $grid->column('sex', __('Gender'))
            ->sortable()
            ->filter(['Male' => 'Male', 'Female' => 'Female']);
        $grid->column('emergency_person_name', __('Guardian'))
            ->hide()
            ->sortable();
        $grid->column('emergency_person_phone', __('Guardian Phone'))->hide()->sortable();
        $grid->column('guardian_relation', __('Guardian relation'))->sortable()->hide();


        $grid->column('phone_number_1', __('Phone number'))->hide();
        $grid->column('phone_number_2', __('Phone number 2'))->hide();
        $grid->column('email', __('Email'))->hide();
        $grid->column('date_of_birth', __('D.O.B'))->sortable()->hide();
        $grid->column('nationality', __('Nationality'))->sortable()->hide();

        $grid->column('place_of_birth', __('Address'))->sortable()->hide();
        $grid->column('home_address', __('Home address'))->hide();
        $grid->column('previous_school', __('Previous school'))->hide();
        $grid->column('residential_type', __('Residential type'))->hide();
        $grid->column('transportation', __('Transportation'))->hide();
        $grid->column('swimming', __('Swimming'))->hide();
        $grid->column('referral', __('Referral'))->hide();
        $grid->column('school_pay_payment_code', __('School pay payment code'))->sortable();



        // $grid->column('religion', __('Religion'))->hide();
        // $grid->column('spouse_name', __('Spouse name'))->hide();
        // $grid->column('spouse_phone', __('Spouse phone'))->hide();
        // $grid->column('father_name')->hide();
        // $grid->column('father_phone')->hide();
        // $grid->column('mother_name')->hide();
        // $grid->column('mother_phone')->hide();
        // $grid->column('national_id_number', 'N.I.N')->hide();
        // $grid->column('passport_number')->hide();
        // $grid->column('tin', 'TIN')->hide();
        // $grid->column('nssf_number')->hide();
        // $grid->column('bank_name')->hide();
        // $grid->column('bank_account_number')->hide();
        //$grid->column('primary_school_name')->hide();
        //$grid->column('primary_school_year_graduated')->hide();
        //$grid->column('seconday_school_name')->hide();
        //$grid->column('seconday_school_year_graduated')->hide();
        //$grid->column('high_school_name')->hide();
        //$grid->column('high_school_year_graduated')->hide();
        //$grid->column('degree_university_name')->hide();
        //$grid->column('degree_university_year_graduated')->hide();
        //$grid->column('masters_university_name')->hide();
        //$grid->column('masters_university_year_graduated')->hide();
        //$grid->column('phd_university_name')->hide();
        //$grid->column('phd_university_year_graduated')->hide();



        $grid->column('created_at', __('Admitted'))
            ->display(function ($date) {
                return Carbon::parse($date)->format('d-M-Y');
            })->hide()->sortable();





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
        $tab->add('Classes', view('admin.dashboard.show-user-profile-classes', [
            'u' => $u
        ]));
        $tab->add('Services', view('admin.dashboard.show-user-profile-bills', [
            'u' => $u
        ]));
        $tab->add('Transactions', view('admin.dashboard.show-user-profile-transactions', [
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



        $form->tab('BIO DATA', function (Form $form) {

            if (Admin::user()->isRole('dos')) {
                $form->multipleSelect('roles', trans('admin.roles'))
                    ->attribute([
                        'autocomplete' => 'off'
                    ])
                    ->default([4])



                    ->options(
                        AdminRole::where('slug', '!=', 'super-admin')
                            ->where('slug', '!=', 'admin')
                            ->get()
                            ->pluck('name', 'id')
                    )->rules('required');
            }


            $u = Admin::user();
            $form->hidden('enterprise_id')->rules('required')->default($u->enterprise_id)
                ->value($u->enterprise_id);

            $form->disableCreatingCheck();
            $form->disableReset();
            $form->disableViewCheck();

            $form->hidden('user_type')->default('student')->value('student')->updateRules('required|max:223');

            $form->text('first_name')->rules('required');
            $form->text('given_name');
            $form->text('last_name')->rules('required');

            $form->text('school_pay_payment_code')->rules('required');
            $form->text('school_pay_account_id')->rules('required');
            $form->select('sex', 'Gender')->options(['Male' => 'Male', 'Female' => 'Female'])->rules('required');




            $states = [
                'on' => ['value' => 1, 'text' => 'Verified', 'color' => 'success'],
                'off' => ['value' => 0, 'text' => 'Pending', 'color' => 'danger'],
            ];
            $form->switch('verification')->states($states)
                ->rules('required')->default(0);
        });



        $form->tab('PERSONAL INFORMATION', function (Form $form) {

            $form->text('home_address');
            $form->text('current_address');
            $form->text('emergency_person_name', "Guardian name");
            $form->text('guardian_relation', "Guardian relation");
            $form->text('emergency_person_phone', "Guardian phone number");
            $form->text('phone_number_2', "Guardian phone number 2");

            $form->text('religion');
            $form->text('previous_school');

            $form->text('father_name', "Father's name");
            $form->text('father_phone', "Father's phone number");
            $form->text('mother_name', "Mother's name");
            $form->text('mother_phone', "Mother's phone number");

            $form->text('residential_type');
            $form->text('transportation');
            $form->text('swimming');
            $form->text('nationality');
            $form->text('referral');
        });


        if (Admin::user()->isRole('dos')) {
            $form->tab('CLASSES', function (Form $form) {
                $form->morphMany('classes', 'CLASS ALLOCATION', function (Form\NestedForm $form) {
                    $form->html('Click on new to add this student to a class');
                    $u = Admin::user();
                    $form->hidden('enterprise_id')->default($u->enterprise_id);

                    $form->select('academic_class_id', 'Class')->options(function () {
                        return AcademicClass::where([
                            'enterprise_id' => Admin::user()->enterprise_id,
                        ])->get()->pluck('name', 'id');
                    })
                        ->rules('required')->load(
                            'stream_id',
                            url('/api/streams?enterprise_id=' . $u->enterprise_id)
                        );
                });
                $form->divider();
            });
        }

        if (Admin::user()->isRole('dos')) {
            $form->tab('THEOLOGY CLASSES', function (Form $form) {
                $form->morphMany('classes', 'CLASS ALLOCATION', function (Form\NestedForm $form) {
                    $form->html('Click on new to add this student to a class');
                    $u = Admin::user();
                    $form->hidden('enterprise_id')->default($u->enterprise_id);

                    $form->select('academic_class_id', 'Class')->options(function () {
                        return AcademicClass::where([
                            'enterprise_id' => Admin::user()->enterprise_id,
                        ])->get()->pluck('name', 'id');
                    })
                        ->rules('required')->load(
                            'stream_id',
                            url('/api/streams?enterprise_id=' . $u->enterprise_id)
                        );
                });
                $form->divider();
            });
        }


        if (Admin::user()->isRole('dos')) {
            $form->tab('SYSTEM ACCOUNT', function (Form $form) {

                $form->image('avatar', 'Student\'s photo');

                $form->text('email', 'Email address')
                    ->creationRules(["unique:admin_users"])
                    ->updateRules(["unique:admin_users,username,{{id}}"]);
                $form->text('username', 'School pay - Payment code.')
                    ->creationRules(["unique:admin_users"])
                    ->updateRules(["unique:admin_users,username,{{id}}"]);

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
            });
        }



        return $form;
    }
}
