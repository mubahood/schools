<?php

namespace App\Admin\Controllers;

use App\Models\SecondaryTermlyReportCard;
use App\Models\Term;
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
        $rep = SecondaryTermlyReportCard::find(2);
        SecondaryTermlyReportCard::update_data($rep);
        dd($rep); */
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
        $grid->column('secondary_report_cards', __('Report cards'))
            ->display(function ($x) {
                return count($x);
            })
            ->sortable();

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
        /* $m = SecondaryTermlyReportCard::find(1);
        $m->do_update = 'Yes';
        $m->generate_head_teacher_comment = 'No';
        $m->generate_class_teacher_comment = 'Yes';
        $m->max_score = 3;
        SecondaryTermlyReportCard::update_data($m); 
        dd('done'); */
        $form = new Form(new SecondaryTermlyReportCard());
        $form->hidden('enterprise_id')->default(Auth::user()->enterprise_id);

        $u = Admin::user();
        $term = $u->ent->active_term();
        $form->select('term_id', "Due term")
            ->options(Term::where([
                'enterprise_id' => $u->enterprise_id
            ])
                ->orderBy('id', 'desc')
                ->get()
                ->pluck('name_text', 'id'))
            ->default($term->id)
            ->rules('required');

        $form->text('report_title', __('Report title'));
        $form->checkbox('classes', __('Classes'))
            ->options(\App\Models\AcademicClass::where([
                'enterprise_id' => $u->enterprise_id
            ])
                ->orderBy('id', 'desc')
                ->get()
                ->pluck('name_text', 'id'))->stacked();


        $form->decimal('max_score')->rules('required');
        $form->radioCard('do_update', __('Update Marks'))
            ->options(['Yes' => 'Yes', 'No' => 'No']);

        $form->radioCard('generate_class_teacher_comment', __('Generate Class Teachers Comment'))
            ->options(['Yes' => 'Yes', 'No' => 'No']);
        $form->radioCard('generate_head_teacher_comment', __('Generate Head Teachers Comment'))
            ->options(['Yes' => 'Yes', 'No' => 'No']);

        $form->textarea('general_commnunication', __('General commnunication'));
        $form->textarea('bottom_message', __('Bottom Message'));



        return $form;
    }
}
