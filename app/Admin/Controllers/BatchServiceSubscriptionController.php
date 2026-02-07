<?php

namespace App\Admin\Controllers;

use App\Models\BatchServiceSubscription;
use App\Models\Service;
use App\Models\StockItemCategory;
use App\Models\Term;
use App\Models\User;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class BatchServiceSubscriptionController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Batch Service Subscriptions';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new BatchServiceSubscription());

        $u = Admin::user();
        $grid->model()->where('enterprise_id', $u->enterprise_id)
            ->orderBy('id', 'desc');
        $grid->column('created_at', __('Created'))->hide()->sortable();

        $grid->column('service_id', __('Service'))
            ->display(function ($service_id) {
                $s = Service::find($service_id);
                if ($s == null) {
                    return "<span class='badge badge-danger'>Service not found</span>";
                }
                return $s->name_text;
            })->sortable();
        $grid->column('quantity', __('Quantity'))->sortable();
        // $grid->column('total', __('Total'));
        $grid->column('due_term_id', __('Due term'))
            ->display(function ($due_term_id) {
                $term = Term::find($due_term_id);
                if ($term == null) {
                    return "<span class='badge badge-danger'>Term not found</span>";
                }
                return "Term " . $term->name_text;
            })->sortable()->hide();
        $grid->column('link_with', __('Link With'))->hide();
        $grid->column('transport_route_id', __('Transport route id'))->hide();
        $grid->column('success_count', __('Successful'))->sortable();
        $grid->column('fail_count', __('Failed'))->sortable();
        $grid->column('total_count', __('Totaled'))->sortable();
        $grid->column('trip_type', __('Trip type'))->sortable()->hide();
        $grid->column('administrators', __('Subscribers'))->display(function ($administrators) {
            $users = [];
            foreach ($administrators as $key => $v) {
                $s = User::find($v);
                if ($s == null) {
                    continue;
                }
                $users[] = $s->name_text;
            }
            return implode(", ", $users);
        })->sortable();
        $grid->disableBatchActions();
        $grid->column('processed_notes', __('Processed notes'))
            ->limit(30);
        $grid->column('processed_button', 'Processed')->display(function () {
            if ($this->is_processed == 'Yes') {
                return "<span class='badge badge-success'>Processed</span>";
            }
            return "<a target='_blank' href='process-batch-service-subscriptions?id=" . $this->id . "' class='btn btn-primary'>Process</a>";
        });

        $grid->actions(function ($actions) {
            $actions->disableView();
            if ($actions->row->is_processed != 'Yes') {
                $actions->disableEdit();
            }
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
        $show = new Show(BatchServiceSubscription::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('service_id', __('Service id'));
        $show->field('quantity', __('Quantity'));
        $show->field('total', __('Total'));
        $show->field('due_academic_year_id', __('Due academic year id'));
        $show->field('due_term_id', __('Due term id'));
        $show->field('link_with', __('Link with'));
        $show->field('transport_route_id', __('Transport route id'));
        $show->field('success_count', __('Success count'));
        $show->field('fail_count', __('Fail count'));
        $show->field('total_count', __('Total count'));
        $show->field('trip_type', __('Trip type'));
        $show->field('administrators', __('Administrators'));
        $show->field('is_processed', __('Is processed'));
        $show->field('processed_notes', __('Processed notes'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new BatchServiceSubscription());

        $u = Admin::user();
        $form->hidden('enterprise_id', __('Enterprise id'))->default($u->enterprise_id)->rules('required');


        $u = Admin::user();


        $terms = [];
        $active_term = 0;
        foreach (
            Term::where(
                'enterprise_id',
                Admin::user()->enterprise_id
            )->orderBy('id', 'desc')->get() as $key => $term
        ) {
            $terms[$term->id] = "Term " . $term->name . " - " . $term->academic_year->name;
            if ($term->is_active) {
                $active_term = $term->id;
            }
        }


        //UPDATE service_subscriptions SET due_term_id = 6, due_academic_year_id= 2
        //6
        //2

        $ajax_url = url(
            '/api/ajax-users?'
                . 'enterprise_id=' . $u->enterprise_id
                . "&search_by_1=name"
                . "&search_by_2=id"
                . "&user_type=student"
                . "&model=User"
        );

        if ($form->isCreating()) {

            $form->select('due_term_id', 'Due term')->options($terms)
                ->default($active_term)
                ->rules('required');



            $form->select('service_id', 'Select Service')->options(Service::where(
                'enterprise_id',
                Admin::user()->enterprise_id
            )->get()->pluck('name_text', 'id'))->rules('required');


            $form->text('quantity', __('Quantity'))
                ->rules('required|int')
                ->attribute('type', 'number')
                ->help("How much/many units of this service was subscribed for?");
        } else {
            $form->display('due_term_id', 'Due term')->with(function ($v) {
                return "Term " . Term::find($v)->name_text;
            });

            $form->display('service_id', 'Service')->with(function ($v) {
                return Service::find($v)->name_text;
            });
            $form->display('quantity', __('Quantity'));
        }

        $form->divider('Inventory Management');

        $u = Admin::user();
        $stockItems = StockItemCategory::where('enterprise_id', $u->enterprise_id)
            ->orderBy('name')
            ->get()
            ->pluck('name', 'id')
            ->toArray();

        $form->radio('to_be_managed_by_inventory', 'Manage by Inventory?')
            ->options([
                'No' => 'No - Regular service subscription',
                'Yes' => 'Yes - Track inventory items for this service',
            ])
            ->default('No')
            ->when('Yes', function (Form $form) use ($stockItems) {
                $form->hasMany('batchItems', 'Items to be Offered', function (Form\NestedForm $form) use ($stockItems) {
                    $form->select('stock_item_category_id', 'Stock Item')->options($stockItems)->rules('required');
                    $form->number('quantity', 'Quantity')->default(1)->rules('required|min:1');
                });
            })
            ->help('Select "Yes" to specify inventory items and quantities to track for each subscriber.');

        $form->radioCard('link_with', 'Link this subscription with?')->options([
            'Transport' => 'Transport',
            'Hostel' => 'Hostel',
            'None' => 'None',
        ])->default('None')
            ->when('Transport', function (Form $form) {
                $u = Admin::user();
                $routes = [];
                foreach (\App\Models\TransportRoute::where('enterprise_id', $u->enterprise_id)->get() as $key => $route) {
                    $routes[$route->id] = $route->name;
                }
                $form->select('transport_route_id', __('Transport Rqoute'))
                    ->options($routes)
                    ->rules('required');
                $form->radio('trip_type', __('Trip Type'))
                    ->options([
                        'To School' => 'To School',
                        'From School' => 'From School',
                        'Round Trip' => 'Round Trip (To & Fro)',
                    ])->rules('required');
            });


        $form->divider('Select Subscribers');
        $u = Admin::user();
        $ajax_url = url('/api/ajax-users?user_type=student&enterprise_id=' . $u->enterprise_id . "");
        $form->multipleSelect('administrators', "Select Subscribers")
            ->options(function ($ids) {
                if (!is_array($ids)) {
                    return [];
                }
                $data = User::whereIn('id', $ids)->get();
                $dp = [];
                foreach ($data as $key => $v) {
                    $dp[$v->id] = $v->name_text;
                }
                return $dp;
            })
            ->ajax($ajax_url)->rules('required');

        $form->disableReset();
        $form->disableViewCheck();
        //hidden total
        $form->hidden('total')->default(0);

        /*  
            $table->integer('due_academic_year_id');
            $table->integer('due_term_id');
            $table->string('link_with')->nullable();
            $table->integer('transport_route_id')->nullable();
            $table->integer('success_count')->nullable();
            $table->integer('fail_count')->nullable();
            $table->integer('total_count')->nullable();
            $table->string('trip_type')->nullable();
            $table->text('administrators')->nullable();
            $table->string('is_processed')->default('No');
            $table->text('processed_notes')->nullable();
        */

        //if is editing , let is_processed be radio
        if ($form->isEditing()) {
            $form->radio('is_processed', __('Is Processed'))->options([
                'Yes' => 'Yes',
                'No' => 'No',
            ])->default('No');
        }

        return $form;
    }
}
