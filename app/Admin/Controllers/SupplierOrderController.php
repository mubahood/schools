<?php

namespace App\Admin\Controllers;

use App\Models\SupplierOrder;
use App\Models\SupplierProduct;
use App\Models\Utils;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class SupplierOrderController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Orders';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new SupplierOrder());

        $grid->model()->where([
            'enterprise_id' => Admin::user()->enterprise_id,
        ])
            ->orderBy('id', 'Desc');
        $grid->disableBatchActions();

        if (Admin::user()->isRole('supplier')) {
            $grid->model()->where('created_to', Admin::user()->id);
            $grid->disableExport();
            $grid->disableCreateButton();
        } else {
        }

        $grid->column('created_at', __('Date'))
            ->display(function ($x) {
                return Utils::my_date($x);
            })
            ->sortable();
 
        $grid->column('created_to', __('Supplier'));
        $grid->column('payment_method', __('Payment method'));
        $grid->column('payment_account', __('Payment account'));
        $grid->column('payment_transaction_id', __('Payment transaction id'));
        $grid->column('customer_note', __('Customer note'));
        $grid->column('supplier_note', __('Supplier note'));
        $grid->column('amount_payable', __('Amount payable'));
        $grid->column('paid_amount', __('Paid amount'));
        $grid->column('balance', __('Balance'));
        $grid->column('buyer_paid', __('Buyer paid'));
        $grid->column('shipping_method', __('Shipping method'));
        $grid->column('order_status', __('Order status'));
        $grid->column('goods_received', __('Goods received'));
        $grid->column('supplier_paid', __('Supplier paid'));
        $grid->column('invoice', __('Invoice'));
        $grid->column('receipt', __('Receipt'));
        $grid->column('processed', __('Processed'));
        $grid->column('attach_documents', __('Attach documents'));

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
        $show = new Show(SupplierOrder::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('created_by', __('Created by'));
        $show->field('created_to', __('Created to'));
        $show->field('payment_method', __('Payment method'));
        $show->field('payment_account', __('Payment account'));
        $show->field('payment_transaction_id', __('Payment transaction id'));
        $show->field('customer_note', __('Customer note'));
        $show->field('supplier_note', __('Supplier note'));
        $show->field('amount_payable', __('Amount payable'));
        $show->field('paid_amount', __('Paid amount'));
        $show->field('balance', __('Balance'));
        $show->field('buyer_paid', __('Buyer paid'));
        $show->field('shipping_method', __('Shipping method'));
        $show->field('order_status', __('Order status'));
        $show->field('goods_received', __('Goods received'));
        $show->field('supplier_paid', __('Supplier paid'));
        $show->field('invoice', __('Invoice'));
        $show->field('receipt', __('Receipt'));
        $show->field('processed', __('Processed'));
        $show->field('attach_documents', __('Attach documents'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new SupplierOrder());


        $u = Admin::user();


        $form->hidden('enterprise_id', __('Enterprise id'))->default($u->enterprise_id)->rules('required');
        if ($form->isCreating()) {
            if ($u->isRole('supplier')) {
                return admin_warning('Suppliers can post an order.');
            }

            $form->hidden('created_by', __('By'))->default($u->id)->rules('required');
            $form->hidden('created_to', __('To'))->default($u->id)->rules('required');
        }
        if (!$form->isCreating()) {

            if (!$u->isRole('supplier')) {
                $form->radio('buyer_paid', __('Has this Order neen Paid?'))
                    ->options([
                        'Paid' => 'Paid',
                        'Not Paid' => 'Not Paid',
                    ])
                    ->when('Paid', function ($form) {
                        $form->decimal('paid_amount', __('Amount paid'))->rules('required');
                        $form->radio(
                            'payment_method',
                            'Payment method'
                        )->options([
                            'Cash' => 'Cash',
                            'Mobile Money' => 'Mobile Money',
                            'Bank' => 'Bank Transfer',
                            'Cheque' => 'Cheque',
                        ])->rules('required');
                        $form->text(
                            'payment_account',
                            __('Account number or Phone number or Person who received cash')
                        )->rules('required');
                        $form->text(
                            'payment_transaction_id',
                            __('Payment transaction ID or Receipt number')
                        )->rules('required');
                    });
            } else {

                $form->display('paid_amount', __('Amount paid'));
                $form->display('payment_method', __('Payment method'));
                $form->display('payment_account', __('Account number or Phone number or Person who received cash'));
                $form->display('payment_transaction_id', __('Payment transaction ID or Receipt number'));

                $form->radio('supplier_paid', __('Has the buyer Paid?'))
                    ->options([
                        'Paid' => 'Paid',
                        'Not Paid' => 'Not Paid',
                    ])->when('Paid', function ($form) {
                        $form->decimal('balance', __('Balance'))->rules('required');
                    });
            }
        }



        if (!$u->isRole('supplier')) {
            $form->textarea('customer_note', __('Customer note'));
            if (!$form->isCreating()) {
                $form->display('supplier_note', __('Supplier note'));
            }
        } else {
            $form->textarea('supplier_note', __('Supplier note'));
            $form->display('customer_note', __('Customer note'));
        }


        if (!$form->isCreating()) {
            if (!$u->isRole('supplier')) {
                $form->display('amount_payable', __('Total Amount payable (UGX)'));
                $form->display('balance', __('Balance'));
            } else {
                $form->decimal('amount_payable', __('Total Amount payable (UGX)'))->rules('required');
            }


            if (!$u->isRole('supplier')) {
                $form->radioCard('order_status', __('Order status'))
                    ->options([
                        'Pending' => 'Pending',
                        'Received' => 'Received',
                        'Completed' => 'Completed',
                        'Canceled' => 'Canceled',
                    ])->rules('required');
            } else {
                $form->radioCard('order_status', __('Order status'))
                    ->options([
                        'Pending' => 'Pending',
                        'Processing' => 'Processing',
                        'Shipping' => 'Shipping',
                        'Canceled' => 'Canceled',
                    ])->rules('required');
            }


            $form->radio('attach_documents', __('Attach relevant documents'))
                ->options([
                    'Yes' => 'Yes',
                    'No' => 'No',
                ])->when('Yes', function ($form) {

                    $form->file('goods_received', __('Goods received'));
                    $form->file('invoice', __('Invoice'));
                    $form->file('receipt', __('Receipt'));
                });
        } else {
            $form->disableEditingCheck();
        }


        $form->text('shipping_method', __('Shipping method'));

        $form->divider('Order Items');

        $form->morphMany('supplier_order_items', 'Order Items', function (Form\NestedForm $form) {

            $form->html('Click on new to add order item');
            $u = Admin::user();
            $pros = SupplierProduct::get_items($u);
            $form->hidden('enterprise_id')->default($u->enterprise_id);
            $form->hidden('supplier_order_id')->default($u->id);
            $form->select('supplier_product_id')->options($pros)->rules('required');
            $form->decimal('quantity')->rules('required');
        });

        $form->disableCreatingCheck();
        $form->disableReset();

        $form->disableViewCheck();

        return $form;
    }
}
