<?php

namespace App\Admin\Controllers;

use App\Models\FeesDataImport;
use App\Models\Utils;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Facades\Admin;

class FeesDataImportController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Fees Data Import';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new FeesDataImport());

        $u =  Admin::user();
        //should only see their own enterprise data
        $grid->model()->where('enterprise_id', $u->enterprise_id)
            ->orderBy('created_at', 'desc');
        // Filters
        $grid->filter(function (Grid\Filter $filter) {

            $filter->like('title', 'Title');
            $filter->equal('status', 'Status')->select([
                'Pending' => 'Pending',
                'Processing' => 'Processing',
                'Completed' => 'Completed',
                'Failed' => 'Failed',
            ]);
            $filter->between('created_at', 'Created At')->datetime();
        });


        $grid->column('title', __('Title'))->limit(30);
        $grid->disableBatchActions();
        $grid->column('created_at', __('Created'))
            ->display(function ($createdAt) {
                return Utils::my_date_3($createdAt);
            });
        $grid->column('identify_by', __('Identify By'));
        $grid->column('file_path', __('Import File'))->display(function ($path) {
            return $path
                ? "<a href='" . url("storage/" . $path) . "' target='_blank'>Download</a>"
                : '-';
        });
        $grid->column('status', __('Status'))->dot([
            'Pending'    => 'warning',
            'Processing' => 'info',
            'Completed'  => 'success',
            'Failed'     => 'danger',
        ]);
        $grid->column('summary', __('Summary'))->limit(50);


        // Columns
        $grid->column('creator_id', __('Imported By'))->display(function ($creatorId) {
            return $this->creator ? $this->creator->username : 'N/A';
        })->sortable();

        //acton buttons
        $grid->column('actions', __('Actions'))->display(function () {
            $import_link = url("fees-data-import-do-import?id={$this->id}");

            $target = " target='_blank'";

            if ($this->status != 'Pending') {
                //try again button
                return "<a href='{$import_link}' class='btn btn-xs btn-warning'{$target}>Try Again</a>";
            }

            return "<a href='{$import_link}' class='btn btn-xs btn-primary'{$target}>Import Data</a>";
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
        $show = new Show(FeesDataImport::findOrFail($id));

        $show->field('id', __('ID'));
        $show->field('enterprise.name', __('Enterprise'));
        $show->field('creator.username', __('Created By'));
        $show->field('title', __('Title'));
        $show->field('identify_by', __('Identify By'));
        $show->field('school_pay_column', __('School Pay Column'));
        $show->field('reg_number_column', __('Reg Number Column'));
        $show->field('services_columns', __('Services Columns'));
        $show->field('current_balance_column', __('Current Balance Column'));
        $show->field('previous_fees_term_balance_column', __('Previous Term Balance Column'));
        $show->field('file_path', __('Import File'))->as(function ($path) {
            return $path
                ? "<a href='" . url("storage/" . $path) . "' target='_blank'>Download</a>"
                : '-';
        });
        $show->field('status', __('Status'))->as(function ($status) {
            return strtoupper($status);
        })->label();
        $show->field('summary', __('Summary'))->unescape();
        $show->field('created_at', __('Created At'));
        $show->field('updated_at', __('Updated At'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new FeesDataImport());
        $collumn_names = [
            'A' => 'A',
            'B' => 'B',
            'C' => 'C',
            'D' => 'D',
            'E' => 'E',
            'F' => 'F',
            'G' => 'G',
            'H' => 'H',
            'I' => 'I',
            'J' => 'J',
            'K' => 'K',
            'L' => 'L',
            'M' => 'M',
            'N' => 'N',
            'O' => 'O',
            'P' => 'P',
            'Q' => 'Q',
            'R' => 'R',
            'S' => 'S',
            'T' => 'T',
            'U' => 'U',
            'V' => 'V',
            'W' => 'W',
            'X' => 'X',
            'Y' => 'Y',
            'Z' => 'Z',
        ];

        // Automatically assign enterprise and creator
        $form->hidden('enterprise_id')->value(Admin::user()->enterprise_id);
        $form->hidden('created_by_id')->value(Admin::user()->id);

        $form->text('title', __('Title'))->rules('required|max:255');
        $form->radio('identify_by', __('Identify By'))
            ->options([
                'school_pay_account_id' => 'School Pay Account ID',
                'reg_number'            => 'Registration Number',
            ])
            ->rules('required')
            ->required()
            ->help('Choose how to identify unique student records.')
            ->when('reg_number', function (Form $form) use ($collumn_names) {
                $form->radio('reg_number_column', __('Reg Number Column'))
                    ->rules('required|max:255')
                    ->options($collumn_names)
                    ->help('Column name that contains registration numbers.');
            })
            ->when('school_pay_account_id', function (Form $form) use ($collumn_names) {
                $form->radio('school_pay_column', __('School Pay Column'))
                    ->rules('required|max:255')
                    ->options($collumn_names)
                    ->help('Column name that contains School Pay Account IDs.');
            });

        $form->checkbox('services_columns', __('Services Columns'))
            ->options($collumn_names)
            ->help('Select columns that contain services subscriptions data.');

        $form->select('Previous Term Balance ColumnPrevious Term Balance ColumnPrevious Term Balance ColumnPrevious Term Balance ColumnPrevious Term Balance ColumnPrevious Term Balance ColumnPrevious Term Balance Column', __('Previous Term Balance Column'))
            ->options($collumn_names)
            ->help('Select the column for previous term balance.');

        $form->radio('current_balance_column', __('Current Balance Column'))
            ->options($collumn_names)
            ->rules('required')
            ->help('Select the column for current balance.');
        //does cater_for_balance cate for negative  sign ? 
        $form->radio('cater_for_balance', __('Does balance column cater for negative sign?'))
            ->options([
                'Yes' => 'Yes',
                'No'  => 'No',
            ])
            ->default('Yes')
            ->required()
            ->rules('required')
            ->help('Does the current balance column cater for negative sign?');

        $form->file('file_path', __('Import File'))
            ->rules('required|mimes:xlsx,csv')
            ->uniqueName()
            ->help('Upload .xlsx or .csv file for import.');



        /*  // Disable delete/edit on completed
        $form->saving(function (Form $form) {
            if ($form->model()->status === 'Completed') {
                admin_error('Not Allowed', 'Cannot modify a completed import.');
                return false;
            }
        }); */


        $form->disableReset();
        $form->disableCreatingCheck();

        return $form;
    }
}
