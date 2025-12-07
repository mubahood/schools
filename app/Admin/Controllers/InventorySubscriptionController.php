<?php

namespace App\Admin\Controllers;

use App\Models\Service;
use App\Models\ServiceSubscription;
use App\Models\StockBatch;
use App\Models\Term;
use App\Models\User;
use App\Models\Utils;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Str;

class InventorySubscriptionController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Inventory Management - Service Subscriptions';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new ServiceSubscription());

        // Only show subscriptions managed by inventory
        $grid->model()->where('enterprise_id', Admin::user()->enterprise_id)
            ->where('to_be_managed_by_inventory', 'Yes')
            ->orderBy('id', 'Desc');

        // Get active term for default filter
        $active_term = 0;
        foreach (
            Term::where('enterprise_id', Admin::user()->enterprise_id)
                ->orderBy('id', 'desc')
                ->get() as $term
        ) {
            if ($term->is_active) {
                $active_term = $term->id;
                break;
            }
        }

        if (!isset($_GET['due_term_id']) && $active_term > 0) {
            $grid->model()->where('due_term_id', $active_term);
        }

        // Only show active students
        $grid->model()->whereHas('sub', function ($q) {
            $q->where('status', 1);
        });

        // Disable batch actions and creation
        $grid->disableBatchActions();
        $grid->disableCreateButton();
        
        // Disable deletion
        $grid->actions(function ($actions) {
            $actions->disableView();
            $actions->disableDelete();
        });

        // Export functionality
        $grid->export(function ($export) {
            $export->filename('Inventory_Subscriptions');
            $export->except(['enterprise_id', 'id']);
            $export->column('administrator_id', function ($value, $original) {
                $u = Administrator::find($original);
                return $u ? $u->name : $original;
            });
        });

        // Filters
        $grid->filter(function ($filter) {
            $filter->disableIdFilter();

            // Term filter
            $terms = [];
            foreach (
                Term::where('enterprise_id', Admin::user()->enterprise_id)
                    ->orderBy('id', 'desc')
                    ->get() as $term
            ) {
                $terms[$term->id] = "Term " . $term->name . " - " . $term->academic_year->name;
            }
            $filter->equal('due_term_id', 'Filter by term')->select($terms);

            // Subscriber filter
            $u = Admin::user();
            $ajax_url = url(
                '/api/ajax?'
                    . 'enterprise_id=' . $u->enterprise_id
                    . "&search_by_1=name"
                    . "&search_by_2=id"
                    . "&model=User"
            );
            $filter->equal('administrator_id', 'Filter by student')
                ->select(function ($id) {
                    $a = User::find($id);
                    if ($a) {
                        return [$a->id => $a->name];
                    }
                })->ajax($ajax_url);

            // Service filter
            $services = [];
            foreach (
                Service::where('enterprise_id', Admin::user()->enterprise_id)
                    ->get() as $v
            ) {
                $services[$v->id] = $v->name;
            }
            $filter->equal('service_id', 'Filter by service')->select($services);

            // Inventory status filter
            $filter->equal('is_service_offered', 'Inventory status')->select([
                'No' => 'Not Offered',
                'Pending' => 'Pending',
                'Yes' => 'Offered',
                'Cancelled' => 'Cancelled'
            ]);

            // Completion status filter
            $filter->equal('is_completed', 'Completion status')->select([
                'Yes' => 'Completed',
                'No' => 'Not Completed'
            ]);
        });

        // Quick search
        $grid->quickSearch(function ($model, $query) {
            $acc = Administrator::where('name', 'like', "%$query%")
                ->where('enterprise_id', Admin::user()->enterprise_id)
                ->first();

            if ($acc != null) {
                $model->where('administrator_id', $acc->id);
            }
        })->placeholder('Search student name...');

        // Grid columns
        $grid->column('created_at', __('Date'))
            ->display(function () {
                return Utils::my_date_time($this->created_at);
            })
            ->sortable();

        $grid->column('due_term_id', __('Term'))
            ->display(function () {
                return $this->due_term->name_text;
            })
            ->sortable();

        $grid->column('administrator_id', __('Student'))
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

        $grid->column('quantity', __('Subscription Qty'))->sortable();

        $grid->column('is_service_offered', 'Inventory Status')->display(function ($value) {
            switch ($value) {
                case 'Yes':
                    return '<span class="label label-success"><i class="fa fa-check-circle"></i> Offered</span>';
                case 'Pending':
                    return '<span class="label label-warning"><i class="fa fa-clock-o"></i> Pending</span>';
                case 'Cancelled':
                    return '<span class="label label-danger"><i class="fa fa-times-circle"></i> Cancelled</span>';
                default:
                    return '<span class="label label-default"><i class="fa fa-minus-circle"></i> Not Offered</span>';
            }
        })->sortable();

        $grid->column('is_completed', 'Status')->display(function ($value) {
            if ($value === 'Yes') {
                return '<span class="label label-success">Completed</span>';
            }
            return '<span class="label label-warning">Incomplete</span>';
        })->sortable();

        $grid->column('stock_batch_id', 'Stock Batch')->display(function ($value) {
            if (!$value) {
                return '<span class="text-muted">Not Selected</span>';
            }
            $batch = StockBatch::find($value);
            if (!$batch) {
                return '<span class="text-danger">Batch Not Found</span>';
            }
            return $batch->cat->name . ' <br><small class="text-muted">Batch #' . $batch->id . '</small>';
        })->sortable();

        $grid->column('provided_quantity', 'Provided Qty')->display(function ($value) {
            if (!$value) {
                return '<span class="text-muted">-</span>';
            }
            return '<strong>' . number_format($value, 2) . '</strong>';
        })->sortable();

        $grid->column('stock_record_id', 'Stock Record')->display(function ($value) {
            if (!$value) {
                return '<span class="text-muted">Not Created</span>';
            }
            return '<a href="' . admin_url('stock-records/' . $value) . '" target="_blank" class="btn btn-xs btn-primary"><i class="fa fa-external-link"></i> View #' . $value . '</a>';
        });

        $grid->column('inventory_provided_date', 'Date Provided')->display(function ($value) {
            if (!$value) {
                return '<span class="text-muted">-</span>';
            }
            return Utils::my_date_time($value);
        })->sortable();

        $grid->column('inventory_provided_by_id', 'Provided By')->display(function ($value) {
            if (!$value) {
                return '<span class="text-muted">-</span>';
            }
            $user = Administrator::find($value);
            return $user ? $user->name : 'Unknown';
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
        $show = new Show(ServiceSubscription::findOrFail($id));

        $show->field('id', __('ID'));
        $show->field('created_at', __('Created at'));
        $show->field('administrator_id', __('Student'))->as(function ($value) {
            $user = User::find($value);
            return $user ? $user->name : 'Unknown';
        });
        $show->field('service_id', __('Service'))->as(function ($value) {
            $service = Service::find($value);
            return $service ? $service->name : 'Unknown';
        });
        $show->field('quantity', __('Subscription Quantity'));
        $show->field('total', __('Total Fee'))->as(function ($value) {
            return number_format($value);
        });
        $show->field('is_service_offered', __('Inventory Status'));
        $show->field('is_completed', __('Completion Status'));
        $show->field('stock_batch_id', __('Stock Batch ID'));
        $show->field('provided_quantity', __('Provided Quantity'));
        $show->field('stock_record_id', __('Stock Record ID'));
        $show->field('inventory_provided_date', __('Date Provided'));
        $show->field('inventory_provided_by_id', __('Provided By'))->as(function ($value) {
            $user = Administrator::find($value);
            return $user ? $user->name : 'Unknown';
        });

        return $show;
    }

    /**
     * Make a form builder.
     * Store keepers can only edit inventory-related fields
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new ServiceSubscription());

        // Disable tools
        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
        });

        // Display read-only subscription information
        $form->display('id', __('Subscription ID'));
        
        $form->display('administrator_id', __('Student'))->with(function ($value) {
            $user = User::find($value);
            return $user ? $user->name : 'Unknown';
        });

        $form->display('service_id', __('Service'))->with(function ($value) {
            $service = Service::find($value);
            return $service ? $service->name : 'Unknown';
        });

        $form->display('quantity', __('Subscription Quantity'));

        $form->display('due_term_id', __('Term'))->with(function ($value) {
            $term = Term::find($value);
            return $term ? $term->name_text : 'Unknown';
        });

        $form->divider('Inventory Management');

        // Inventory status field - the main field store keepers can edit
        $form->radio('is_service_offered', 'Inventory Status')
            ->options([
                'No' => 'Not Offered',
                'Pending' => 'Pending',
                'Yes' => 'Offered',
                'Cancelled' => 'Cancelled'
            ])
            ->required()
            ->when('Yes', function (Form $form) {
                // Build stock batch options
                $u = Admin::user();
                $stockBatches = [];
                foreach (
                    StockBatch::where([
                        'enterprise_id' => $u->enterprise_id,
                        'is_archived' => 'No',
                    ])
                        ->where('current_quantity', '>', 0)
                        ->get() as $batch
                ) {
                    $p = Str::plural($batch->cat->measuring_unit);
                    $stockBatches[$batch->id] = $batch->cat->name . " - " . number_format($batch->current_quantity) . " $p available - Batch ##{$batch->id}";
                }
                
                $form->select('stock_batch_id', 'Select Stock Batch')
                    ->options($stockBatches)
                    ->rules('required_if:is_service_offered,Yes');
                
                $form->decimal('provided_quantity', 'Quantity to Provide')
                    ->rules('required_if:is_service_offered,Yes|numeric|min:0.01')
                    ->default(function ($form) {
                        return $form->model()->quantity ?? 1;
                    });
                    
                $form->display('stock_record_id', 'Stock Record ID')
                    ->with(function ($value) {
                        if ($value) {
                            return "<a href='" . admin_url('stock-records/' . $value) . "' target='_blank'><strong>View Stock Record #{$value}</strong></a>";
                        }
                        return '<span class="text-muted">Will be created automatically when you save</span>';
                    });
                    
                $form->display('inventory_provided_date', 'Date Provided')->with(function ($value) {
                    return $value ? Utils::my_date_time($value) : 'Not yet provided';
                });
                
                $form->display('inventory_provided_by_id', 'Provided By')
                    ->with(function ($value) {
                        if ($value) {
                            $user = Administrator::find($value);
                            return $user ? $user->name : 'Unknown';
                        }
                        return 'Not yet provided';
                    });
            })
            ->when('Pending', function (Form $form) {
                $form->html('<div class="alert alert-info">
                    <i class="fa fa-info-circle"></i> 
                    <strong>Pending Status:</strong> Mark as "Offered" once inventory is ready to be provided to the student.
                </div>');
            })
            ->when('Cancelled', function (Form $form) {
                $form->html('<div class="alert alert-warning">
                    <i class="fa fa-exclamation-triangle"></i> 
                    <strong>Cancelled:</strong> This subscription is cancelled. No stock record will be created.
                </div>');
            });

        $form->display('is_completed', 'Completion Status')
            ->with(function ($value) {
                if ($value === 'Yes') {
                    return '<span class="label label-success">Completed</span>';
                }
                return '<span class="label label-warning">Incomplete</span>';
            });

        // Save callback
        $form->saving(function (Form $form) {
            // Ensure hidden fields are not changed
            $form->model()->enterprise_id = $form->model()->getOriginal('enterprise_id');
            $form->model()->service_id = $form->model()->getOriginal('service_id');
            $form->model()->administrator_id = $form->model()->getOriginal('administrator_id');
            $form->model()->quantity = $form->model()->getOriginal('quantity');
            $form->model()->total = $form->model()->getOriginal('total');
            $form->model()->due_academic_year_id = $form->model()->getOriginal('due_academic_year_id');
            $form->model()->due_term_id = $form->model()->getOriginal('due_term_id');
            $form->model()->to_be_managed_by_inventory = 'Yes'; // Ensure it stays as inventory-managed
        });

        return $form;
    }
}
