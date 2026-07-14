<?php

namespace App\Admin\Controllers;

use App\Models\FinancialRecord;
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
    protected $title = 'Suppliers';

    protected function grid()
    {
        $grid = new Grid(new Administrator());

        $grid->model()
            ->orderBy('name', 'Asc')
            ->where([
                'enterprise_id' => Admin::user()->enterprise_id,
                'user_type' => 'supplier',
            ]);

        $grid->actions(function ($actions) {
            $actions->disableDelete();
        });

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->like('name', 'Search by name');
            $filter->like('phone_number_1', 'Phone number');
        });

        $grid->export(function ($export) {
            $export->filename('Suppliers');
            $export->except(['actions', 'total_expenditure', 'quick_links']);
        });

        $grid->quickSearch('name')->placeholder('Search suppliers...');

        $grid->column('name', 'Name')->sortable();
        $grid->column('phone_number_1', 'Phone 1');
        $grid->column('phone_number_2', 'Phone 2')->hide();
        $grid->column('sex', 'Gender')->hide();
        $grid->column('email', 'Email');
        $grid->column('current_address', 'Address')->hide();

        // Pre-aggregate all supplier expenditures in one query
        $supplierTotals = FinancialRecord::where([
            'enterprise_id' => Admin::user()->enterprise_id,
            'type' => 'EXPENDITURE',
        ])->groupBy('supplier_id')
          ->selectRaw('supplier_id, SUM(amount) as total')
          ->pluck('total', 'supplier_id');

        $grid->column('total_expenditure', 'Total Expenditure')->display(function () use ($supplierTotals) {
            $total = $supplierTotals[$this->id] ?? 0;
            $formatted = 'UGX ' . number_format(abs($total));
            return $total != 0
                ? '<span class="label label-danger">' . $formatted . '</span>'
                : '<span class="text-muted">—</span>';
        });

        $grid->column('quick_links', 'Links')->display(function () {
            $expUrl = admin_url('financial-records-expenditure?supplier_id=' . $this->id);
            return "<a href='$expUrl' class='btn btn-xs btn-danger' title='View expenditures for this supplier'>Expenditures</a>";
        });

        return $grid;
    }

    protected function detail($id)
    {
        $u = Administrator::findOrFail($id);
        $tab = new Tab();
        $tab->add('Bio', view('admin.dashboard.show-user-profile-bio', ['u' => $u]));
        return $tab;
    }

    protected function form()
    {
        $form = new Form(new Administrator());

        $form->tab('BIO DATA', function (Form $form) {
            $u = Admin::user();
            $form->hidden('enterprise_id')->rules('required')->default($u->enterprise_id)->value($u->enterprise_id);
            $form->hidden('user_type')->default('supplier')->value('supplier');
            $form->text('first_name', 'First Name')->rules('required');
            $form->text('last_name', 'Last Name')->rules('required');
            $form->select('sex', 'Gender')->options(['Male' => 'Male', 'Female' => 'Female']);
            $form->text('current_address', 'Address');
            $form->text('phone_number_1', 'Phone Number 1')->rules('required');
            $form->text('phone_number_2', 'Phone Number 2');
            $form->textarea('description', 'Description / Notes');
        })
            ->tab('USER ROLES', function (Form $form) {
                $roleModel = config('admin.database.roles_model');
                $form->multipleSelect('roles', trans('admin.roles'))
                    ->attribute(['autocomplete' => 'off'])
                    ->options(
                        $roleModel::where('slug', '=', 'supplier')->get()->pluck('name', 'id')
                    );
            })
            ->tab('SYSTEM ACCOUNT', function (Form $form) {
                $form->image('avatar', trans('admin.avatar'));
                $form->text('email', 'Email Address')
                    ->creationRules(['required', 'unique:admin_users'])
                    ->updateRules(['required', 'unique:admin_users,email,{{id}}']);
                $form->text('username', 'Username')
                    ->creationRules(['required', 'unique:admin_users'])
                    ->updateRules(['required', 'unique:admin_users,username,{{id}}']);
                $form->password('password', trans('admin.password'))->rules('required|confirmed');
                $form->password('password_confirmation', trans('admin.password_confirmation'))
                    ->rules('required')
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
