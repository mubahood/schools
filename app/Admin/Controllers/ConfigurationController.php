<?php

namespace App\Admin\Controllers;

use App\Models\Enterprise;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Auth;

class ConfigurationController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'System Configuration';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Enterprise());
        $grid->disableCreateButton();
        $grid->disableBatchActions();
        $grid->model()->where([
            'id' => Auth::user()->enterprise_id
        ]);

        $grid->column('name', __('School Name'));
        $grid->column('short_name', __('Short name'));
        $grid->column('logo', __('Logo'));
        $grid->column('phone_number', __('Phone number'));
        $grid->column('email', __('Email'));
        $grid->column('address', __('Address'));


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

        $show->field('name', __('School Name'));
        $show->field('short_name', __('Short name'));
        $show->field('logo', __('Logo'));
        $show->field('phone_number', __('Phone number'));
        $show->field('email', __('Email'));
        $show->field('address', __('Address'));
        $show->field('expiry', __('Expiry'));
        $show->field('administrator_id', __('Administrator id'));
        $show->field('subdomain', __('Subdomain'));
        $show->field('color', __('Color'));
        $show->field('welcome_message', __('Welcome message'));
        $show->field('type', __('Type'));

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
        $form->disableCreatingCheck();
        $form->disableReset();
        $form->disableViewCheck();

        $form->text('name', __('School Name'))->required();
        $form->text('short_name', __('Short name'))->required();
        $form->image('logo', __('School badge'))->required();
        $form->text('address', __('School Address'))->required();
        $form->quill('details', __('School details'))->required();
        $form->text('phone_number', __('Phone number'))->required();
        $form->text('phone_number_2', __('Alternative phone number'))->required();
        $form->text('p_o_box', __('P.O.BOX'))->required();
        $form->email('email', __('Email'))->required();
        $form->color('color', __('School Color'))->default('color')->required();
        $form->quill('welcome_message', __('Welcome message'));
        $form->divider();
        $form->image('hm_signature', __('Head Teacher signature'));
        $form->image('dos_signature', __('Head Teacher signature'));
        $form->image('bursar_signature', __('Bursar signature')); 

        return $form;
    }
}
