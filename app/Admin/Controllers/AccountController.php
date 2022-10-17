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
        /* $ac = Account::find(810);
        $ac->name .= "11";
        $ac->new_balance = 1;
        $ac->new_balance_amount = 10000;
        $ac->save();
        dd("done"); */
        Utils::reconcile_in_background(Admin::user()->enterprise_id);
        $grid = new Grid(new Account());

        $grid->model()
            ->orderBy('id', 'Desc')
            ->where([
                'enterprise_id' => Admin::user()->enterprise_id,
                'type' => 'STUDENT_ACCOUNT'
            ]);


        $grid->filter(function ($filter) {
            // Remove the default id filter
            $filter->disableIdFilter();

            $u = Admin::user();
            $ajax_url = url(
                '/api/ajax?'
                    . 'enterprise_id=' . $u->enterprise_id
                    . "&search_by_1=name"
                    . "&search_by_2=id"
                    . "&model=Account"
            );

            $filter->equal('id', 'Student')->select()->ajax($ajax_url);
         
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


        $grid->model()->where('enterprise_id', Admin::user()->enterprise_id)
            ->orderBy('id', 'Desc');

        $grid->column('id', __('#ID'));

        $grid->column('owner.avatar', __('Photo'))
            ->width(80)
            ->lightbox(['width' => 60, 'height' => 60])
            ->sortable();


        $grid->column('created_at', __('Created'))->hide()->sortable();
        /* $grid->column('type', __('Account Category'))
            ->filter([
                'STUDENT_ACCOUNT' => 'Student\'s accounts',
                'EMPLOYEE_ACCOUNT' => 'Employees accounts',
                'BANK_ACCOUNT' => 'Bank accounts',
                'CASH_ACCOUNT' => 'Cash accounts',
            ]); */

        /*  $grid->column('name', __('Account Name'))
            ->link()
            ->sortable(); */

        $grid->column('name', __('Account owner'))
            ->display(function () {
                return
                    '<a class="text-dark" href="' . admin_url('students/' . $this->administrator_id) . '">' . $this->name . "</a>";;
            });

        $grid->column('school', __('School pay'))
            ->display(function () {
                if ($this->owner->school_pay_payment_code == null) {
                    return "-";
                }
                if (strlen($this->owner->school_pay_payment_code) < 2) {
                    return "-";
                }
                return $this->owner->school_pay_payment_code;
            });

        $grid->column('balance', __('Account balance'))->display(function () {
            return "UGX " . number_format($this->balance);
        })->sortable();
        $grid->column('status', __('Verification'))
            ->filter([0 => 'Pending', 1 => 'Verified'])
            ->using([0 => 'Pending', 1 => 'Verified'])
            ->label([
                0 => 'danger',
                1 => 'success',
            ])
            ->sortable();

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

        if (!$form->isEditing()) {
            $form->radio('type', "Account type")
                ->options([
                    'BANK_ACCOUNT' => 'Bank account',
                    'OTHER_ACCOUNT' => 'Other account',
                ])->default(-1)
                ->rules('required');
        }

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
