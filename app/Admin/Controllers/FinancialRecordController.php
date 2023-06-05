<?php

namespace App\Admin\Controllers;

use App\Models\Account;
use App\Models\FinancialRecord;
use App\Models\Term;
use Dflydev\DotAccessData\Util;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FinancialRecordController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Financial Records';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new FinancialRecord());

        $grid->column('id', __('Id'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        $grid->column('enterprise_id', __('Enterprise id'));
        $grid->column('account_id', __('Account id'));
        $grid->column('academic_year_id', __('Academic year id'));
        $grid->column('term_id', __('Term id'));
        $grid->column('parent_account_id', __('Parent account id'));
        $grid->column('created_by_id', __('Created by id'));
        $grid->column('amount', __('Amount'));
        $grid->column('termly_school_fees_balancing_id', __('Termly school fees balancing id'));
        $grid->column('description', __('Description'));
        $grid->column('type', __('Type'));
        $grid->column('payment_date', __('Payment date'));

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
        $show = new Show(FinancialRecord::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('account_id', __('Account id'));
        $show->field('academic_year_id', __('Academic year id'));
        $show->field('term_id', __('Term id'));
        $show->field('parent_account_id', __('Parent account id'));
        $show->field('created_by_id', __('Created by id'));
        $show->field('amount', __('Amount'));
        $show->field('termly_school_fees_balancing_id', __('Termly school fees balancing id'));
        $show->field('description', __('Description'));
        $show->field('type', __('Type'));
        $show->field('payment_date', __('Payment date'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new FinancialRecord());
        $u = Auth::user();
        $form->hidden('enterprise_id', __('Enterprise id'))->default($u->enterprise_id)->rules('required');
        if ($form->isCreating()) {
            $form->hidden('created_by_id', __('Enterprise id'))->default($u->id)->rules('required');
        }

        $type = "";
        $segments = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
        if (in_array('financial-records-expenditure', $segments)) {
            $form->text('type', __('Record Type'))
                ->value('EXPENDITURE')
                ->readonly()
                ->rules('required');
            $type = 'Expenditure ';
        }
        if (in_array('financial-records-budget', $segments)) {
            $form->text('type', __('Record Type'))
                ->readonly()
                ->value('BUDGET')
                ->rules('required');
            $type = 'Budget ';
        }
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
        $form->date('payment_date', __('Due Date'))->default(date('Y-m-d'))->rules('required');
        $form->divider();

        $ajax_url = url(
            '/api/ajax?'
                . 'enterprise_id=' . $u->enterprise_id
                . "&search_by_1=name"
                . "&search_by_2=id"
                . "&model=Account"
        );
        $ajax_url = trim($ajax_url);

        $accs = [];
        foreach (Account::where([
            'enterprise_id' => $u->enterprise_id,
            'type' => 'OTHER_ACCOUNT'
        ])
            ->get() as $val) {
            if ($val->account_parent_id == null) {
                continue;
            }

            $accs[$val->id] = $val->getName();
        }


        $form->select('account_id', "Account")
            ->options($accs)->rules('required');


        //$form->number('academic_year_id', __('Academic year id'));
        //$form->number('term_id', __('Term id'));
        //$form->number('parent_account_id', __('Parent account id'));
        //$form->number('created_by_id', __('Created by id'));
        //$form->number('termly_school_fees_balancing_id', __('Termly school fees balancing id'));
        $form->decimal('amount', __($type . 'Amount'))->rules('required');
        $form->text('description', __('Description'))->rules('required');

        $form->disableViewCheck();
        $form->disableReset();


        return $form;
    }
}
