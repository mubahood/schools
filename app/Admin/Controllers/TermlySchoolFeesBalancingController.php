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
        $grid->column('from_term_id', __('From Term'))->display(
            function ($x) {
                $t = Term::find($x);
                if ($t != null) {
                    return "TERM " . $t->name_text;
                }
                $y = Term::find($this->term_id);
                if ($y != null) {
                    return "TERM " . $y->name_text;
                }
                return $x;
            }
        )->sortable();

        //to term
        $grid->column('to_term_id', __('To Term'))->display(
            function ($x) {
                $t = Term::find($x);
                if ($t != null) {
                    return "TERM " . $t->name_text;
                }
                return '-';
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

        $grid->column('processed', __('Process Status'))
            ->display(function ($x) {
                if ($x == 'Yes') {
                    return "<span class='label label-success'>Processed</span>";
                } else {
                    return "<span class='label label-warning'>Not Processed</span>";
                }
            })->sortable();

        //action 
        $grid->column('action', __('Action'))->display(function ($x) {
            if($this->processed == 'Yes'){
                return 'N/A';
            } 
            $t = Term::find($this->to_term_id);
            if ($t == null) {
                return 'N/A';
            }
            $url = url('process-termly-school-fees-balancings?id=' . $this->id);
            return '<a href="' . $url . '" class="btn btn-primary" target="_blank">PROCESS NOW</a>';
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
            $form->select('from_term_id', __('From Term'))->options($terms)
                ->rules('required');

            $form->select('to_term_id', __('Term'))->options($terms)
                ->rules('required');
        } else {
            $form->display('from_term_id', __('From Term'))->with(function ($x) {
                $t = Term::find($x);
                if ($t != null) {
                    return "TERM " . $t->name_text;
                }
                return $x;
            });

            $form->display('to_term_id', __('To Term'))->with(function ($x) {
                $t = Term::find($x);
                if ($t != null) {
                    return "TERM " . $t->name_text;
                }
                return $x;
            });
        }

        //target_students_status ACTIVE OR ALL
        $form->radioCard('target_students_status', __('Target Students Status'))
            ->options([
                'Active' => 'Only Active Students',
                'All' => 'All Students',
            ])
            ->rules('required');

        $form->radioCard('processed', __('Process School Fees Balances'))
            ->options([
                'Yes' => 'No',
                'No' => 'Yes',
            ])
            ->rules('required')
            ->when('No', function (Form $form) {
                $form->radioCard('updated_existed_balances', __('Updated Existed Balances'))->default('No')
                    ->options([
                        'Yes' => 'Yes',
                        'No' => 'No',
                    ])
                    ->rules('required');
            });

        $form->disableCreatingCheck();
        $form->disableReset();
        $form->disableViewCheck();

        return $form;
    }
}
