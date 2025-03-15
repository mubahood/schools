<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Post\MessageStatus;
use App\Models\BulkMessage;
use App\Models\DirectMessage;
use App\Models\Utils;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Auth;

class DirectMessageController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Direct Messages';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {

        $grid = new Grid(new DirectMessage());

        $grid->filter(function ($filter) {
            $u = Admin::user();
            $ajax_url = url(
                '/api/ajax?'
                    . 'enterprise_id=' . $u->enterprise_id
                    . "&search_by_1=name"
                    . "&search_by_2=id"
                    . "&model=User"
            );

            $filter->equal('administrator_id', 'Receiver Account')
                ->select()->ajax($ajax_url);


            $filter->equal('bulk_message_id', 'By Message Category')
                ->select(BulkMessage::where('enterprise_id', $u->enterprise_id)->pluck('message_title', 'id'));
        });
        $u = Auth::user();
        $grid->model()
            ->where([
                'enterprise_id' => $u->enterprise_id
            ])
            ->orderBy('id', 'desc');

        $grid->batchActions(function ($batch) {
            $batch->add(new MessageStatus());
        });
        $grid->column('id', __('ID'))->sortable();
        $grid->column('created_at', __('Created'))
            ->display(function ($created_at) {
                return date('d-m-Y H:i:s', strtotime($created_at));
            })->sortable()
            ->hide();
        $grid->column('bulk_message_id', __('Bulk message'))
            ->display(function ($bulk_message_id) {
                if ($this->bulk_message == null) {
                    return '-';
                }
                return $this->bulk_message->message_title;
            })->sortable()
            ->hide();
        $grid->column('administrator_id', __('Receiver'))
            ->display(function ($administrator_id) {
                if ($this->administrator == null) {
                    return '-';
                }
                return $this->administrator->name;
            })->sortable();
        $grid->column('receiver_number', __('Receiver Number'))->sortable();
        $grid->column('message_body', __('Message'))->sortable();
        $grid->column('status', __('Status'))->label([
            'Pending' => 'info',
            'Sent' => 'success',
            'Failed' => 'danger',
            'Draft' => 'warning',
        ])->filter([
            'Pending' => 'Pending',
            'Sent' => 'Sent',
            'Failed' => 'Failed',
            'Draft' => 'Draft',
        ])
            ->sortable();
        $grid->column('is_scheduled', __('Scheduled'))->sortable()->hide();
        $grid->column('delivery_time', __('Delivery Time'))
            ->display(function ($delivery_time) {
                return date('d-m-Y H:i:s', strtotime($delivery_time));
            })
            ->sortable()->hide();
        $grid->column('error_message_message', __('Error message message'))->hide();
        $grid->column('response', __('Response'))->hide();

        //button to resend message
        $grid->column('resend', __('Resend'))->display(function () {

            $url = url('send-message?id' . $this->id);
            // open in new tab
            return "<a href='$url' target='_blank' class='btn btn-xs btn-primary'>Resend</a>"; 
        });


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
        $show = new Show(DirectMessage::findOrFail($id));
        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('bulk_message_id', __('Bulk message id'));
        $show->field('administrator_id', __('Administrator id'));
        $show->field('receiver_number', __('Receiver number'));
        $show->field('message_body', __('Message body'));
        $show->field('status', __('Status'));
        $show->field('is_scheduled', __('Is scheduled'));
        $show->field('delivery_time', __('Delivery time'));
        $show->field('error_message_message', __('Error message message'));
        $show->field('response', __('Response'));
        $show->field('balance', __('Balance'));
        $show->field('STUDENT_NAME', __('STUDENT NAME'));
        $show->field('PARENT_NAME', __('PARENT NAME'));
        $show->field('STUDENT_CLASS', __('STUDENT CLASS'));
        $show->field('TEACHER_NAME', __('TEACHER NAME'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new DirectMessage());

        $form->saved(function (Form $form) {
            Utils::send_messages();
        });
        $u = Auth::user();
        $form->hidden('enterprise_id', __('Enterprise id'))->default($u->enterprise_id);
        $form->select('bulk_message_id', __('Bulk message'))->options(BulkMessage::where('enterprise_id', $u->enterprise_id)->pluck('message_title', 'id'))->rules('required');

        $ajax_url = url('/api/ajax-users?enterprise_id=' . $u->enterprise_id . "");
        $form->select('administrator_id', "Receiver")
            ->options(function ($id) use ($ajax_url) {
                $user = Administrator::find($id);
                if ($user) {
                    return [$user->id => $user->name];
                }
            })
            ->rules('required')
            ->ajax($ajax_url);
        $form->text('receiver_number', __('Receiver number'));
        $form->textarea('message_body', __('Message body'));
        $form->radioCard('status', __('Action'))
            ->options([
                'Pending' => 'Pending',
                'Sent' => 'Sent',
                'Failed' => 'Failed',
                'Draft' => 'Draft',
            ])->default('Pending')
            ->rules('required');
        /*         $form->text('is_scheduled', __('Is scheduled'))->default('No');
        $form->datetime('delivery_time', __('Delivery time'))->default(date('Y-m-d H:i:s')); */

        return $form;
    }
}
