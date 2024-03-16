<?php

namespace App\Admin\Controllers;

use App\Models\TransportSubscription;
use App\Models\User;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class TransportSubscriptionController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Transport Subscriptions';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new TransportSubscription());
        $grid->filter(
            function ($filter) {
                $filter->disableIdFilter();
                $u = Admin::user();
                $ajax_url = url(
                    '/api/ajax-users?'
                        . 'enterprise_id=' . $u->enterprise_id
                        . "&search_by_1=name"
                        . "&search_by_2=id"
                        . "&user_type=student"
                        . "&model=User"
                );


                $filter->equal('transport_route_id', __('Transport Route'))
                    ->select(
                        \App\Models\TransportRoute::where('enterprise_id', $u->enterprise_id)->get()->pluck('name', 'id')
                    );

                $filter->equal('user_id', __('Filter by Subscriber'))
                    ->select(function ($id) {
                        $a = User::find($id);
                        if ($a) {
                            return [$a->id => $a->name_text];
                        }
                    })->ajax($ajax_url);

                /* 
                $filter->equal('user_id', __('Filter by Subscriber'))
                    ->select(function ($id) {
                        $a = Administrator::find($id);
                        if ($a) {
                            return [$a->id => $a->name_text];
                        }
                    })
                    ->ajax($ajax_url)->rules('required'); */

                $filter->equal('term_id', __('Term'))
                    ->select(
                        \App\Models\Term::where('enterprise_id', $u->enterprise_id)->get()->pluck('name_text', 'id')
                    );
                $filter->equal('trip_type', __('Trip Type'))
                    ->select([
                        'To School' => 'To School',
                        'From School' => 'From School',
                        'Round Trip' => 'Round Trip',
                    ]);
                $filter->equal('status', __('Status'))
                    ->select([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                    ]);
            }
        );
        $u = Admin::user();
        $grid->model()->where('enterprise_id', $u->enterprise_id);
        $grid->disableBatchActions();
        $grid->column('user_id', __('User'))
            ->display(function ($user_id) {
                $u = $this->subscriber;
                if ($u) {
                    return $u->name;
                }
                return "N/A";
            })->sortable();
        $grid->column('transport_route_id', __('Transport Route'))
            ->display(function ($route_id) {
                $r = $this->route;
                if ($r) {
                    return $r->name;
                }
                return "N/A";
            })->sortable();
        $grid->column('term_id', __('Term'))
            ->display(function ($term_id) {
                $t = $this->term;
                if ($t) {
                    return $t->name;
                }
                return "N/A";
            })->sortable();
        // $grid->column('status', __('Status'));
        $grid->column('trip_type', __('Trip Type'))
            ->filter([
                'To School' => 'To School',
                'From School' => 'From School',
                'Round Trip' => 'Round Trip',
            ])->sortable();
        $grid->column('amount', __('Amount'))
            ->display(function ($amount) {
                return "UGX " . number_format($amount);
            })->sortable();
        $grid->column('description', __('Description'))->hide();

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
        $show = new Show(TransportSubscription::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('user_id', __('User id'));
        $show->field('transport_route_id', __('Transport route id'));
        $show->field('term_id', __('Term id'));
        $show->field('status', __('Status'));
        $show->field('trip_type', __('Trip type'));
        $show->field('amount', __('Amount'));
        $show->field('description', __('Description'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new TransportSubscription());
        $u = auth('admin')->user();
        $form->hidden('enterprise_id', __('Enterprise id'))->value($u->enterprise_id);

        $ajax_url = url(
            '/api/ajax-users?'
                . 'enterprise_id=' . $u->enterprise_id
                . "&search_by_1=name"
                . "&search_by_2=id"
                . "&user_type=student"
                . "&model=User"
        );

        $form->select('user_id', "Subscriber")
            ->options(function ($id) {
                $a = Administrator::find($id);
                if ($a) {
                    return [$a->id => "#" . $a->id . " - " . $a->name];
                }
            })
            ->ajax($ajax_url)->rules('required');

        $routes = [];
        foreach (\App\Models\TransportRoute::where('enterprise_id', $u->enterprise_id)->get() as $key => $route) {
            $routes[$route->id] = $route->name;
        }

        $form->select('transport_route_id', __('Transport Rqoute'))
            ->options($routes)
            ->rules('required');
        $active_term = Admin::user()->ent->active_term();
        $form->select('term_id', __('Due Term'))
            ->options(
                \App\Models\Term::where([
                    'enterprise_id' => $u->enterprise_id,
                ])->get()->pluck('name_text', 'id')
            )
            ->default($active_term->id)
            ->rules('required');

        $form->radio('status', __('Status'))
            ->options([
                'active' => 'Active',
                'inactive' => 'Inactive',
            ])->default('active')->rules('required')->required();

        $form->radio('trip_type', __('Trip Type'))
            ->options([
                'To School' => 'To School',
                'From School' => 'From School',
                'Round Trip' => 'Round Trip',
            ])->rules('required')
            ->required();

        if (!$form->isCreating()) {
            $form->decimal('amount', __('Amount'));
        }

        $form->textarea('description', __('Description'));

        return $form;
    }
}
