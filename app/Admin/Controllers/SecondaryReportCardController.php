<?php

namespace App\Admin\Controllers;

use App\Models\AcademicClass;
use App\Models\AcademicClassSctream;
use App\Models\SecondaryReportCard;
use App\Models\SecondarySubject;
use App\Models\SecondaryTermlyReportCard;
use App\Models\Term;
use App\Models\TermlySecondaryReportCard;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class SecondaryReportCardController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Secondary Report Cards';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new SecondaryReportCard());
        $u = Admin::user();

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $u = Admin::user();
            $year = $u->ent->active_academic_year();


            $ajax_url = url(
                '/api/ajax-users?'
                    . 'enterprise_id=' . $u->enterprise_id
                    . "&search_by_1=name"
                    . "&search_by_2=id"
                    . "&user_type=student"
                    . "&model=User"
            );
            $ajax_url = trim($ajax_url);
            $filter->equal('administrator_id', 'Filter by student')
                ->select(function ($id) {
                    $a = Administrator::find($id);
                    if ($a) {
                        return [$a->id => $a->name];
                    }
                })->ajax($ajax_url);



            $filter->equal('academic_class_id', 'Filter by Class')
                ->select(AcademicClass::getAcademicClasses(['enterprise_id' => $u->enterprise_id]));

            $filter->equal('academic_class_sctream_id', 'Filter by Stream')
                ->select(AcademicClassSctream::getItemsToArray(['enterprise_id' => $u->enterprise_id]));

            $filter->equal('term_id', 'Filter by Term')
                ->select(Term::getItemsToArray(['enterprise_id' => $u->enterprise_id]));

            /*  $filter->group('average_score', 'Filter by Score', function ($group) {
                $group->gt('greater than');
                $group->lt('less than');
                $group->equal('equal to');
            }); */
        });

        $grid->model()->where([
            'enterprise_id' => $u->enterprise_id,
        ])->orderBy('id', 'desc');
        $grid->disableBatchActions();

        $grid->column('id', __('Id'))->sortable();
        $grid->column('academic_year_id', __('Academic Year'))->hide();
        $grid->column('term_id', __('Term'))
            ->display(function ($x) {
                return "Term " . $this->term->name_text;
            })->sortable();

        $grid->column('administrator_id', __('Student'))
            ->display(function ($x) {
                if ($this->owner == null) {
                    return $x;
                }
                return $this->owner->name;
            })->sortable();
        /*  $grid->column('secondary_termly_report_card_id', __('Termly Report Card'))
            ->display(function ($x) {
                return $this->termly_report_card->name_text;
            })->sortable(); */
        $grid->column('academic_class_id', __('Class'))
            ->display(function ($x) {
                if ($this->academic_class == null) {
                    return '-';
                }
                return $this->academic_class->short_name;
            })
            ->sortable();
        $grid->column('class_teacher_comment', __('Class teacher comment'))->editable();
        $grid->column('head_teacher_comment', __('Head teacher comment'))->editable();

        $grid->column('print', __('PRINT'))
            ->display(function ($x) {
                return "<a href='" . url('/secondary-report-cards-print?secondary_report_card_id=' . $this->id . '') . "' target='_blank'>PRINT</a>";
            });
        //secondary_termly_report_card_id
        $grid->column('secondary_termly_report_card_id', __('Termly Report Card'))
            ->display(function ($x) {
                if ($this->secondary_termly_report_card == null) {
                    return 'N/A';
                }
                return $this->secondary_termly_report_card->report_title;
            })->sortable();

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
        $show = new Show(SecondaryReportCard::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('academic_year_id', __('Academic year id'));
        $show->field('term_id', __('Term id'));
        $show->field('secondary_termly_report_card_id', __('Secondary termly report card id'));
        $show->field('administrator_id', __('Administrator id'));
        $show->field('academic_class_id', __('Academic class id'));
        $show->field('class_teacher_comment', __('Class teacher comment'));
        $show->field('head_teacher_comment', __('Head teacher comment'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new SecondaryReportCard());

        $form->number('enterprise_id', __('Enterprise id'));
        $form->number('academic_year_id', __('Academic year id'));
        $form->number('term_id', __('Term id'));
        $form->number('secondary_termly_report_card_id', __('Secondary termly report card id'));
        $form->number('administrator_id', __('Administrator id'));
        $form->number('academic_class_id', __('Academic class id'));
        $form->textarea('class_teacher_comment', __('Class teacher comment'));
        $form->textarea('head_teacher_comment', __('Head teacher comment'));

        return $form;
    }
}
