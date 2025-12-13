<?php

namespace App\Admin\Controllers;

use App\Models\ServiceItemToBeOffered;
use App\Models\ServiceSubscription;
use App\Models\StockItemCategory;
use App\Models\StockRecord;
use App\Models\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Auth;

class ServiceItemToBeOfferedController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Service Items Tracking';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new ServiceItemToBeOffered());

        // Apply enterprise filter
        $u = Auth::user();
        if ($u) {
            $grid->model()->where('enterprise_id', $u->enterprise_id);
        }

        // Default ordering
        $grid->model()->orderBy('id', 'desc');

        // Eager load relationships for performance
        $grid->model()->with(['serviceSubscription.service', 'serviceSubscription.subscriber', 
            'stockItemCategory', 'stockBatch', 'user', 'offeredBy']);

        // Filters
        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            
            $filter->equal('service_subscription_id', 'Subscription ID')->integer();
            
            $filter->equal('stock_item_category_id', 'Stock Item')->select(function () {
                $u = Auth::user();
                return StockItemCategory::where('enterprise_id', $u->enterprise_id)
                    ->pluck('name', 'id');
            });
            
            $filter->equal('is_service_offered', 'Offered Status')->select([
                'Yes' => 'Offered',
                'No' => 'Not Offered'
            ]);
            
            $filter->between('created_at', 'Created Date')->date();
            $filter->between('offered_at', 'Offered Date')->date();
        });

        // Grid columns
        $grid->column('id', 'ID')->sortable()->width(80);
        
        $grid->column('created_at', 'Date')->display(function ($date) {
            return date('M d, Y', strtotime($date));
        })->sortable()->width(120);

        $grid->column('service_subscription_id', 'Subscription')->display(function ($id) {
            $sub = $this->serviceSubscription;
            if ($sub) {
                $studentName = $sub->subscriber ? $sub->subscriber->name : 'N/A';
                return "<a href='/admin/service-subscriptions/{$id}' target='_blank'>
                    <strong>#{$id}</strong><br/>
                    <small>{$studentName}</small>
                </a>";
            }
            return "#$id";
        })->width(180);

        $grid->column('service_name', 'Service')->display(function () {
            return $this->serviceSubscription->service->name ?? 'N/A';
        })->width(150);

        $grid->column('item_name', 'Item')->display(function () {
            return $this->stockItemCategory->name ?? 'N/A';
        })->sortable()->width(200);

        $grid->column('quantity', 'Quantity')
            ->editable()
            ->totalRow()
            ->sortable()
            ->width(100);

        $grid->column('is_service_offered', 'Status')->display(function ($status) {
            if ($status === 'Yes') {
                return "<span class='label label-success'>Offered</span>";
            }
            return "<span class='label label-warning'>Pending</span>";
        })->sortable()->width(100);

        $grid->column('stock_batch_id', 'Batch')->display(function ($batchId) {
            if ($batchId && $this->stockBatch) {
                $batch = $this->stockBatch;
                return "<a href='/admin/stock-records/{$batchId}' target='_blank'>
                    <strong>#{$batchId}</strong><br/>
                    <small>Qty: {$batch->current_quantity}</small>
                </a>";
            }
            return "<span class='text-muted'>Not Assigned</span>";
        })->width(120);

        $grid->column('offered_at', 'Offered Date')->display(function ($date) {
            if ($date) {
                return date('M d, Y', strtotime($date));
            }
            return '<span class="text-muted">-</span>';
        })->sortable()->width(120);

        $grid->column('offered_by_id', 'Offered By')->display(function ($userId) {
            if ($userId && $this->offeredBy) {
                return $this->offeredBy->name;
            }
            return '<span class="text-muted">-</span>';
        })->width(150);

        // Export
        $grid->export(function ($export) {
            $export->filename('Service_Items_Tracking_' . date('Y-m-d'));
            $export->column('id', 'ID');
            $export->column('created_at', 'Date Created');
            $export->column('service_subscription_id', 'Subscription ID');
            $export->column('item_name', 'Item');
            $export->column('quantity', 'Quantity');
            $export->column('is_service_offered', 'Status');
            $export->column('stock_batch_id', 'Batch ID');
            $export->column('offered_at', 'Offered Date');
        });

        // Disable create button (records are auto-generated)
        $grid->disableCreateButton();

        // Conditional actions
        $grid->actions(function ($actions) {
            // Can't delete items that have been offered
            if ($this->row->is_service_offered === 'Yes') {
                $actions->disableDelete();
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
        $show = new Show(ServiceItemToBeOffered::findOrFail($id));

        $show->field('id', 'ID');
        $show->field('created_at', 'Date Created')->as(function ($date) {
            return \App\Models\Utils::my_date_time($date);
        });

        $show->divider();

        $show->field('service_subscription_id', 'Service Subscription')->as(function ($subId) {
            if ($this->serviceSubscription) {
                $sub = $this->serviceSubscription;
                $student = $sub->subscriber ? $sub->subscriber->name : 'N/A';
                $service = $sub->service ? $sub->service->name : 'N/A';
                return "#{$subId} - {$student} ({$service})";
            }
            return "#$subId";
        })->link(function () {
            return '/admin/service-subscriptions/' . $this->service_subscription_id;
        });

        $show->field('stock_item_category_id', 'Stock Item')->as(function ($itemId) {
            return $this->stockItemCategory ? $this->stockItemCategory->name : "Item #$itemId";
        })->link(function () {
            return '/admin/stock-item-categories/' . $this->stock_item_category_id;
        });

        $show->divider();

        $show->field('quantity', 'Quantity');
        
        $show->field('is_service_offered', 'Offered Status')->using([
            'Yes' => 'Offered',
            'No' => 'Not Offered'
        ])->label([
            'Yes' => 'success',
            'No' => 'warning'
        ]);

        $show->field('stock_batch_id', 'Stock Batch')->as(function ($batchId) {
            if ($batchId && $this->stockBatch) {
                return "Batch #{$batchId} (Qty: {$this->stockBatch->current_quantity})";
            }
            return 'Not Assigned';
        })->link(function () {
            return $this->stock_batch_id ? '/admin/stock-records/' . $this->stock_batch_id : null;
        });

        $show->divider();

        $show->field('offered_at', 'Offered Date')->as(function ($date) {
            return $date ? \App\Models\Utils::my_date_time($date) : 'Not yet offered';
        });

        $show->field('offered_by_id', 'Offered By')->as(function ($userId) {
            return $this->offeredBy ? $this->offeredBy->name : 'N/A';
        });

        $show->divider();

        $show->field('remarks', 'Remarks');

        $show->field('user_id', 'Created By')->as(function ($userId) {
            return $this->user ? $this->user->name : "User #$userId";
        });

        $show->field('updated_at', 'Last Updated')->as(function ($date) {
            return \App\Models\Utils::my_date_time($date);
        });

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new ServiceItemToBeOffered());

        $u = Auth::user();

        // Warning message for manual creation
        if (!$form->isEditing()) {
            $form->html('<div class="alert alert-warning">
                <i class="fa fa-exclamation-triangle"></i> 
                <strong>Note:</strong> Service items are normally auto-generated when subscriptions are created. 
                Manual creation should only be used for special cases.
            </div>');
        }

        // Service Subscription - with AJAX search
        $form->select('service_subscription_id', 'Service Subscription')->options(function ($id) use ($u) {
            if ($id) {
                $sub = ServiceSubscription::find($id);
                if ($sub) {
                    $student = $sub->subscriber ? $sub->subscriber->name : 'N/A';
                    $service = $sub->service ? $sub->service->name : 'N/A';
                    return [$id => "#{$id} - {$student} ({$service})"];
                }
            }
            return [];
        })->ajax('/admin/api/service-subscriptions', 'id', 'subscriber.name')
            ->rules('required')
            ->help('Select the service subscription this item belongs to');

        // Stock Item Category
        $form->select('stock_item_category_id', 'Stock Item')->options(function ($id) use ($u) {
            if ($id) {
                $item = StockItemCategory::find($id);
                return $item ? [$id => $item->name] : [];
            }
            return StockItemCategory::where('enterprise_id', $u->enterprise_id)
                ->pluck('name', 'id');
        })->rules('required')
            ->help('Select the stock item to be offered');

        // Quantity
        $form->number('quantity', 'Quantity')
            ->default(1)
            ->min(1)
            ->rules('required|integer|min:1')
            ->help('Number of items to be offered');

        // Offered Status
        $form->radio('is_service_offered', 'Offered Status')
            ->options([
                'No' => 'Not Offered',
                'Yes' => 'Offered'
            ])
            ->default('No')
            ->rules('required')
            ->help('Has this item been offered to the student?');

        // Stock Batch - with validation
        $form->select('stock_batch_id', 'Stock Batch')->options(function ($id) use ($u) {
            if ($id) {
                $batch = StockRecord::find($id);
                if ($batch) {
                    return [$id => "Batch #{$id} - {$batch->stockItemCategory->name} (Qty: {$batch->current_quantity})"];
                }
            }
            return [];
        })->ajax('/admin/api/stock-records', 'id', 'stockItemCategory.name')
            ->help('Select the stock batch used to fulfill this item');

        // Remarks
        $form->textarea('remarks', 'Remarks')
            ->rows(3)
            ->help('Any additional notes about this item delivery');

        // Auto-set fields on save
        $form->saving(function (Form $form) use ($u) {
            // Set enterprise and user IDs for new records
            if (!$form->model()->id) {
                $form->enterprise_id = $u->enterprise_id;
                $form->user_id = $u->id;
            }

            // Validate stock batch matches item category
            if ($form->stock_batch_id) {
                $batch = StockRecord::find($form->stock_batch_id);
                $itemCategory = StockItemCategory::find($form->stock_item_category_id);
                
                if ($batch && $itemCategory && $batch->stock_item_category_id != $itemCategory->id) {
                    admin_error('Error', 'Stock batch does not match the selected item category!');
                    return back()->withInput();
                }

                // Check if batch has enough quantity
                if ($batch && $batch->current_quantity < $form->quantity) {
                    admin_warning('Warning', "Stock batch only has {$batch->current_quantity} items available, but you're trying to assign {$form->quantity}!");
                }
            }

            // Auto-set offered_at and offered_by_id when marking as offered
            if ($form->is_service_offered === 'Yes') {
                if (!$form->model()->offered_at) {
                    $form->offered_at = now();
                }
                if (!$form->model()->offered_by_id) {
                    $form->offered_by_id = $u->id;
                }
            }
        });

        // After save, check subscription completion status
        $form->saved(function (Form $form) {
            if ($form->model()->serviceSubscription) {
                $form->model()->serviceSubscription->checkAndUpdateCompletionStatus();
            }
        });

        // Hide unnecessary fields
        $form->hidden('enterprise_id');
        $form->hidden('user_id');

        return $form;


        return $form;
    }
}
