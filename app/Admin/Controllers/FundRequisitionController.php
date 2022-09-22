<?php

namespace App\Admin\Controllers;

use App\Models\Account;
use App\Models\Enterprise;
use App\Models\FundRequisition;
use App\Models\StockItemCategory;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Str;


class FundRequisitionController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Funds requisition form';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new FundRequisition());

        $grid->model()->where('enterprise_id', Admin::user()->enterprise_id);
        if (!Admin::user()->isRole('bursar')) {
            $grid->model()->where('applied_by', Admin::user()->id)
                ->orderBy('id', 'Desc');
        }

        $grid->actions(function ($actions) {
            if ($actions->row['status'] == 'Approved') {
                $actions->disableEdit();
                $actions->disableDelete();
            }
        });


        $grid->column('id', __('Id'));
        $grid->column('created_at', __('Created'));
        $grid->column('stock_item_category_id', __('Item'))->display(function () {
            return $this->cat->name;
        })->sortable();

        $grid->column('applied_by', __('Applied by'))->display(function () {
            return $this->appliedBy->name;
        })->sortable();


        $grid->column('quantity', __('Quantity'))
            ->display(function ($x) {
                return number_format($x) . " " . Str::plural($this->cat->measuring_unit);
            })->sortable();

        $grid->column('total_amount', __('Amount'))
            ->display(function ($x) {
                return "UGX " . number_format($x);
            })->sortable();


        $grid->column('status', __('Status'))->filter([
            'Pending' => 'Pending',
            'Rejected' => 'Rejected',
            'Approved' => 'Approved',
        ])->sortable()
            ->label([
                'Pending' => 'warning',
                'Rejected' => 'danger',
                'Approved' => 'success',
            ]);

        $grid->column('invoice', __('Invoice'))->downloadable(url('public/storage'))->hide();
        $grid->column('description', __('Description'))->hide();
        $grid->column('approved_by', __('Approved by'))->display(function () {
            if ($this->approvedBy == null) {
                return "-";
            }
            return $this->approvedBy->name;
        })->hide();


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
        $show = new Show(FundRequisition::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('stock_item_category_id', __('Stock item category id'));
        $show->field('applied_by', __('Applied by'));
        $show->field('approved_by', __('Approved by'));
        $show->field('quantity', __('Quantity'));
        $show->field('total_amount', __('Total cost (in UGX)'));
        $show->field('invoice', __('Invoice'));
        $show->field('status', __('Status'));
        $show->field('description', __('Description'));
        $show->field('deleted_at', __('Deleted at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        /* $f = FundRequisition::find(1);
        $f->description .= time();
        $f->save();
        die("Romina"); */
        $form = new Form(new FundRequisition());

        $form->hidden('enterprise_id')->rules('required')->default(Admin::user()->enterprise_id)
            ->value(Admin::user()->enterprise_id);


        $cats = [];
        foreach (StockItemCategory::where([
            'enterprise_id' => Admin::user()->enterprise_id,
        ])->get() as $val) {
            $p = Str::plural($val->measuring_unit);
            $cats[$val->id] = $val->name . " - (in $p)";
        }

        $form->select('stock_item_category_id', 'Item')
            ->options($cats)->rules('required');


        if ($form->isCreating()) {
            $form->hidden('applied_by')->rules('required')->default(Admin::user()->id)
                ->value(Admin::user()->id)->required();
            $form->hidden('approved_by')->rules('required')->default(0)
                ->value(0)->required();


            $form->hidden('status')->rules('required')->value('Pending')->default('Pending')->required();
        }

        if ($form->isEditing()) {
            $form->hidden('approved_by')->rules('required')->default(Admin::user()->id)
                ->value(Admin::user()->id)->required();
        }


        $form->decimal('quantity', __('Quantity (in Units)'))
            ->attribute('type', 'number')
            ->rules('required');

        $form->decimal('total_amount', __('Total amount'))
            ->attribute('type', 'number')
            ->rules('required');


        if (Admin::user()->isRole('bursar')) {
            if ($form->isEditing()) {
                $form->radio('status', __('Status'))
                    ->options([
                        'Rejected' => 'Reject',
                        'Approved' => 'Approve (Funds released)',
                    ])
                    ->rules('required')
                    ->when('Approved', function ($f) {
                        $u = Admin::user();


                        $from_cats = [];
                        $ent = Enterprise::find(Admin::user()->enterprise_id);

                        foreach (Account::where([
                            'administrator_id' => $ent->administrator_id,
                        ])->get() as $a) {
                            $balance = number_format($a->balance);
                            $from_cats[$a->id]  = "#" . $a->id . " - " . $a->name . " (UGX $balance)";
                        }

                        $f->select('account_from', 'From account')
                            ->options($from_cats)->rules('required');
                        $f->select('account_to', 'To account')
                            ->options($from_cats)->rules('required');
                    })->help('<b>NOTE:<b> Once you approve this requision, you cannot reverse the process.');
            }
        } else {
        }





        $form->file('invoice', __('Attach invoice photo'));
        $form->textarea('description', __('Description'));

        $form->disableReset();
        $form->disableEditingCheck();
        $form->disableCreatingCheck();
        return $form;
    }
}
