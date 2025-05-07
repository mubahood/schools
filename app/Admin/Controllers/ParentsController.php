<?php

namespace App\Admin\Controllers;

use App\Models\Utils;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Tab;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\MessageBag;

class ParentsController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Parents';

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

        $grid->export(function ($export) {
            $export->except(['kids', 'children']);
        });


        /*         $ git add  .git/MERGE_MSG -f


        UW PICO 5.09                 File: /home4/schooics/public_html/.git/MERGE_MSG                 Modified   */
        $grid->model()
            ->orderBy('id', 'Desc')
            ->where([
                'enterprise_id' => Admin::user()->enterprise_id,
                'user_type' => 'parent'
            ]);
        $grid->actions(function ($actions) {
            //$actions->disableView();
        });


        $grid->filter(function ($filter) {

            $roleModel = config('admin.database.roles_model');
            /*  $filter->equal('main_role_id', 'Filter by role')
                ->select($roleModel::where('slug', '!=', 'super-admin')
                    ->where('slug', '!=', 'student')
                    ->get()
                    ->pluck('name', 'id')); 
  */
        });


        /*         $grid->disableExport();
        $grid->disableCreateButton(); */
        $grid->quickSearch('name')->placeholder('Search by name');
        $grid->disableBatchActions();
        $grid->column('id', __('Id'))->sortable();
        $grid->column('name', __('Name'))->sortable();
        $grid->column('children', __('No. of Children'))
            ->display(function ($x) {
                if ($this->kids == null) {
                    return '-';
                }
                $txt = '<a href="' . admin_url('students/?parent_id=' . $this->id) . '" title="View children" ><b>' . count($this->kids) . "</b></a>";

                return $txt;
            });
        $grid->column('kids', __('Lis of Children'))
            ->display(function ($x) {
                if ($this->kids == null) {
                    return '-';
                }
                $txt = "";
                $isFirst = true;
                foreach ($this->kids as $key => $kid) {
                    if (!$isFirst) {
                        $txt .= ', ';
                    } else {
                        $isFirst = false;
                    }
                    $txt .= '<a href="' . admin_url('students/' . $kid->id) . '" title="' . $kid->name . '" >' . $kid->name . "</a>";
                }
                return $txt;
            });
        $grid->column('roles', 'Roles')->pluck('name')->label()->hide();
        $grid->column('phone_number_1', __('Phone number'))
            ->display(function ($x) {
                $phone_number = $this->getParentPhonNumber();
                if ($phone_number == null) {
                    return "-";
                }
                return $phone_number;
            })
            ->sortable();
        $grid->column('current_address', __('Address'));
        $grid->column('email', __('Email'));
        $grid->column('sex', __('Gender'))->hide();
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
        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
            $tools->disableView();
        });
        $form->divider('Personal Information');

        $u = Admin::user();
        $form->hidden('enterprise_id')->rules('required')->default($u->enterprise_id)
            ->value($u->enterprise_id);

        $form->hidden('user_type')->default('parent')->value('parent');

        $form->text('first_name')->rules('required');
        $form->text('last_name')->rules('required');
        $form->select('sex', 'Gender')->options(['Male' => 'Male', 'Female' => 'Female'])->rules('required');
        $form->text('current_address', 'Address');
        $form->text('phone_number_1', 'Mobile phone number')->rules('required');
        $form->text('phone_number_2', 'Home phone number');
        $form->text('nationality');
        $form->text('religion');

        //SYSTEM ACCOUNT
        $form->divider('System Account');
        $roleModel = config('admin.database.roles_model');
        $roles = $roleModel::where(['slug' => 'parent'])
            ->get()->pluck('name', 'id');

        $form->multipleSelect('roles', trans('admin.roles'))
            ->attribute([
                'autocomplete' => 'off'
            ])
            ->options($roles)->rules('required');

        $ajax_url = url('/api/ajax-users?enterprise_id=' . $u->enterprise_id . "&user_type=student");
        $form->multipleSelect('kids', "Children")
            ->options(function ($ids) {
                if (!is_array($ids)) {
                    return [];
                }
                $data = Administrator::whereIn('id', $ids)->pluck('name', 'id');
                return $data;
            })
            ->ajax($ajax_url)->rules('required');



        $form->image('avatar', 'Profile photo');

        $form->text('email', 'Email address');

        $form->password('password', trans('admin.password'))->rules('required|confirmed');

        $form->password('password_confirmation', trans('admin.password_confirmation'))->rules('required')
            ->default(function ($form) {
                return $form->model()->password;
            });

        $form->ignore(['password_confirmation']);
        $form->saving(function (Form $form) {
            $prepared_phone_number_1 = Utils::prepare_phone_number($form->phone_number_1);
            if (!Utils::phone_number_is_valid($prepared_phone_number_1)) {
                $error = new MessageBag([
                    'title'   => 'Error',
                    'message' => 'First phone number is invalid.'
                ]);
                return back()->with(compact('error'));
            }

            if ($form->phone_number_2 != null) {
                if (strlen($form->phone_number_2) > 3) {
                    $prepared_phone_number_2 = Utils::prepare_phone_number($form->phone_number_2);
                    if (!Utils::phone_number_is_valid($prepared_phone_number_2)) {
                        $error = new MessageBag([
                            'title'   => 'Error',
                            'message' => 'Second phone number is invalid.'
                        ]);
                        return back()->with(compact('error'));
                    }
                }
            }



            if ($form->email == null || strlen($form->email) < 3) {
                $form->email = $form->phone_number_1;
            }

            $form->phone_number_1 = $prepared_phone_number_1;
            $form->username = $prepared_phone_number_1;

            if ($form->password && $form->model()->password != $form->password) {
                $form->password = Hash::make($form->password);
            }
        });



        return $form;
    }
}
