<?php

namespace App\Admin\Controllers;

use App\Models\ImportSchoolPayTransaction;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Facades\Admin;

class ImportSchoolPayTransactionController extends AdminController
{
    protected $title = 'Import SchoolPay Transactions';

    protected function grid()
    {
        $grid = new Grid(new ImportSchoolPayTransaction());

        // **Scope to current enterprise**
        $grid->model()->where('enterprise_id', Admin::user()->enterprise_id)
            ->orderBy('created_at', 'desc');
        $cols = array_combine(range('A', 'Z'), range('A', 'Z'));

        $grid->column('id', __('Id'));
        $grid->column('school_pay_transporter_id', __('Transaction ID'))
            ->sortable()
            ->editable('select', $cols);

        //amount
        $grid->column('amount', __('Amount'))
            ->sortable()
            ->editable();

        $grid->column('description', __('Description'))
            ->sortable()
            ->editable();
        $grid->column('payment_date', __('Payment Date'))
            ->sortable()
            ->editable('select', $cols);
        $grid->column('schoolpayReceiptNumber', __('Schoolpay Receipt Number'))
            ->sortable()
            ->editable('select', $cols);

        $grid->column('sourceChannelTransDetail', __('Source Channel Trans Detail'))
            ->sortable()
            ->editable('select', $cols);
        $grid->column('sourceChannelTransactionId', __('Source Channel Transaction ID'))
            ->sortable()
            ->editable('select', $cols);

        $grid->column('sourcePaymentChannel', __('Source Payment Channel'))
            ->sortable()
            ->editable('select', $cols);
        $grid->column('studentClass', __('Student Class'))
            ->sortable()
            ->editable('select', $cols);

        //studentName
        $grid->column('studentName', __('Student Name'))
            ->sortable()
            ->editable('select', $cols);

        //studentPaymentCode
        $grid->column('studentPaymentCode', __('Student Payment Code'))
            ->sortable()
            ->editable('select', $cols);


        //acton buttons
        $grid->column('actions', __('Actions'))->display(function () {
            $import_link = url("import-school-pay-transactions-do-import?id={$this->id}");

            $target = " target='_blank'";


            return "<a href='{$import_link}' class='btn btn-xs btn-primary'{$target}>Do Import Data</a>";
        });

        //studentRegistrationNumber
        $grid->column('studentRegistrationNumber', __('Student Registration Number'))
            ->sortable()
            ->editable('select', $cols);

        //source
        $grid->column('source', __('Source'))
            ->sortable()
            ->editable('select', [
                'school_pay' => 'School Pay',
                'peg_pay' => 'Peg Pay',
                'other' => 'Other',
            ]);


        return $grid;
    }

    protected function detail($id)
    {
        $show = new Show(ImportSchoolPayTransaction::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('school_pay_transporter_id', __('School pay transporter id'));
        $show->field('amount', __('Amount'));
        $show->field('description', __('Description'));
        $show->field('payment_date', __('Payment date'));
        $show->field('schoolpayReceiptNumber', __('SchoolpayReceiptNumber'));
        $show->field('sourceChannelTransDetail', __('SourceChannelTransDetail'));
        $show->field('sourceChannelTransactionId', __('SourceChannelTransactionId'));
        $show->field('sourcePaymentChannel', __('SourcePaymentChannel'));
        $show->field('studentClass', __('StudentClass'));
        $show->field('studentName', __('StudentName'));
        $show->field('studentPaymentCode', __('StudentPaymentCode'));
        $show->field('studentRegistrationNumber', __('StudentRegistrationNumber'));
        $show->field('file_path', __('File path'));
        $show->field('source', __('Source'));

        return $show;
    }

    protected function form()
    {
        $form = new Form(new ImportSchoolPayTransaction());

        $columns = array_combine(range('A', 'Z'), range('A', 'Z'));

        // tie to current enterprise & user
        $form->hidden('enterprise_id')->value(Admin::user()->enterprise_id);

        $form->radio('school_pay_transporter_id', 'Transaction ID')
            ->options($columns)->rules('required')->required();

        $form->radio('amount', 'Amount Column')
            ->options($columns)->rules('required')->required();

        $form->radio('description', 'Description Column')
            ->options($columns)->rules('required')->required();

        $form->radio('payment_date', 'Payment Date Column')
            ->options($columns)->rules('required')->required();

        $form->radio('schoolpayReceiptNumber', 'Schoolpay Receipt Number Column')
            ->options($columns)->rules('required')->required();


        $form->radio('studentName', 'Student Name Column')
            ->options($columns)->rules('required')->required();

        $form->radio('studentPaymentCode', 'Student Payment Code Column')
            ->options($columns)->rules('required')->required();

        $form->radio('studentRegistrationNumber', 'Student Registration Number Column')
            ->options($columns)->rules('required')->required();


        $form->radio('studentClass', 'Student Class Column')
            ->options($columns);


        $form->radio('sourceChannelTransDetail', 'SourceChannelTransDetail Column')
            ->options($columns);

        $form->radio('sourceChannelTransactionId', 'SourceChannelTransactionId Column')
            ->options($columns);

        $form->radio('sourcePaymentChannel', 'SourcePaymentChannel Column')
            ->options($columns);


        $form->file('file_path', 'Import File')
            ->rules('required|mimes:xlsx,csv')
            ->uniqueName();

        $form->select('source', 'Source')
            ->options([
                'school_pay' => 'School Pay',
                'peg_pay' => 'Peg Pay',
                'other' => 'Other',
            ])
            ->rules('required')
            ->required();

        $form->disableReset();


        return $form;
    }
}
