<?php

namespace App\Admin\Controllers;

use App\Models\CreditPurchase;
use App\Models\Enterprise;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Auth;

class CreditPurchaseController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Credit Purchasing';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new CreditPurchase());
        $u = Auth::user();
        $grid->disableBatchActions();

        if (!$u->isRole('super-admin')) {
            $grid->model()
                ->where([
                    'enterprise_id' => $u->enterprise_id
                ]);
        }
        $grid->model()
            ->orderBy('id', 'desc');

        $grid->column('id', __('ID'))->sortable();
        $grid->column('created_at', __('Date'))
            ->display(function ($created_at) {
                return date('d-m-Y H:i:s', strtotime($created_at));
            })->sortable();
        $grid->column('amount', __('Amount (UGX)'))
            ->display(function ($amount) {
                return number_format($amount);
            })
            ->sortable();
        $grid->column('payment_status', __('Payment Status'))
            ->display(function ($payment_status) {
                if ($payment_status == 'Not Paid') {
                    return "<span class='label label-danger'>$payment_status</span>";
                } else {
                    return "<span class='label label-success'>$payment_status</span>";
                }
            })->label([
                'Not Paid' => 'danger',
                'Paid' => 'success',
            ])->sortable();
        $grid->column('deposit_status', __('Deposit Status'))
            ->display(function ($deposit_status) {
                if ($deposit_status == 'Not Deposited') {
                    return "<span class='label label-danger'>$deposit_status</span>";
                } else {
                    return "<span class='label label-success'>$deposit_status</span>";
                }
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
        $show = new Show(CreditPurchase::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('amount', __('Amount'));
        $show->field('payment_status', __('Payment status'));
        $show->field('deposit_status', __('Deposit status'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new CreditPurchase());

        $u = Admin::user();
        if ($u->isRole('super-admin')) {
            $form->select('enterprise_id', __('Enterprise'))->options(
                Enterprise::pluck('name', 'id')
            )->rules('required');
        } else {
            $form->hidden('enterprise_id', __('Enterprise id'))->default($u->enterprise_id)->rules('required');
        }
        $form->decimal('amount', __('Amount (UGX)'))->rules('required');

        if ($form->isCreating()) {
            $form->hidden('payment_status', __('Payment status'))->default('Not Paid');
            $form->hidden('deposit_status', __('Deposit status'))->default('Not Deposited');
        } else {
            if ($u->isRole('super-admin')) {
                $form->radioCard('payment_status', __('Payment status'))
                    ->options([
                        'Not Paid' => 'Not Paid',
                        'Paid' => 'Paid',
                    ])
                    ->default('Not Paid');
                $form->radioCard('deposit_status', __('Credit Deposit Status'))
                    ->options([
                        'Diposited' => 'Diposited',
                        'Not Diposited' => 'Not Diposited',
                    ])
                    ->default('Not Diposited');
            }
        }


        return $form;
    }
}
