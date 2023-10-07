<?php

namespace App\Admin\Controllers;

use App\Models\AcademicClass;
use App\Models\AcademicClassSctream;
use App\Models\SecondaryReportCard;
use App\Models\SecondaryReportCardItem;
use App\Models\SecondarySubject;
use App\Models\SecondaryTermlyReportCard;
use App\Models\Term;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class SecondaryReportCardItemController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Report Card Items';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        /* 

academic_year_id		
            

*/
        $grid = new Grid(new SecondaryReportCardItem());
        $grid->disableCreateButton();
        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $u = Admin::user();
            $year = $u->ent->active_academic_year();
            $subs = SecondarySubject::get_active_subjects($year->id, true);


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

            $filter->equal('secondary_subject_id', 'Filter by Subject')
                ->select($subs);
            $filter->group('average_score', 'Filter by Score', function ($group) {
                $group->gt('greater than');
                $group->lt('less than');
                $group->equal('equal to');
            });
        });

        $grid->actions(function ($act) {
            $act->disableView();
            $act->disableDelete();
        });

        $grid->disableBatchActions();
        $u = Admin::user();
        $grid->model()->where([
            'enterprise_id' => $u->enterprise_id,
        ]);

        $grid->model()->orderBy('id', 'desc');
        $grid->column('id', __('Id'))->sortable();

        $grid->column('academic_class_id', __('Class'))
            ->display(function ($x) {
                if ($this->academic_class == null) {
                    return '-';
                }
                return $this->academic_class->short_name;
            })
            ->sortable();
        $grid->column('academic_class_sctream_id', __('Strem'))
            ->display(function ($x) {
                if ($this->academic_class_stream == null) {
                    return '-';
                }
                return $this->academic_class_stream->name;
            })
            ->sortable();

        $grid->column('administrator_id', __('Student'))
            ->display(function ($x) {
                if ($this->student == null) {
                    return '-';
                }
                return $this->student->name;
            })
            ->sortable();


        $grid->column('secondary_subject_id', __('Subject'))
            ->display(function ($x) {
                if ($this->secondary_subject == null) {
                    return $x;
                }
                return $this->secondary_subject->subject_name . " - " . $this->secondary_subject->code;
            })
            ->sortable();
        $grid->column('average_score', __('Score'))->sortable();
        $grid->column('generic_skills', __('Generic Skills'))->editable();
        $grid->column('remarks', __('Genral Remarks'))->editable();
        $grid->column('teacher', __('Teacher'))->hide();

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
        $show = new Show(SecondaryReportCardItem::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('academic_year_id', __('Academic year id'));
        $show->field('secondary_subject_id', __('Secondary subject id'));
        $show->field('secondary_report_card_id', __('Secondary report card id'));
        $show->field('average_score', __('Average score'));
        $show->field('generic_skills', __('Generic skills'));
        $show->field('remarks', __('Remarks'));
        $show->field('teacher', __('Teacher'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new SecondaryReportCardItem());

        $form->decimal('average_score', __('Average score'))->default(0.00);
        $form->textarea('generic_skills', __('Generic skills'));
        $form->textarea('remarks', __('Remarks'));
        $form->text('teacher', __('Teacher'));
        return $form;
    }
}
