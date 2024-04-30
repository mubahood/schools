<?php

namespace App\Admin\Controllers;

use App\Models\AcademicClass;
use App\Models\ReportCardPrint;
use App\Models\TermlyReportCard;
use App\Models\TheologyClass;
use App\Models\TheologyMarkRecord;
use App\Models\TheologyTermlyReportCard;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ReportCardPrintController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Report Cards';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new ReportCardPrint());
        $u = Admin::user();
        $grid->model()->where([
            'enterprise_id' => $u->enterprise_id
        ])->orderBy('id', 'desc');
        $grid->column('title', __('Title'))->sortable();
        $grid->column('type', __('Type'))->sortable();
        $grid->column('theology_termly_report_card_id', __('Theology Report Card'))
            ->display(function ($f) {
                $rep = TheologyMarkRecord::find($f);
                if ($rep == null) {
                    return "N/A";
                }
                return $rep->title;
            })->sortable();
        $grid->column('termly_report_card_id', __('Termly report card id'));
        $grid->column('academic_class_id', __('Academic class id'));
        $grid->column('theology_class_id', __('Theology class id'));
        $grid->column('download_link', __('Download link'));
        $grid->column('re_generate', __('Re generate'));
        $grid->column('theology_tempate', __('Theology tempate'));
        $grid->column('secular_tempate', __('Secular tempate'));
        $grid->column('print', __('PRINT'))
            ->display(function ($f) {
                $url = url("/report-card-printings?id=$this->id");
                return "<a href='$url' target='_blank'>Print</a>";
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
        $show = new Show(ReportCardPrint::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('title', __('Title'));
        $show->field('type', __('Type'));
        $show->field('theology_termly_report_card_id', __('Theology termly report card id'));
        $show->field('termly_report_card_id', __('Termly report card id'));
        $show->field('academic_class_id', __('Academic class id'));
        $show->field('theology_class_id', __('Theology class id'));
        $show->field('download_link', __('Download link'));
        $show->field('re_generate', __('Re generate'));
        $show->field('theology_tempate', __('Theology tempate'));
        $show->field('secular_tempate', __('Secular tempate'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new ReportCardPrint());
        $u = Admin::user();

        $form->hidden('enterprise_id', __('Enterprise id'))->default($u->enterprise_id);
        $form->text('title', __('Title'))->rules('required');

        $u = Admin::user();
        $reports = TermlyReportCard::where([
            'enterprise_id' => $u->enterprise_id
        ])
            ->orderBy('id', 'desc')
            ->get()->pluck('report_title', 'id');
        $form->select('termly_report_card_id', __('Termly report card'))
            ->options($reports)->rules('required');
            
        $form->radio('type', __('Type'))->options([
            'Secular' => 'Secular',
            'Theology' => 'Theology',
        ])->rules('required')
            ->when('Secular', function ($form) {
                $u = Admin::user();
            

                $classes = AcademicClass::where([
                    'enterprise_id' => $u->enterprise_id
                ])->orderBy('id', 'desc')
                    ->get()->pluck('name_text', 'id');
                $form->select('academic_class_id', __('Class'))
                    ->options($classes)->rules('required');

                $form->radioCard('secular_tempate', __('Theology tempate'))
                    ->options([
                        'Template_5' => 'Template 5',
                    ])->default('No');
            })->when('Theology', function ($form) {
                $u = Admin::user();
                /* $reports = TheologyTermlyReportCard::where([
                    'enterprise_id' => $u->enterprise_id
                ])
                    ->orderBy('id', 'desc')
                    ->get()->pluck('report_title', 'id');
                $form->select('theology_termly_report_card_id', __('Theology Termly report card'))
                    ->options($reports)->rules('required'); */


                $classes = TheologyClass::where([
                    'enterprise_id' => $u->enterprise_id
                ])->orderBy('id', 'desc')
                    ->get()->pluck('name_text', 'id');
                $form->select('theology_class_id', __('Theology Class'))
                    ->options($classes)->rules('required');
                $form->radioCard('theology_tempate', __('Theology tempate'))
                    ->options([
                        'Template_6' => 'Template 6',
                    ])->default('No');
            });

        $form->radio('re_generate', __('Re Generate Reports'))->options([
            'No' => 'No',
            'Yes' => 'Yes',
        ])->rules('required');

        return $form;
    }
}
