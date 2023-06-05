<?php

namespace App\Admin\Controllers;

use App\Models\Term;
use App\Models\TermlySchoolFeesBalancing;
use App\Models\Transaction;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class TermlySchoolFeesBalancingController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Termly School Fees Balancing';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new TermlySchoolFeesBalancing());

        $grid->actions(function ($act) {
            $act->disableDelete();
        });

        $grid->model()->where([
            'enterprise_id' => Admin::user()->enterprise_id,
        ])->orderBy('id', 'DESC');

        $grid->disableBatchActions();
        $grid->column('term_id', __('Term'))->display(
            function ($x) {
                $t = Term::find($x);
                if ($t != null) {
                    return "TERM " . $t->name_text;
                }
                return $x;
            }
        )->sortable();

        $grid->column('balance_cd', __('Balance C/D'))->display(
            function ($x) {
                $t = Transaction::where([
                    'type' => 'BALANCE_CARRIED_DOWN',
                    'termly_school_fees_balancing_id' => $this->id,
                ])->sum('amount');
                return '<b><a title="View these transactions" href="transactions?termly_school_fees_balancing_id=' . $this->id . '&type%5B0%5D=BALANCE_CARRIED_DOWN"> UGX ' . number_format($t) . "</a></b>";
            }
        );


        $grid->column('balance_bf', __('Balance B/F'))->display(
            function ($x) {
                $t = Transaction::where([
                    'type' => 'BALANCE_BROUGHT_FORWARD',
                    'termly_school_fees_balancing_id' => $this->id,
                ])->sum('amount');
                return '<b><a title="View these transactions" href="transactions?termly_school_fees_balancing_id=' . $this->id . '&type%5B0%5D=BALANCE_BROUGHT_FORWARD"> UGX ' . number_format($t) . "</a></b>";
            }
        );

        $grid->column('processed', __('Processed'))->hide();

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
        $show = new Show(TermlySchoolFeesBalancing::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('academic_year_id', __('Academic year id'));
        $show->field('term_id', __('Term id'));
        $show->field('processed', __('Processed'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new TermlySchoolFeesBalancing());

        $u = Admin::user();
        $form->hidden('enterprise_id', __('Enterprise id'))->default($u->enterprise_id)->rules('required');

        $terms = [];
        foreach (Term::where([
            'enterprise_id' => $u->enterprise_id
        ])
            ->orderBy('id', 'DESC')
            ->get() as  $v) {
            $terms[$v->id] = $v->academic_year->name . " - Term " . $v->name;
        }

        if ($form->isCreating()) {
            $form->select('term_id', __('Term'))->options($terms)
                ->rules('required');
        } else {
            $form->select('term_id', __('Term'))->options($terms)
                ->readOnly()
                ->rules('required');
        }

        $form->radioCard('processed', __('Process School Fees Balances'))
            ->options([
                'Yes' => 'Yes',
                'No' => 'No',
            ])
            ->rules('required');

        $form->disableCreatingCheck();
        $form->disableReset();
        $form->disableViewCheck();

        return $form;
    }
}
