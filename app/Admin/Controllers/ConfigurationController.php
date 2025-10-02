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
        $grid->column('logo', __('Logo'))->lightbox(['width' => 100, 'height' => 100]);
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
        $form->text('motto', __('School Motto'))->required();
        $form->image('logo', __('School badge'))->uniqueName()->required()->required();
        $form->text('address', __('School Address'))->required();
        $form->quill('details', __('School details'));
        $form->text('phone_number', __('Phone number'));
        $form->text('phone_number_2', __('Alternative phone number'));
        $form->text('p_o_box', __('P.O.BOX'));
        $form->text('email', __('Email'));
        $form->color('color', __('School Color'))->default('color')->required();
        $form->color('sec_color', __('Secondary color'))->rules('required')->required();
        $form->quill('welcome_message', __('Welcome message'));
        $form->radio('can_send_messages', __('Enable Message Sending'))
            ->options([
                'Yes' => 'Yes',
                'No' => 'No',
            ])->default('No');


        $form->radio('school_pay_import_automatically', __('Import SchoolPay to Students Accounts Automatically?'))
            ->options([
                'Yes' => 'Yes',
                'No' => 'No',
            ])->default('No')
            ->rules('required');
        $form->date('school_pay_last_accepted_date', __('Last SchoolPay Import Date'))->rules('required')->default(date('Y-m-d'));
        
        $form->divider('Online Student Application Settings');
        
        $form->radio('accepts_online_applications', __('Accept Online Student Applications'))
            ->options([
                'Yes' => 'Yes',
                'No' => 'No',
                'Custom' => 'Custom Message (Applications Closed)',
            ])
            ->default('No')
            ->help('Enable or disable online student application portal');
        
        $form->decimal('application_fee', __('Application Fee'))
            ->default(0.00)
            ->help('Fee amount for student application (0 for free)');
        
        $form->quill('application_instructions', __('Application Instructions'))
            ->help('Instructions that will be shown to applicants on the landing page');
        
        $form->textarea('required_application_documents', __('Required Documents (JSON)'))
            ->rows(10)
            ->default(json_encode([
                ['name' => 'Birth Certificate', 'required' => true],
                ['name' => 'Previous School Report', 'required' => true],
                ['name' => 'Passport Photo', 'required' => true],
                ['name' => 'Parent/Guardian ID', 'required' => false],
            ], JSON_PRETTY_PRINT))
            ->help('JSON array of required documents. Format: [{"name": "Document Name", "required": true/false}]');
        
        $form->date('application_deadline', __('Application Deadline'))
            ->help('Last date to accept applications (optional)');
        
        $form->textarea('application_status_message', __('Custom Status Message'))
            ->rows(3)
            ->help('Custom message to show when applications are closed (only used when set to "Custom")');
        
        $form->divider();
        $form->text('hm_name', __('Head Teacher Name'));
        $form->image('hm_signature', __('Head Teacher signature'));
        $form->image('dos_signature', __('D.O.S signature'));
        $form->image('bursar_signature', __('Bursar signature'));

        return $form;
    }
}
