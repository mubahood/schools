<?php

namespace App\Admin\Controllers;

use App\Models\Account;
use App\Models\Enterprise;
use App\Models\Utils;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\MessageBag;

class AccountController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Financial Accounts';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        Utils::reconcile_in_background(Admin::user()->enterprise_id);

        $grid = new Grid(new Account());
        $grid->model()->where('enterprise_id', Admin::user()->enterprise_id)
            ->orderBy('id', 'Desc');

        $grid->column('id', __('Account number'));
        $grid->column('created_at', __('Created'))->sortable();
        $grid->column('name', __('Account Name'))->sortable();
        $grid->column('administrator_id', __('Account owner'))
            ->display(function () {
                return $this->owner->name;
            });
        $grid->column('balance', __('Account balance'))->sortable();

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
        $show = new Show(Account::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('administrator_id', __('Administrator id'));
        $show->field('name', __('Name'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Account());

        $form->saving(function ($f) {
            $type = $f->type;
            $u = Admin::user();
            $enterprise_id = $u->enterprise_id;
            $administrator_id = 0;
            $ent =  Enterprise::find($enterprise_id);
            if ($ent == null) {
                die("Enterprise not found.");
            }
            $enterprise_owner_id = $ent->administrator_id;
            if (
                $type == 'BANK_ACCOUNT' ||
                $type == 'CASH_ACCOUNT'
            ) {

                $administrator_id = $ent->administrator_id;
            } else {
                $administrator_id = $f->administrator_id;
            }

            if ($administrator_id < 1) {
                $error = new MessageBag([
                    'title'   => 'Error',
                    'message' => "Account ower ID was not found.",
                ]);
                return back()->with(compact('error'));
            }

            if ($type == 'CASH_ACCOUNT') {
                $acc = Account::where([
                    'type' => 'CASH_ACCOUNT',
                    'enterprise_id' => $enterprise_id,
                ])->first();
                if ($acc != null) {
                    $error = new MessageBag([
                        'title'   => 'Error',
                        'message' => "You can not have multiple cash account.",
                    ]);
                    return back()->with(compact('error'));
                }
            }

            if (
                $administrator_id != $enterprise_owner_id
            ) {
                $acc_1 = Account::where([
                    'administrator_id' => $administrator_id,
                ])->first();
                if ($acc_1 != null) {
                    $error = new MessageBag([
                        'title'   => 'Error',
                        'message' => "Selected user already have a financial account. A regular account cannot have multiple financial accounts.",
                    ]);
                    return back()->with(compact('error'));
                }
            }


            $f->administrator_id = $administrator_id;
            return $f;
            /*  $success = new MessageBag([
                'title'   => 'title...',
                'message' => "Good to go!",
            ]);
            return back()->with(compact('success')); */
        });

        $u = Admin::user();
        $form->hidden('enterprise_id', __('Enterprise id'))->default($u->enterprise_id)->rules('required');

        $form->text('name', __('Account name'))
            ->rules('required');

        $form->radio('type', "Account type")
            ->options([
                'BANK_ACCOUNT' => 'Bank account',
                'CASH_ACCOUNT' => 'Cash account',
                'OTHER_ACCOUNT' => 'Other account',
            ])->default(-1)
            ->rules('required')
            ->when('OTHER_ACCOUNT', function ($f) {
                $u = Admin::user();
                $ajax_url = url(
                    '/api/ajax?'
                        . 'enterprise_id=' . $u->enterprise_id
                        . "&search_by_1=name"
                        . "&search_by_2=id"
                        . "&model=User"
                );
                $f->select('administrator_id', "Account owner")
                    ->options(function ($id) {
                        $a = Account::find($id);
                        if ($a) {
                            return [$a->id => "#" . $a->id . " - " . $a->name];
                        }
                    })
                    ->ajax($ajax_url)->rules('required');
            });

        $form->disableCreatingCheck();
        $form->disableEditingCheck();
        $form->disableReset();
        $form->disableViewCheck();

        return $form;
    }
}
