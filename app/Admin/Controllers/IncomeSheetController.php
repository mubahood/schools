<?php

namespace App\Admin\Controllers;

use App\Models\IncomeSheet;
use App\Models\Transaction;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class IncomeSheetController extends AdminController
{
    protected $title = 'Income Sheets';

    protected function grid()
    {
        $grid = new Grid(new IncomeSheet());

        $grid->model()
            ->where('enterprise_id', Admin::user()->enterprise_id)
            ->orderBy('id', 'DESC');

        $grid->column('id', __('ID'))->sortable();

        $grid->column('print_report', __('Print'))
            ->display(function () {
                return "<a href='" . url('income-sheet-print') . "?id=$this->id' target='_blank'>Print PDF</a>";
            });

        $grid->column('title', __('Title'));
        $grid->column('date_from', __('Date From'));
        $grid->column('date_to', __('Date To'));
        $grid->column('type', __('Type'))->label('primary');
        $grid->column('sources', __('Sources'))->display(function ($sources) {
            if (empty($sources)) return '<span class="label label-default">ALL</span>';
            return collect($sources)->map(function ($s) {
                return '<span class="label label-info">' . e(str_replace('_', ' ', $s)) . '</span>';
            })->implode(' ');
        });
        $grid->column('status', __('Status'))->label([
            'Not Generated' => 'default',
            'Generated' => 'success',
        ]);
        $grid->column('created_at', __('Created'));

        return $grid;
    }

    protected function detail($id)
    {
        $show = new Show(IncomeSheet::findOrFail($id));
        $show->field('id', __('ID'));
        $show->field('title', __('Title'));
        $show->field('date_from', __('Date From'));
        $show->field('date_to', __('Date To'));
        $show->field('type', __('Type'));
        $show->field('sources', __('Sources'))->as(function ($sources) {
            if (empty($sources)) return 'ALL';
            return collect($sources)->map(fn($s) => str_replace('_', ' ', $s))->implode(', ');
        });
        $show->field('status', __('Status'));
        $show->field('created_at', __('Created'));
        return $show;
    }

    protected function form()
    {
        $form = new Form(new IncomeSheet());

        $form->hidden('enterprise_id')->default(Admin::user()->enterprise_id);

        $form->text('title', __('Title'))
            ->placeholder('e.g. Income Sheet - Term 1 2025')
            ->rules('required');

        $form->date('date_from', __('Date From'))->rules('required');
        $form->date('date_to', __('Date To'))->rules('required');

        $form->radio('type', __('Type'))
            ->options([
                'DAY_AND_BOARDING' => 'Day and Boarding',
                'DAY' => 'Day Only',
                'BOARDING' => 'Boarding Only',
            ])
            ->default('DAY_AND_BOARDING');

        $sources = Transaction::select('source')
            ->distinct()
            ->whereNotNull('source')
            ->where('source', '!=', '')
            ->where('enterprise_id', Admin::user()->enterprise_id)
            ->pluck('source', 'source')
            ->mapWithKeys(function ($val) {
                return [$val => str_replace('_', ' ', $val)];
            })
            ->toArray();

        $form->checkbox('sources', __('Payment Sources'))
            ->options($sources)
            ->help('Select which payment sources to include. Leave empty to include ALL sources.');

        return $form;
    }
}
