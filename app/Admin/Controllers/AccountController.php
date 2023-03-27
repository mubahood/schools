<?php

namespace App\Admin\Controllers;

use App\Models\Account;
use App\Models\AccountParent;
use App\Models\Enterprise;
use App\Models\Utils;
use Dflydev\DotAccessData\Util;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\DB;
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


        /*  $ac = Account::find(881);
        $ac->name .= "1";
        $ac->want_to_transfer = 'Soap';
        $ac->transfer_keyword = 1;
        $ac->save();
        die("done"); */
        $grid = new Grid(new Account());



        $grid->model()
            ->orderBy('id', 'Desc')
            ->where([
                'enterprise_id' => Admin::user()->enterprise_id,
                /*   'type' => 'STUDENT_ACCOUNT' */
            ]);



        $grid->filter(function ($filter) {
            // Remove the default id filter
            $filter->disableIdFilter();



            $filter->equal('account_parent_id', 'Filter by account category')
                ->select(
                    AccountParent::where([
                        'enterprise_id' => Admin::user()->enterprise_id,
                    ])->orderBy('name', 'Asc')->get()->pluck('name', 'id')
                );

            /*             $filter->equal('type', 'Account type')->select(
                [
                    'STUDENT_ACCOUNT' => 'Students\' accounts',
                    'EMPLOYEE_ACCOUNT' => 'Employees\' accounts',
                    'BANK_ACCOUNT' => 'Bank accounts',
                    'CASH_ACCOUNT' => 'Cash accounts',
                ]
            );

            */


            $filter->group('balance', function ($group) {
                $group->gt('greater than');
                $group->lt('less than');
                $group->equal('equal to');
            });
        });


        $grid->disableBatchActions();
        $grid->actions(function ($actions) {
            $actions->disableView();
            $actions->disableDelete();
        });



        $ent = Enterprise::find(Admin::user()->enterprise_id);
        $grid->model()->where([
            'enterprise_id' => Admin::user()->enterprise_id,
            'administrator_id' => $ent->administrator_id,
        ])
            ->orderBy('id', 'Desc');

        $grid->quickSearch('name')->placeholder('Search by account name');
        $grid->column('id', __('#ID'));

        $grid->column('owner.avatar', __('Photo'))
            ->width(80)
            ->hide()
            ->lightbox(['width' => 60, 'height' => 60]);


        $grid->column('created_at', __('Created'))->hide()->sortable();
        $grid->column('type', __('Account type'))->hide()->sortable();

        $grid->column('name', __('Account name'))->sortable();
        $grid->column('account_parent_id', __('Account category'))
            ->display(function () {
                $acc =  Utils::getObject(AccountParent::class, $this->account_parent_id);
                if ($acc == null) {
                    return "None";
                }
                return $acc->name;
            })
            ->sortable();

        /*  $grid->column('name', __('Account Name'))
            ->link()
            ->sortable(); */



        $grid->column('balance', __('Account balance'))->display(function () {
            return "UGX " . number_format($this->balance);
        })->sortable()
            ->totalRow(function ($amount) {
                return  "UGX " . number_format($amount);
            });


        //anjane

        $grid->export(function ($export) {

            $export->filename('Accounts');

            $export->except(['enterprise_id', 'type', 'owner.avatar', 'id']);

            //$export->only(['column3', 'column4']);
            $export->originalValue(['name', 'balance']);
            $export->column('balance', function ($value, $original) {
                return $original;
            });
            $export->column('status', function ($value, $original) {
                if ($original) {
                    return "Verified";
                } else {
                    return "Pending";
                }
            });
            /*
            $export->column('balance', function ($value, $original) {
                return $original;
            }); */
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

        $payable = 0;
        $paid = 0;
        $balance = 0;
        $id = 0;

        if ($form->isEditing()) {

            $params = request()->route()->parameters();
            if (isset($params['account'])) {
                $id =  $params['account'];
            }

            $u = $form->model()->find($id);

            if ($u == null) {
                die("Model not found.");
            }


            foreach ($u->transactions as $key => $v) {
                if ($v->amount < 0) {
                    $payable += $v->amount;
                } else {
                    $paid += $v->amount;
                }
            }
            $balance = $payable + $paid;

            $form->display('name', __('Account name'));
            $form->display('payable', __('Total payable fees'))
                ->default("UGX " . number_format($payable));

            $form->display('paid', __('Total paid fees'))
                ->default("UGX " . number_format($paid));

            $form->display('paid', __('FEES BALANCE'))
                ->default("UGX " . number_format($balance));
            $form->divider();
        }

        if (!$form->isEditing()) {
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
                $administrator_id = $ent->administrator_id;

                if ($administrator_id < 1) {
                    $error = new MessageBag([
                        'title'   => 'Error',
                        'message' => "Account ower ID was not found.",
                    ]);
                    return back()->with(compact('error'));
                }



                $f->administrator_id = $administrator_id;
                return $f;
                /*  $success = new MessageBag([
                'title'   => 'title...',
                'message' => "Good to go!",
            ]);
            return back()->with(compact('success')); */
            });
        }


        $u = Admin::user();
        $ent = Enterprise::find($u->enterprise_id);
        $form->hidden('enterprise_id', __('Enterprise id'))->default($u->enterprise_id)->rules('required');
        $form->hidden('administrator_id', __('Enterprise id'))->default($ent->administrator_id)->rules('required');


        $form->text('name', __('Account name'))
            ->rules('required');

        $form->select('account_parent_id', "Account category")
            ->options(
                AccountParent::where([
                    'enterprise_id' => Admin::user()->enterprise_id
                ])->orderBy('name', 'Asc')->get()->pluck('name', 'id')
            )
            ->rules('required');



        if ($form->isEditing()) {
            $form->radio('status', "Account verification")
                ->options([
                    0 => 'Not verified',
                    1 => 'Account verified',
                ])->rules('required');
        }

        if ($form->isEditing()) {
            $form->radio('new_balance', "Change balance")
                ->options([
                    0 => 'Don\'t change account balance',
                    1 => 'Change account balance',
                ])
                ->when(1, function ($f) {
                    $f->text('new_balance_amount', __('New Account Amount'))
                        ->rules('int')->attribute('type', 'number')
                        ->rules('required');
                })
                ->default(0)
                ->rules('required');
        }

        if ($form->isEditing()) {
            $form->radio('category', "Account type")
                ->options(Utils::account_categories())
                ->readonly()
                ->rules('required');
        } else {
            $form->hidden('type', "Account type")
                ->default('OTHER_ACCOUNT')
                ->rules('required');

            $form->radio('category', "Account type")
                ->options(Utils::account_categories())
                ->readonly()
                ->rules('required');
        }


        $form->radio('transfer_keyword', "Do you want to transfer trannsactions to this account?")
            ->options([
                1 => 'Yes',
                0 => 'No',
            ])->when(1, function ($f) {
                $f->text('want_to_transfer', "Transfer keyword")
                    ->rules('required')
                    ->help("Any transaction containing mentioned keyword in its description should be transfered to this account.");
            })->rules('required');

        $form->textarea('description', __('Account description'));


        /*
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
            });*/



        $form->disableCreatingCheck();
        $form->disableEditingCheck();
        $form->disableReset();
        $form->disableViewCheck();

        return $form;
    }
}
