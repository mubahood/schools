<?php

namespace App\Admin\Controllers;

use App\Models\Enterprise;
use App\Models\User;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class EnterpriseController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Enterprises';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Enterprise());
        $grid->disableBatchActions();
        $grid->quickSearch('name', 'id', 'short_name')->placeholder('Search by name, id or short name');

        $grid->model()->orderBy('id', 'DESC');

        $grid->actions(function ($actions) {
            $actions->disableView();
        });

        $grid->column('color', __('color'))->sortable()->editable();
        $grid->column('id', __('Id'))->sortable();
        $grid->column('name', __('Name'))->sortable();
        $grid->column('administrator_id', __('Onwer'))->display(function () {
            if ($this->owner == null) {
                return '-';
            }
            return $this->owner->name;
        });
        $grid->column('logo', __('Logo'));
        $grid->column('short_name', __('Short name'));
        $grid->column('phone_number', __('Phone number'));
        $grid->column('email', __('Email'));
        $grid->column('address', __('Address'))->hide();
        $grid->column('created_at', __('Created'));
        $grid->column('details', __('Details'))->hide();

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
        $show = new Show(Enterprise::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('name', __('Name'));
        $show->field('short_name', __('Short name'));
        $show->field('details', __('Details'));
        $show->field('logo', __('Logo'));
        $show->field('phone_number', __('Phone number'));
        $show->field('email', __('Email'));
        $show->field('address', __('Address'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Enterprise());

        $u = Admin::user();

        $ajax_url = url(
            '/api/ajax?'
                . 'enterprise_id=' . $u->enterprise_id
                . "&search_by_1=name"
                . "&search_by_2=id"
                . "&model=User"
        );

        $form->select('administrator_id', __('School owner'))
            ->ajax(
                $ajax_url
            )
            ->options(function ($id) {
                $a = User::find($id);
                if ($a) {
                    return [$a->id => "#" . $a->id . " - " . $a->name];
                }
            })
            ->rules('required');

        $form->text('name', __('Name'))->rules('required');
        $form->text('short_name', __('Short name'));
        $form->text('motto', __('School Motto'));

        $form->select('type', __('School type'))
            ->options([
                'Primary' => 'Primary school school',
                'Secondary' => 'O\'level school',
                'Advanced' => 'Both O\'level and A\'level school',
            ])
            ->rules('required');
        $form->radio('has_theology', __('Has theology'))
            ->options([
                'Yes' => 'Yes',
                'No' => 'No',
            ])
            ->rules('required');

        $form->text('hm_name', __('Head Teacher Name'));
        //$form->textarea('welcome_message', __('Welcome_message'));
        $form->quill('welcome_message', __('Welcome_message'));
        $form->text('subdomain', __('Subdomain'));
        $form->image('logo', __('Logo'));
        $form->color('color', __('Primary color'));
        $form->text('phone_number', __('Phone number'));
        $form->text('phone_number_2', __('Phone number 2'));
        $form->text('email', __('Email'))->attribute('type', 'email');
        $form->text('website', __('Website'))->attribute('type', 'Website');
        $form->text('address', __('Address'));
        $form->date('expiry', __('Expiry'));
        $form->textarea('details', __('Details'));
        $form->divider('SCHOOL PAY INFO');


        $form->radio('school_pay_status', __('School-pay Status'))
            ->options([
                'Yes' => 'Yes',
                'No' => 'No',
            ])
            ->when('Yes', function ($form) {
                $form->text('school_pay_code', __('School-pay code'));
                $form->text('school_pay_password', __('School-pay password'));
            });

        $form->radio('has_valid_lisence', __('Has Valid License'))
            ->options([
                'Yes' => 'Yes',
                'No' => 'No',
            ])
            ->rules('required');

        return $form;
    }
}
