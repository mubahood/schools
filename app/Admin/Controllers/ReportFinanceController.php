<?php

namespace App\Admin\Controllers;

use App\Models\ReportFinanceModel;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ReportFinanceController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Financial Reports';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new ReportFinanceModel());

        $grid->model()
            ->where([
                'enterprise_id' => Admin::user()->enterprise_id,
            ])
            ->orderBy('id', 'DESC');
        $grid->column('process_report', __('Process Report'))
            ->display(function ($process_report) {
                return "<a href='" . url('reports-finance-process') . "?id=$this->id' target=\"_blank\">Process Report</a>";
            });

        $grid->column('print_report', __('Process Report'))
            ->display(function () {
                return "<a href='" . url('reports-finance-print') . "?id=$this->id' target=\"_blank\">Print Report</a>";
            });
        $grid->column('academic_year_id', __('Academic year id'))
            ->display(function ($academic_year_id) {
                return $this->academic_year->name;
            });
        $grid->column('term_id', __('Term'))->display(function ($term_id) {
            if ($this->academic_term == null) {
                return $term_id;
            }
            return "Term " . $this->academic_term->name;
        });
        $grid->column('total_expected_service_fees', __('Total expected service fees'));
        $grid->column('total_expected_tuition', __('Total expected tuition'));
        $grid->column('total_payment_school_pay', __('Total payment school pay'));
        $grid->column('total_payment_manual_pay', __('Total payment manual pay'));
        $grid->column('total_payment_mobile_app', __('Total payment mobile app'));
        $grid->column('total_payment_total', __('Total payment total'));
        $grid->column('total_school_fees_balance', __('Total school fees balance'));
        $grid->column('total_budget', __('Total budget'));
        $grid->column('total_expense', __('Total expense'));
        $grid->column('total_stock_value', __('Total stock value'));
        $grid->column('total_bursaries_funds', __('Total bursaries funds'));
        $grid->column('messages', __('Messages'));
        $grid->column('classes', __('Classes'));
        $grid->column('active_studentes', __('Active studentes'));
        $grid->column('active_studentes_ids', __('Active studentes ids'));
        $grid->column('bursaries', __('Bursaries'));
        $grid->column('services', __('Services'));
        $grid->column('services_sub_category', __('Services sub category'));
        $grid->column('budget_vs_expenditure', __('Budget vs expenditure'));
        $grid->column('stocks', __('Stocks'));
        $grid->column('other', __('Other'));

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
        $show = new Show(ReportFinanceModel::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('academic_year_id', __('Academic year id'));
        $show->field('term_id', __('Term id'));
        $show->field('total_expected_service_fees', __('Total expected service fees'));
        $show->field('total_expected_tuition', __('Total expected tuition'));
        $show->field('total_payment_school_pay', __('Total payment school pay'));
        $show->field('total_payment_manual_pay', __('Total payment manual pay'));
        $show->field('total_payment_mobile_app', __('Total payment mobile app'));
        $show->field('total_payment_total', __('Total payment total'));
        $show->field('total_school_fees_balance', __('Total school fees balance'));
        $show->field('total_budget', __('Total budget'));
        $show->field('total_expense', __('Total expense'));
        $show->field('total_stock_value', __('Total stock value'));
        $show->field('total_bursaries_funds', __('Total bursaries funds'));
        $show->field('messages', __('Messages'));
        $show->field('classes', __('Classes'));
        $show->field('active_studentes', __('Active studentes'));
        $show->field('active_studentes_ids', __('Active studentes ids'));
        $show->field('bursaries', __('Bursaries'));
        $show->field('services', __('Services'));
        $show->field('services_sub_category', __('Services sub category'));
        $show->field('budget_vs_expenditure', __('Budget vs expenditure'));
        $show->field('stocks', __('Stocks'));
        $show->field('other', __('Other'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new ReportFinanceModel());

        $form->number('enterprise_id', __('Enterprise id'));
        $form->number('academic_year_id', __('Academic year id'));
        $form->number('term_id', __('Term id'));
        $form->number('total_expected_service_fees', __('Total expected service fees'));
        $form->number('total_expected_tuition', __('Total expected tuition'));
        $form->number('total_payment_school_pay', __('Total payment school pay'));
        $form->number('total_payment_manual_pay', __('Total payment manual pay'));
        $form->number('total_payment_mobile_app', __('Total payment mobile app'));
        $form->number('total_payment_total', __('Total payment total'));
        $form->number('total_school_fees_balance', __('Total school fees balance'));
        $form->number('total_budget', __('Total budget'));
        $form->number('total_expense', __('Total expense'));
        $form->number('total_stock_value', __('Total stock value'));
        $form->number('total_bursaries_funds', __('Total bursaries funds'));
        $form->textarea('messages', __('Messages'));
        $form->textarea('classes', __('Classes'));
        $form->textarea('active_studentes', __('Active studentes'));
        $form->textarea('active_studentes_ids', __('Active studentes ids'));
        $form->textarea('bursaries', __('Bursaries'));
        $form->textarea('services', __('Services'));
        $form->textarea('services_sub_category', __('Services sub category'));
        $form->textarea('budget_vs_expenditure', __('Budget vs expenditure'));
        $form->textarea('stocks', __('Stocks'));
        $form->textarea('other', __('Other'));

        return $form;
    }
}
