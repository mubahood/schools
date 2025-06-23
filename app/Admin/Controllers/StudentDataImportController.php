<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Post\DuplicateStudentDataImports;
use App\Models\StudentDataImport;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Facades\Admin;
use App\Models\Utils;

class StudentDataImportController extends AdminController
{
    protected $title = 'Student Data Imports';

    protected function grid()
    {
        $grid = new Grid(new StudentDataImport());

        $grid->batchActions(function ($batch) {
            // … any other batch actions …
            $batch->add(new \App\Admin\Actions\Post\BatchReplicate());
        });
        // scope to current enterprise
        $grid->model()
            ->where('enterprise_id', Admin::user()->enterprise_id)
            ->orderBy('created_at', 'desc');

        // filters
        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->like('title', 'Title');
            $filter->equal('status', 'Status')->select([
                'Pending'    => 'Pending',
                'Processing' => 'Processing',
                'Completed'  => 'Completed',
                'Failed'     => 'Failed',
            ]);
            $filter->between('created_at', 'Created At')->datetime();
        });

        // columns
        $grid->column('id',     'ID')->sortable();
        $grid->column('title',  'Title')->limit(30);
        $grid->column('file_path', 'Import File')->display(function ($path) {
            return $path
                ? "<a href='" . url("storage/{$path}") . "' target='_blank'>Download</a>"
                : '-';
        });
        $grid->column('status', 'Status')->dot([
            'Pending'    => 'warning',
            'Processing' => 'info',
            'Completed'  => 'success',
            'Failed'     => 'danger',
        ]);
        $grid->column('summary', 'Summary')->limit(50);
        $grid->column('created_at', 'Created')
            ->display(fn($v) => Utils::my_date_3($v))
            ->sortable();

        // imported by
        $grid->column('creator.username', 'Imported By')->sortable();

        // import/retry button
        $grid->column('actions', 'Actions')->display(function () {
            $url = url("student-data-import-do-import?id={$this->id}");
            $btn = $this->status === 'Pending'
                ? 'Import Students'
                : 'Try Again';
            $cls = $this->status === 'Pending' ? 'btn-primary' : 'btn-warning';
            return "<a href='{$url}' class='btn btn-xs {$cls}' target='_blank'>{$btn}</a>";
        });


        return $grid;
    }

    protected function detail($id)
    {
        $show = new Show(StudentDataImport::findOrFail($id));

        $show->field('id',        'ID');
        $show->field('enterprise.name', 'Enterprise');
        $show->field('creator.username', 'Imported By');
        $show->field('title',     'Title');
        $show->field('identify_by', 'Identify By');
        $show->field('file_path', 'Import File')->as(function ($path) {
            return $path
                ? "<a href='" . url("storage/{$path}") . "' target='_blank'>Download</a>"
                : '-';
        });
        $show->field('class_column',       'Class Column');
        $show->field('reg_number_column',  'Reg Number Column');
        $show->field('school_pay_column',  'School Pay Column');
        $show->field('name_column',        'Name Column');
        $show->field('gender_column',      'Gender Column');
        $show->field('dob_column',         'Date of Birth Column');
        $show->field('phone_column',       'Phone Column');
        $show->field('email_column',       'Email Column');
        $show->field('address_column',     'Address Column');
        $show->field('parent_name_column', 'Parent Name Column');
        $show->field('parent_phone_column', 'Parent Phone Column');
        $show->field('status',   'Status')
            ->as(fn($s) => ucfirst($s))
            ->label([
                'Pending'    => 'warning',
                'Processing' => 'info',
                'Completed'  => 'success',
                'Failed'     => 'danger',
            ]);
        $show->field('summary', 'Summary')->unescape();
        $show->field('created_at', 'Created At');
        $show->field('updated_at', 'Updated At');

        return $show;
    }

    protected function form()
    {
        $form = new Form(new StudentDataImport());

        // Column choices A–Z
        $cols = array_combine(range('A', 'Z'), range('A', 'Z'));

        // Auto-assign enterprise & creator
        $form->hidden('enterprise_id')->value(Admin::user()->enterprise_id);
        $form->hidden('created_by_id')->value(Admin::user()->id);

        $form->text('title', 'Title')
            ->rules('required|max:255');

        $form->radio('identify_by', 'Identify By')
            ->options([
                'reg_number'            => 'Registration Number',
                'school_pay_account_id' => 'School Pay Account ID',
            ])
            ->rules('required')
            ->when('reg_number', function (Form $form) use ($cols) {
                $form->radio('reg_number_column', 'Reg Number Column')
                    ->options($cols)
                    ->rules('required');
            })
            ->when('school_pay_account_id', function (Form $form) use ($cols) {
                $form->radio('school_pay_column', 'School Pay Column')
                    ->options($cols)
                    ->rules('required');
            });

        $form->radio('class_column', 'Class Column')
            ->options($cols)
            ->rules('required');

        $form->radio('name_column', 'Name Column')
            ->options($cols)
            ->rules('required');

        // OPTIONAL columns
        $form->radio('gender_column', 'Gender Column')
            ->options($cols)
            ->rules('nullable')
            ->help('Optional: select if your sheet includes a gender column.');

        $form->radio('dob_column', 'DOB Column')
            ->options($cols)
            ->rules('nullable')
            ->help('Optional: select if your sheet includes a date-of-birth column.');

        $form->radio('phone_column', 'Phone Column')
            ->options($cols)
            ->rules('nullable')
            ->help('Optional: select if your sheet includes a phone number column.');

        $form->radio('email_column', 'Email Column')
            ->options($cols)
            ->rules('nullable')
            ->help('Optional: select if your sheet includes an email address column.');

        $form->radio('address_column', 'Address Column')
            ->options($cols)
            ->rules('nullable');

        $form->radio('parent_name_column', 'Parent Name Column')
            ->options($cols)
            ->rules('nullable');

        $form->radio('parent_phone_column', 'Parent Phone Column')
            ->options($cols)
            ->rules('nullable');

        $form->file('file_path', 'Import File')
            ->rules('required|mimes:xlsx,csv')
            ->uniqueName();

        $form->radio('status', 'Status')
            ->options([
                'Pending'    => 'Pending',
                'Processing' => 'Processing',
                'Completed'  => 'Completed',
                'Failed'     => 'Failed',
            ])
            ->default('Pending')
            ->readOnly();

        $form->textarea('summary', 'Summary')->readonly();

        // Prevent editing completed imports
        $form->saving(function (Form $form) {
            if ($form->model()->status === 'Completed') {
                admin_error('Not Allowed', 'Cannot modify a completed import.');
                return back();
            }
        });

        // Tidy up footer
        $form->disableReset();
        $form->footer(function ($f) {
            $f->disableViewCheck();
            $f->disableEditingCheck();
            $f->disableCreatingCheck();
        });

        return $form;
    }
}
