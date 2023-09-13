<?php

namespace App\Admin\Controllers;

use App\Models\WalletRecord;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class WalletRecordController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Wallet Records';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new WalletRecord());
        $grid->disableCreateButton();
        $grid->disableBatchActions();
        $grid->disableActions();


        $u = Admin::user();
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
                return date('d M Y', strtotime($created_at));
            })->sortable();
        $grid->column('details', __('Description'))->sortable();
        $grid->column('amount', __('Amount UGX'))->display(function ($amount) {
            return number_format($amount);
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
        $show = new Show(WalletRecord::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('details', __('Details'));
        $show->field('amount', __('Amount'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    /*   protected function form()
    {
        $form = new Form(new WalletRecord());

        $form->textarea('details', __('Details'));
        $form->number('amount', __('Amount'));

        return $form;
    } */
}
