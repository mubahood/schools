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


class SuppliersController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Suppliers';

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

        /*         $ git add  .git/MERGE_MSG -f


        UW PICO 5.09                 File: /home4/schooics/public_html/.git/MERGE_MSG                 Modified   */
        $grid->model()
            ->orderBy('id', 'Desc')
            ->where([
                'enterprise_id' => Admin::user()->enterprise_id,
                'user_type' => 'supplier'
            ]);
        $grid->actions(function ($actions) {
            //$actions->disableView();
        });


        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $u = Admin::user();
            $teachers = [];
            foreach (Administrator::where([
                'enterprise_id' => $u->enterprise_id,
                'user_type' => 'supplier',
            ])->get() as $key => $a) {
                if ($a->isRole('teacher')) {
                    $teachers[$a['id']] = $a['name'] . " => " . $a['id'];
                }
            }

            $filter->like('name', 'Name');
        });



        $grid->column('id', __('Id'))->sortable();
        $grid->column('name', __('Name'))->sortable();
        $grid->column('phone_number_1', __('Phone number'));
        $grid->column('phone_number_2', __('Phone number 2'));
        $grid->column('sex', __('Gender'));
        $grid->column('email', __('Email'));

        $grid->column('current_address', __('Address'));
 
        $grid->column('home_address', __('Home address'))->hide();
        /* $grid->column('religion', __('Religion'))->hide();
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
        $grid->column('phd_university_year_graduated')->hide(); */

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



        $form->tab('BIO DATA', function (Form $form) {

            $u = Admin::user();
            $form->hidden('enterprise_id')->rules('required')->default($u->enterprise_id)
                ->value($u->enterprise_id);

            $form->hidden('user_type')->default('supplier')->value('supplier');

            $form->text('first_name')->rules('required');
            $form->text('last_name')->rules('required');
            $form->select('sex','Gender')->options(['Male' => 'Male', 'Female' => 'Female'])->rules('required');
            $form->text('current_address', 'Address');
            $form->text('phone_number_1', 'Phone number 1')->rules('required');
            $form->text('phone_number_2', 'Phone number 2');
        })
            ->tab('USER ROLES', function (Form $form) {
                $roleModel = config('admin.database.roles_model');
                $form->multipleSelect('roles', trans('admin.roles'))
                    ->attribute([
                        'autocomplete' => 'off'
                    ])
                    ->options(
                        $roleModel::where('slug', '=', 'supplier')
                            ->get()
                            ->pluck('name', 'id')
                    )->rules('required');
            })
            ->tab('SYSTEM ACCOUNT', function (Form $form) {
                $form->image('avatar', trans('admin.avatar'));

                $form->text('email', 'Email address')
                    ->creationRules(['required', "unique:admin_users"])
                    ->updateRules(['required', "unique:admin_users,username,{{id}}"]);
                $form->text('username', 'Username')
                    ->creationRules(['required', "unique:admin_users"])
                    ->updateRules(['required', "unique:admin_users,username,{{id}}"]);

                $form->password('password', trans('admin.password'))->rules('required|confirmed');
                $form->password('password_confirmation', trans('admin.password_confirmation'))->rules('required')
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




        return $form;
    }
}
