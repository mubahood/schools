<?php

namespace App\Admin\Controllers;

use App\Models\NurseryTermlyReportCard;
use App\Models\Term;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class NurseryTermlyReportCardController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Nursery Termly Report Cards';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        /* $x = NurseryTermlyReportCard::find(1);
        $x->report_title .= 1; 
        $x->save();
        die("romina");
 */
        $grid = new Grid(new NurseryTermlyReportCard());

        $grid->column('id', __('Id'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        $grid->column('enterprise_id', __('Enterprise id'));
        $grid->column('academic_year_id', __('Academic year id'));
        $grid->column('term_id', __('Term id'));
        $grid->column('report_title', __('Report title'));
        $grid->column('Report cards', __('Report cards'))->display(function () {
            return count($this->nursery_termly_report_cards);
        });
        $grid->column('general_commnunication', __('General commnunication'));

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
        $show = new Show(NurseryTermlyReportCard::findOrFail($id));

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
        $form = new Form(new NurseryTermlyReportCard());
        $u = Admin::user();
        $form->hidden('enterprise_id', __('Enterprise id'))->default($u->enterprise_id)->rules('required');
        $form->hidden('academic_year_id', __('Academic year id'));

        $_terms = Term::where([
            'enterprise_id' => $u->enterprise_id
        ])
            ->orderBy('id', 'DESC')
            ->get();

        $terms = [];
        foreach ($_terms as  $v) {
            $terms[$v->id] = $v->academic_year->name . " - " . $v->name;
        }

        $form->select('term_id', __('Term'))->options($terms)
            ->creationRules(['required']);

        $form->text('report_title', __('Report title'))->rules(['required']);
        $form->textarea('general_commnunication', __('General commnunication'));
        if ($form->isEditing()) {
            $form->radio('do_update', __('Do you want to update all related report cards?'))->options([1 => 'Yes', 0 => 'No'])
                ->default(0);
        }

        return $form;
    }
}
