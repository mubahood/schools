<?php

namespace App\Admin\Controllers;

use App\Models\SecondaryTermlyReportCard;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Auth;

class SecondaryTermlyReportCardController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Termly Report Cards';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        /* 

    	do_update	

        */
     /*    $rep = new SecondaryTermlyReportCard();
        $u = Admin::user();
        $ent = Admin::user()->ent;
        $year = $ent->active_academic_year();
        $term = Admin::user()->ent->active_term();
        $rep->enterprise_id = 11;
        $rep->academic_year_id = $year->id;
        $rep->term_id = $term->id;
        $rep->report_title = 'End of term 1 2023' . rand(10000, 1000000);
        $rep->general_commnunication = 'Simple general communication go here. Simple general communication go here. Simple general communication go here. Simple general communication go here.';
        $rep->save();
        dd('done'); */

        $grid = new Grid(new SecondaryTermlyReportCard());

        $grid->actions(function ($act) {
            $act->disableView();
            $act->disableDelete();
        });
        $grid->model()->where([
            'enterprise_id' => Auth::user()->enterprise_id,
        ])
            ->orderBy('id', 'Desc');
        $grid->disableBatchActions();
        $grid->column('id', __('ID'))->sortable();

        $grid->column('report_title', __('Report card'))->sortable();

        $grid->column('academic_year_id', __('Year'))
            ->display(function ($x) {
                if ($this->year == null) {
                    return $x;
                }
                return $this->year->name;
            })
            ->sortable();
        $grid->column('term_id', __('Term'))
            ->display(function ($x) {
                if ($this->term == null) {
                    return $x;
                }
                return $this->term->name;
            })
            ->sortable();

        $grid->column('general_commnunication', __('General commnunication'))->hide();

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
        $show = new Show(SecondaryTermlyReportCard::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('academic_year_id', __('Academic year id'));
        $show->field('term_id', __('Term id'));
        $show->field('report_title', __('Report title'));
        $show->field('general_commnunication', __('General commnunication'));
        $show->field('do_update', __('Do update'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new SecondaryTermlyReportCard());

        $form->number('enterprise_id', __('Enterprise id'));
        $form->number('academic_year_id', __('Academic year id'));
        $form->number('term_id', __('Term id'));
        $form->textarea('report_title', __('Report title'));
        $form->textarea('general_commnunication', __('General commnunication'));
        $form->text('do_update', __('Do update'))->default('No');

        return $form;
    }
}
