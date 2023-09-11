<?php

namespace App\Admin\Controllers;

use App\Models\Account;
use App\Models\BulkMessage;
use App\Models\User;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class BulkMessageController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Bulk Messages';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        /* $m = BulkMessage::find(1);
        BulkMessage::do_prepare_messages($m);
        die(); */
        $grid = new Grid(new BulkMessage());

        $grid->column('id', __('Id'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        $grid->column('enterprise_id', __('Enterprise id'));
        $grid->column('message_title', __('Message title'));
        $grid->column('message_body', __('Message body'));
        $grid->column('message_delivery_type', __('Message delivery type'));
        $grid->column('message_delivery_time', __('Message delivery time'));
        $grid->column('send_action', __('Send action'));
        $grid->column('send_confirm', __('Send confirm'));
        $grid->column('clone_action', __('Clone action'));
        $grid->column('clone_confirm', __('Clone confirm'));
        $grid->column('target_types', __('Target types'));
        $grid->column('target_individuals_phone_numbers', __('Target individuals phone numbers'));
        $grid->column('target_teachers_ids', __('Target teachers ids'));
        $grid->column('target_parents_condition_type', __('Target parents condition type'));
        $grid->column('target_parents_condition_phone_numbers', __('Target parents condition phone numbers'));
        $grid->column('target_parents_condition_fees_type', __('Target parents condition fees type'));
        $grid->column('target_parents_condition_fees_status', __('Target parents condition fees status'));
        $grid->column('target_parents_condition_fees_amount', __('Target parents condition fees amount'));

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
        $show = new Show(BulkMessage::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('message_title', __('Message title'));
        $show->field('message_body', __('Message body'));
        $show->field('message_delivery_type', __('Message delivery type'));
        $show->field('message_delivery_time', __('Message delivery time'));
        $show->field('send_action', __('Send action'));
        $show->field('send_confirm', __('Send confirm'));
        $show->field('clone_action', __('Clone action'));
        $show->field('clone_confirm', __('Clone confirm'));
        $show->field('target_types', __('Target types'));
        $show->field('target_individuals_phone_numbers', __('Target individuals phone numbers'));
        $show->field('target_teachers_ids', __('Target teachers ids'));
        $show->field('target_parents_condition_type', __('Target parents condition type'));
        $show->field('target_parents_condition_phone_numbers', __('Target parents condition phone numbers'));
        $show->field('target_parents_condition_fees_type', __('Target parents condition fees type'));
        $show->field('target_parents_condition_fees_status', __('Target parents condition fees status'));
        $show->field('target_parents_condition_fees_amount', __('Target parents condition fees amount'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {

        $form = new Form(new BulkMessage());

        $u = Admin::user();
        $form->hidden('enterprise_id', __('Enterprise id'))
            ->default($u->enterprise_id);
        $form->text('message_title', __('Bulk Messaging Title (Purpose)'))
            ->rules('required');

        $form->radioCard('target_types', __('Target types'))->options([
            'Individuals' => 'Individuals',
            'To Parents' => 'To Parents',
            'To Teachers' => 'To Teachers',
        ])->rules('required')
            ->when('Individuals', function ($form) {
                $form->tags('target_individuals_phone_numbers', __('Target individuals phone numbers'))
                    ->help('Enter phone numbers separated by comma')
                    ->rules('required');
            })->when('To Parents', function ($form) {
                $u = Admin::user();
                $ajax_url = url('/api/ajax-users?enterprise_id=' . $u->enterprise_id . "&user_type=student");
                $form->multipleSelect('target_parents_condition_phone_numbers', "To Parents of students")
                    ->options(function ($ids) {
                        return Administrator::whereIn('id', $ids)->pluck('name', 'id');
                    })
                    ->ajax($ajax_url)->rules('required');

                $form->radioCard('target_parents_condition_type', __('Condition'))->options([
                    'Fees Balance' => 'School Fees Balance Reminder',
                    'Specific Parents' => 'Only Selected Parents',
                ])
                    ->when('Fees Balance', function ($form) {
                        $form->radioCard('target_parents_condition_fees_type', __('School Fees Balance - Condition'))->options([
                            'Equal To' => 'Equal To',
                            'Less Than' => 'Less Than',
                        ])->rules('required');
                        $form->decimal('target_parents_condition_fees_amount', __('School Fees Balance Amount'))
                            ->rules('required');

                        $form->radioCard('target_parents_condition_fees_status', __('Student\'s Account Verification Status'))->options([
                            'Only Verified' => 'Only Verified Accounts',
                            'All' => 'All Accounts'
                        ])->rules('required');
                    })
                    ->rules('required');
            })->when('To Teachers', function ($form) {
                $u = Admin::user();
                $ajax_url = url('/api/ajax-users?enterprise_id=' . $u->enterprise_id . "&user_type=employee");
                $form->multipleSelect('target_teachers_ids', "Target teachers")
                    ->options(function ($ids) {
                        return Administrator::whereIn('id', $ids)->pluck('name', 'id');
                    })
                    ->ajax($ajax_url)->rules('required');
            });


        $form->radioCard('send_action', __('Sending Action'))->options([
            'Send' => 'Send Now',
            'Draft' => 'Save as Draft'
        ])->when('Send', function ($form) {
            $form->radioCard('send_confirm', __('Are you sure you want to send these messages now?'))->options([
                'Yes' => 'Yes',
                'No' => 'No',
            ])->rules('required');
        })->rules('required');


        $form->radioCard('message_delivery_type', __('Message delivery'))->options([
            'Sheduled' => 'Sheduled',
            'Send Now' => 'Send Now'
        ])->when('Sheduled', function ($form) {
            $form->datetime('message_delivery_time', __('Scheduled Message Delivery Time'))->default(date('Y-m-d H:i:s'))->rules('required');
        })->rules('required');

        $form->radioCard('clone_action', __('Duplicate this messaging'))->options([
            'Duplicate' => 'Duplicate',
            'Dont Duplicate' => 'Don\'t Duplicate',
        ])->when('Duplicate', function ($form) {
            $form->radioCard('clone_confirm', __('Are you sure you want to send these messages now?'))->options([
                'Yes' => 'Yes',
                'No' => 'No',
            ])->rules('required');
        })->rules('required');

        $form->radioCard('do_process_messages', __('Process Messages'))->options([
            'Yes' => 'Yes',
            'No' => 'No',
        ])->rules('required');

        $form->textarea('message_body', __('Compose Message'))->rules('required');
        $form->html('<p><b>Available Keywords</b></p>
        <p class="p-0 m-0">Student\'s Name: <code>[STUDENT_NAME]</code></p>
        <p class="p-0 m-0">Parent\'s Name: <code>[PARENT_NAME]</code></p>
        <p class="p-0 m-0">Teacher\'s Name: <code>[TEACHER_NAME]</code></p>
        <p class="p-0 m-0">School fees balance: <code>[FEES_BALANCE]</code></p>
        ', '');

        return $form;
    }
}
