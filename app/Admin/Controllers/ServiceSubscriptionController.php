<?php

namespace App\Admin\Controllers;

use App\Models\Account;
use App\Models\Service;
use App\Models\ServiceSubscription;
use App\Models\StockItemCategory;
use App\Models\Term;
use App\Models\User;
use App\Models\Utils;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Auth;

class ServiceSubscriptionController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Service subscriptions';

    /**
     * @return Grid
     */
    protected function grid()
    {
        $s = ServiceSubscription::find(1667);
        //$s->delete();
        //die("romina");  


        $grid = new Grid(new ServiceSubscription());



        $grid->export(function ($export) {

            $export->filename('Accounts');

            $export->except(['enterprise_id', 'type', 'owner.avatar', 'id']);

            //$export->only(['column3', 'column4']);
            $export->originalValue([]);
            $export->column('administrator_id', function ($value, $original) {
                $u = Administrator::find($original);
                if ($u == null) {
                    return $original;
                }
                return $u->name;
            });
            /*
            $export->column('balance', function ($value, $original) {
                return $original;
            }); */
        });
        $grid->model()->where('enterprise_id', Admin::user()->enterprise_id)
            ->orderBy('id', 'Desc');



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
        if (!isset($_GET['due_term_id'])) {
            $grid->model()->where('due_term_id', $active_term);
        }
        //where sub.status = active
        $grid->model()->whereHas('sub', function ($q) {
            $q->where('status', 1);
        });



        // $grid->disableBatchActions();


        $grid->column('created_at', __('Date'))
            ->display(function () {
                return Utils::my_date_time($this->created_at);
            })
            ->sortable();

        $grid->column('due_term_id', __('Due term'))
            ->display(function () {
                return $this->due_term->name_text;
            })
            ->sortable();

        $grid->actions(function ($actions) {
            $actions->disableView();
        });




        $grid->filter(function ($filter) {
            // Remove the default id filter
            $filter->disableIdFilter();


            $terms = [];
            foreach (
                Term::where(
                    'enterprise_id',
                    Admin::user()->enterprise_id
                )->orderBy('id', 'desc')->get() as $key => $term
            ) {
                $terms[$term->id] = "Term " . $term->name . " - " . $term->academic_year->name;
            }

            $filter->equal('due_term_id', 'Filter by term')
                ->select($terms);

            $u = Admin::user();
            $ajax_url = url(
                '/api/ajax?'
                    . 'enterprise_id=' . $u->enterprise_id
                    . "&search_by_1=name"
                    . "&search_by_2=id"
                    . "&model=User"
            );
            $filter->equal('administrator_id', 'Filter by subscriber')
                ->select(function ($id) {
                    $a = User::find($id);
                    if ($a) {
                        return [$a->id => $a->name];
                    }
                })->ajax($ajax_url);


            $services = [];
            foreach (
                Service::where(
                    'enterprise_id',
                    Admin::user()->enterprise_id
                )->get() as $v
            ) {
                $services[$v->id] = $v->name;
            }

            $filter->equal('service_id', 'Filter by service')
                ->select($services);
        });






        $grid->quickSearch(function ($model, $query) {
            $acc = Administrator::where('name', 'like', "%$query%")
                ->where('enterprise_id', Admin::user()->enterprise_id)
                ->first();

            if ($acc != null) {
                $model->where('administrator_id', $acc->id);
            }
        })->placeholder('Search...');


        $grid->model()->where('enterprise_id', Admin::user()->enterprise_id)
            ->orderBy('id', 'Desc');

        $grid->column('id', __('id'))->sortable()->hide();

        $grid->column('administrator_id', __('Subscriber'))
            ->display(function () {
                if ($this->sub == null) {
                    return $this->administrator_id;
                }

                $link = '<a href="' . admin_url('students/' . $this->administrator_id) . '" title="View profile">' . $this->sub->name . '</a>';
                return $link;
            });

        $grid->column('service_id', __('Service'))->display(function () {
            return $this->service->name;
        })->sortable();

        $grid->column('quantity', __('Quantity'))->sortable();
        $grid->column('total', __('Total fee (UGX)'))->display(function () {
            return  number_format(((int)($this->total)));
        })->totalRow(function ($amount) {
            return   number_format($amount);
        });
        $grid->column('balance', __('Student Balance'))->display(function () {
            if ($this->sub == null) {
                return '-';
            }
            if ($this->sub->account == null) {
                return '-';
            }

            return  number_format(((int)($this->sub->account->balance)));
        });

        //link_with
        $grid->column('link_with', __('Link with'))->display(function () {
            return $this->link_with;
        })->sortable();

        $grid->column('to_be_managed_by_inventory', 'Inventory Mgmt')->display(function ($value) {
            if ($value === 'Yes') {
                return '<span class="label label-info">Yes</span>';
            }
            return '<span class="label label-default" style="background-color: #cececeff;">No</span>';
        })->sortable();

        $grid->column('is_service_offered', 'Inventory Status')->display(function ($value) {
            if ($this->to_be_managed_by_inventory !== 'Yes') {
                return '<span class="label label-default" style="background-color: #cececeff;">N/A</span>';
            }
            
            switch ($value) {
                case 'Yes':
                    return '<span class="label label-success">Offered</span>';
                case 'Pending':
                    return '<span class="label label-warning">Pending</span>';
                case 'Cancelled':
                    return '<span class="label label-danger">Cancelled</span>';
                default:
                    return '<span class="label label-default" style="background-color: #cbcbcbff;">Not Offered</span>';
            }
        })->sortable();

        $grid->column('is_completed', 'Completed')->display(function ($value) {
            if ($value === 'Yes') {
                return '<span class="label label-success">Yes</span>';
            }
            return '<span class="label label-warning">No</span>';
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
        $show = new Show(ServiceSubscription::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('service_id', __('Service id'));
        $show->field('administrator_id', __('Administrator id'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new ServiceSubscription());

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
            $form->select('administrator_id', "Subscriber")
                ->options(function ($id) {
                    $a = Administrator::find($id);
                    if ($a) {
                        return [$a->id => "#" . $a->id . " - " . $a->name];
                    }
                })
                ->ajax($ajax_url)->rules('required');


            $form->select('service_id', 'Select Service')->options(Service::where(
                'enterprise_id',
                Admin::user()->enterprise_id
            )->get()->pluck('name_text', 'id'))->rules('required');


            $form->text('quantity', __('Quantity'))
                ->rules('required|int')
                ->attribute('type', 'number')
                ->default(1);
        } else {
            $form->display('due_term_id', 'Due term')->with(function ($v) {
                return "Term " . Term::find($v)->name_text;
            });
            $form->display('administrator_id', "Subscriber")
                ->with(function ($v) {
                    $a = User::find($v);
                    if ($a) {
                        return "#" . $a->id . " - " . $a->name_text;
                    }
                });
            $form->display('service_id', 'Service')->with(function ($v) {
                return Service::find($v)->name_text;
            });
            $form->display('quantity', __('Quantity'));
            
            // Display items to be offered (inherited from Service)
            $service = Service::find($form->model()->service_id);
            if ($service && $service->to_be_managed_by_inventory === 'Yes' && !empty($service->items_to_be_offered)) {
                $itemNames = [];
                foreach ($service->items_to_be_offered as $itemId) {
                    $category = StockItemCategory::find($itemId);
                    if ($category) {
                        $itemNames[] = $category->name;
                    }
                }
                
                if (!empty($itemNames)) {
                    $form->display('items_to_be_offered_display', 'Items to be Offered')
                        ->with(function () use ($itemNames) {
                            return '<span class="label label-info" style="margin-right: 5px; margin-bottom: 5px; display: inline-block;">' 
                                . implode('</span> <span class="label label-info" style="margin-right: 5px; margin-bottom: 5px; display: inline-block;">', $itemNames) 
                                . '</span>';
                        });
                }
            }
        } 
        
        // Check if this subscription has items to be offered
        if ($form->isEditing()) {
            $subscription = $form->model();
            $service = Service::find($subscription->service_id);
            
            if ($service && $service->to_be_managed_by_inventory === 'Yes' && !empty($service->items_to_be_offered)) {
                $u = Admin::user();
                $stockCategories = [];
                foreach ($service->items_to_be_offered as $itemId) {
                    $category = StockItemCategory::find($itemId);
                    if ($category) {
                        $stockCategories[$itemId] = $category->name;
                    }
                }
                
                $form->checkboxButton('items_have_been_offered', 'Mark Items as Provided')
                    ->options($stockCategories)
                    ->help('Check the items that have been provided to the student');
                    
                // Display status summary
                if (!empty($subscription->items_have_been_offered)) {
                    $providedCount = count($subscription->items_have_been_offered);
                    $totalCount = count($service->items_to_be_offered);
                    $pending = $totalCount - $providedCount;
                    
                    $form->html("<div class='alert alert-info'>
                        <i class='fa fa-info-circle'></i> 
                        <strong>Status:</strong> {$providedCount} of {$totalCount} items provided. 
                        " . ($pending > 0 ? "{$pending} items pending." : "All items provided!") . "
                    </div>");
                }
            } else {
                $form->html('<div class="alert alert-default">
                    <i class="fa fa-info-circle"></i> 
                    This service does not require item tracking.
                </div>');
            }
        }

  

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
                $form->select('transport_route_id', __('Transport Stage'))
                    ->options($routes)
                    ->rules('required');
                $form->radio('trip_type', __('Trip Type'))
                    ->options([
                        'To School' => 'To School',
                        'From School' => 'From School',
                        'Round Trip' => 'Round Trip (To & Fro)',
                    ])->rules('required');
            });

        $form->divider('Add more subscriptions to selected subscriber');
        $form->html('Click on "New Button" to add more subscriptions to the selected subscriber');

        //has many items

        if ($form->isCreating()) {

            $form->hasMany('items', 'Items', function (Form\NestedForm $form) {
                $u = Admin::user();
                $form->hidden('enterprise_id', __('Enterprise id'))->default($u->enterprise_id)->rules('required');
                $form->hidden('is_processed', __('Processed'))->default('No')->rules('required');
                $form->hidden('total', __('Total'))->default(0)->rules('required');

                $form->select('service_id', 'Select Service')->options(Service::where(
                    'enterprise_id',
                    Admin::user()->enterprise_id
                )->get()->pluck('name_text', 'id'))->rules('required');
                $form->decimal('quantity', __('Quantity'))->default(1)->rules('required');
            });
        } else {
            $form->hasMany('items', 'Items', function (Form\NestedForm $form) {
                $u = Admin::user();
                $form->hidden('enterprise_id', __('Enterprise id'))->default($u->enterprise_id)->rules('required');
                $form->hidden('is_processed', __('Processed'))->default('No')->rules('required');
                $form->hidden('total', __('Total'))->default(0)->rules('required');

                $form->select('service_id', 'Select Service')->options(Service::where(
                    'enterprise_id',
                    Admin::user()->enterprise_id
                )->get()->pluck('name_text', 'id'))->rules('required');
                $form->decimal('quantity', __('Quantity'))->default(1)->rules('required');
            })->disableCreate()
                ->disableDelete()
                ->disable();
        }

        $form->disableReset();
        $form->disableViewCheck();
        return $form;
    }
}
