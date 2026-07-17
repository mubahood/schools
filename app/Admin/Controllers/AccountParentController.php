<?php

namespace App\Admin\Controllers;

use App\Models\AccountParent;
use App\Models\Term;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Auth;

class AccountParentController extends AdminController
{
    protected $title = 'Departments / Votes';

    protected function grid()
    {
        $grid = new Grid(new AccountParent());

        $grid->disableBatchActions();
        $grid->model()
            ->where('enterprise_id', Admin::user()->enterprise_id)
            ->orderBy('name', 'Asc');

        // Term selector: allow switching term for budget/expense display
        $u = Admin::user();
        $terms = [];
        $activeTerm = $u->ent->dpTerm();
        foreach (
            Term::where('enterprise_id', $u->enterprise_id)
                ->orderBy('id', 'desc')
                ->get() as $t
        ) {
            $terms[$t->id] = 'Term ' . $t->name . ' - ' . $t->academic_year->name;
        }

        $selectedTermId = request('term_id', $activeTerm ? $activeTerm->id : null);
        $selectedTerm = $activeTerm;
        if ($selectedTermId && $selectedTermId != ($activeTerm ? $activeTerm->id : null)) {
            $selectedTerm = Term::find($selectedTermId);
        }

        $grid->filter(function ($filter) use ($terms) {
            $filter->disableIdFilter();
            $filter->like('name', 'Search by name');
            $filter->equal('term_id', 'Display term for budget/expense')
                ->select($terms);
        });

        $grid->quickSearch('name')->placeholder('Search departments...');

        $grid->export(function ($export) {
            $export->filename('Departments');
            $export->except(['actions', 'quick_links']);
        });

        $grid->column('name', 'Department / Vote')->sortable();

        $grid->column('budget', 'Budget')->display(function () use ($selectedTerm) {
            if (!$selectedTerm) return 'UGX 0';
            return 'UGX ' . number_format($this->getBudget($selectedTerm));
        });

        $grid->column('expense', 'Expenditure')->display(function () use ($selectedTerm) {
            if (!$selectedTerm) return 'UGX 0';
            return 'UGX ' . number_format(abs($this->getExpenditure($selectedTerm)));
        });

        $grid->column('balance', 'Balance')->display(function () use ($selectedTerm) {
            if (!$selectedTerm) {
                return '<span class="label label-default">UGX 0</span>';
            }
            $bud = $this->getBudget($selectedTerm);
            $exp = $this->getExpenditure($selectedTerm);
            $bal = $bud + $exp;
            $label = $bal >= 0 ? 'label-success' : 'label-danger';
            return '<span class="label ' . $label . '">UGX ' . number_format($bal) . '</span>';
        });

        $grid->column('accounts_count', 'Accounts')->display(function () {
            $count = \App\Models\Account::where('account_parent_id', $this->id)->count();
            $url = admin_url('accounts?account_parent_id=' . $this->id);
            return "<a href='$url' class='label label-info'>$count accounts</a>";
        });

        $grid->column('quick_links', 'Quick Links')->display(function () use ($selectedTermId) {
            $budgetUrl = admin_url('financial-records-budget?parent_account_id=' . $this->id
                . ($selectedTermId ? '&term_id=' . $selectedTermId : ''));
            $expUrl = admin_url('financial-records-expenditure?parent_account_id=' . $this->id
                . ($selectedTermId ? '&term_id=' . $selectedTermId : ''));
            $accountsUrl = admin_url('accounts?account_parent_id=' . $this->id);
            return "<a href='$budgetUrl' class='btn btn-xs btn-success' title='View budget records'>Budget</a> "
                . "<a href='$expUrl' class='btn btn-xs btn-danger' title='View expenditure records'>Expenditure</a> "
                . "<a href='$accountsUrl' class='btn btn-xs btn-info' title='View accounts in this department'>Accounts</a>";
        });

        $grid->column('description', 'Description')->hide();

        return $grid;
    }

    protected function detail($id)
    {
        $show = new Show(AccountParent::findOrFail($id));

        $show->field('id', 'ID');
        $show->field('name', 'Name');
        $show->field('description', 'Description');
        $show->field('created_at', 'Created');

        return $show;
    }

    protected function form()
    {
        $form = new Form(new AccountParent());

        $u = Admin::user();
        $form->hidden('enterprise_id')->default($u->enterprise_id)->rules('required');
        $form->text('name', 'Department / Vote Name')->rules('required');
        $form->textarea('description', 'Description');

        $form->disableViewCheck();
        $form->disableReset();

        return $form;
    }
}
