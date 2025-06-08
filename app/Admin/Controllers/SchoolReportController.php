<?php

namespace App\Admin\Controllers;

use App\Models\SchoolReport;
use App\Models\Term;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class SchoolReportController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'School Fees Reports';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new SchoolReport());
        $u = Admin::user();
        $grid->model()->where('enterprise_id', $u->enterprise_id)
            ->orderBy('created_at', 'desc');
        $grid->column('created_at', __('Created At'))->display(function ($value) {
            return date('Y-m-d H:i', strtotime($value));
        })->sortable();
        /*     
        $grid->column('updated_at', __('Updated At'))->display(function ($value) {
            return date('Y-m-d H:i', strtotime($value));
        }); */
        $grid->disableBatchActions();
        $grid->column('term_id', __('Term'))->display(function ($termId) {
            $term = \App\Models\Term::find($termId);
            return $term ? 'Term ' . $term->name_text : '-';
        })->sortable();
        $grid->column('total_students', __('Total Students'))->sortable();
        $grid->column('expected_fees', __('Expected Fees'))->display(function ($value) {
            return number_format($value);
        })->sortable();
        $grid->column('fees_collected_manual_entry', __('Manual Entry'))->display(function ($value) {
            return number_format($value);
        });
        $grid->column('fees_collected_schoolpay', __('SchoolPay'))->display(function ($value) {
            return number_format($value);
        });
        $grid->column('fees_collected_other', __('Other'))->display(function ($value) {
            return number_format($value);
        });
        $grid->column('fees_collected_total', __('Total Collected'))->display(function ($value) {
            return '<b>' . number_format($value) . '</b>';
        });
        $grid->column('pdf_path', __('PDF'))->display(function ($path) {
            if ($path) {
                return '<a href="' . asset($path) . '" target="_blank" class="btn btn-xs btn-info">View PDF</a>';
            }
            return '-';
        });
        $grid->column('generate_button', __('Generate Report'))->display(function () {
            $url = url('generate-school-report?id=' . $this->id);
            return '<a href="' . $url . '" class="btn btn-sm btn-primary" target="_blank">Generate Report</a>';
        });

        $grid->disableExport();
        $grid->filter(function ($filter) {
            $filter->like('term_id', 'Term');
            $filter->between('created_at', 'Created At')->datetime();
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
        $show = new Show(SchoolReport::findOrFail($id));


        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('term_id', __('Term id'));
        $show->field('pdf_path', __('Pdf path'));
        $show->field('total_students', __('Total students'));
        $show->field('expected_fees', __('Expected fees'));
        $show->field('fees_collected_manual_entry', __('Fees collected manual entry'));
        $show->field('fees_collected_schoolpay', __('Fees collected schoolpay'));
        $show->field('fees_collected_total', __('Fees collected total'));
        $show->field('fees_collected_other', __('Fees collected other'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new SchoolReport());
        $u = Admin::user();
        $form->hidden('enterprise_id', __('Enterprise id'))->default($u->enterprise_id);
        $terms = [];
        foreach (
            Term::where('enterprise_id', $u->enterprise_id)
                ->orderBy('id', 'desc')
                ->get() as $term
        ) {
            $terms[$term->id] = 'Term ' . $term->name_text;
        }
        $form->select('term_id', __('Select Term'))
            ->options($terms)
            ->rules('required');
        /*      $form->textarea('pdf_path', __('Pdf path'));
        $form->number('total_students', __('Total students'));
        $form->number('expected_fees', __('Expected fees'));
        $form->number('fees_collected_manual_entry', __('Fees collected manual entry'));
        $form->number('fees_collected_schoolpay', __('Fees collected schoolpay'));
        $form->number('fees_collected_total', __('Fees collected total'));
        $form->number('fees_collected_other', __('Fees collected other')); */

        return $form;
    }
}
