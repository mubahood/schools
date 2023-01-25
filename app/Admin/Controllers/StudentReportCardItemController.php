<?php

namespace App\Admin\Controllers;

use App\Models\ServiceSubscription;
use App\Models\StudentReportCard;
use App\Models\StudentReportCardItem;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Auth;

class StudentReportCardItemController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Report card items';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new StudentReportCardItem());
        $u = Auth::user();
        $grid->model()
            ->where([
                'enterprise_id' => $u->ent->id
            ])->orderBy('id', 'desc');
        $grid->disableBatchActions();
        $grid->disableActions();
        $grid->disableCreateButton();
        $grid->disableExport();

        $grid->filter(function ($filter) {

            if (
                ((!isset($_GET['student_report_card_id'])) ||
                    (((int)($_GET['student_report_card_id'])) < 1))
            ) {
                $filter->expand();
            }

            // Remove the default id filter
            $filter->disableIdFilter();

            $u = Admin::user();
            $ajax_url = url(
                '/api/report-cards?'
                    . 'enterprise_id=' . $u->enterprise_id
            );

            $filter->equal('student_report_card_id', 'Report card')->select(function ($id) {
                $r = StudentReportCard::find($id);
                if ($r == null) {
                    return  "-";
                }
                return [$r->id => "#{$r->id} - " . $r->owner->name . " - " . $r->termly_report_card->report_title];
            })->ajax($ajax_url);
        });

        if (
            ((!isset($_GET['student_report_card_id'])) ||
                (((int)($_GET['student_report_card_id'])) < 1))
        ) {
            $grid->disableActions();
            return  $grid;
        }



        $url =  admin_url('print?id=' . $_GET['student_report_card_id']);

        admin_info("PRINT", '<a href="' . $url . '" target="_blank">PRINT THIS REPORT CARD</a>');
        $grid->column('main_course_id', __('Subject'))
            ->display(function () {
                return $this->subject->subject_name;
            });
        $grid->column('student_report_card_id', __('Student report card id'))->hide();
        $grid->column('did_bot', __('Did bot'))->hide();
        $grid->column('did_mot', __('Did mot'))->hide();
        $grid->column('did_eot', __('Did eot'))->hide();
        $grid->column('bot_mark', __('B.O.T'))->editable();
        $grid->column('mot_mark', __('M.O.T'))->editable();
        $grid->column('eot_mark', __('E.O.T'))->editable();
        $grid->column('total', __('Total Mark'))->editable();
        $grid->column('grade_name', __('Grade'))->editable();
        $grid->column('aggregates', __('Aggregates'))->editable();
        $grid->column('remarks', __('Remarks'))->editable();
        $grid->column('initials', __('Initials'))->editable();

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
        $show = new Show(StudentReportCardItem::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('main_course_id', __('Main course id'));
        $show->field('student_report_card_id', __('Student report card id'));
        $show->field('did_bot', __('Did bot'));
        $show->field('did_mot', __('Did mot'));
        $show->field('did_eot', __('Did eot'));
        $show->field('bot_mark', __('Bot mark'));
        $show->field('mot_mark', __('Mot mark'));
        $show->field('eot_mark', __('Eot mark'));
        $show->field('grade_name', __('Grade name'));
        $show->field('aggregates', __('Aggregates'));
        $show->field('remarks', __('Remarks'));
        $show->field('initials', __('Initials'));
        $show->field('total', __('Total'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new StudentReportCardItem());

        $form->number('enterprise_id', __('Enterprise id'));
        $form->number('main_course_id', __('Main course id'));
        $form->number('student_report_card_id', __('Student report card id'));
        $form->switch('did_bot', __('Did bot'));
        $form->switch('did_mot', __('Did mot'));
        $form->switch('did_eot', __('Did eot'));
        $form->number('bot_mark', __('Bot mark'));
        $form->number('mot_mark', __('Mot mark'));
        $form->number('eot_mark', __('Eot mark'));
        $form->textarea('grade_name', __('Grade name'));
        $form->number('aggregates', __('Aggregates'));
        $form->textarea('remarks', __('Remarks'));
        $form->textarea('initials', __('Initials'));
        $form->decimal('total', __('Total'));

        return $form;
    }
}
