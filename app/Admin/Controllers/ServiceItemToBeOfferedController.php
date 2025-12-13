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
        $grid->model()->with([
            'serviceSubscription.service',
            'serviceSubscription.subscriber',
            'stockItemCategory',
            'stockBatch',
            'user',
            'offeredBy'
        ]);

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
            ->rules('required');

        // Stock Item Category
        $form->select('stock_item_category_id', 'Stock Item')->options(function ($id) use ($u) {
            if ($id) {
                $item = StockItemCategory::find($id);
                return $item ? [$id => $item->name] : [];
            }
            return StockItemCategory::where('enterprise_id', $u->enterprise_id)
                ->pluck('name', 'id');
        })->rules('required');

        // Offered Status - this controls visibility of Stock Batch and Quantity
        $form->radio('is_service_offered', 'Offered Status')
            ->options([
                'No' => 'Not Offered',
                'Yes' => 'Offered'
            ])
            ->default('No')
            ->rules('required')
            ->when('Yes', function (Form $form) use ($u) {
                // Stock Batch - dropdown with batches filtered by item category
                $form->select('stock_batch_id', 'Stock Batch')->options(function ($id) use ($u, $form) {
                    $itemCategoryId = $form->model()->stock_item_category_id ?? request('stock_item_category_id');

                    if (!$itemCategoryId) {
                        return [];
                    }

                    // Get stock batches for this item category
                    $batches = \App\Models\StockBatch::where('enterprise_id', $u->enterprise_id)
                        ->where('stock_item_category_id', $itemCategoryId)
                        ->where('current_quantity', '>', 0)
                        ->where('is_archived', '!=', 'Yes')
                        ->get()
                        ->mapWithKeys(function ($batch) {
                            return [$batch->id => "Batch #{$batch->id} - Qty: {$batch->current_quantity} - {$batch->description}"];
                        });

                    return $batches->toArray();
                })->rules('required');

                // Quantity - decimal type
                $form->decimal('quantity', 'Quantity')
                    ->default(1)
                    ->rules('required');
            });

        // Remarks
        $form->textarea('remarks', 'Remarks')->rows(3);

        // Auto-set fields on save
        $form->saving(function (Form $form) use ($u) {
            // Set enterprise and user IDs for new records
            if (!$form->model()->id) {
                $form->enterprise_id = $u->enterprise_id;
                $form->user_id = $u->id;
            }

            // Validate stock batch when status is "Offered"
            if ($form->is_service_offered === 'Yes') {
                if (!$form->stock_batch_id) {
                    admin_error('Error', 'Stock batch is required when marking item as offered!');
                    return back()->withInput();
                }

                $batch = \App\Models\StockBatch::find($form->stock_batch_id);

                if (!$batch) {
                    admin_error('Error', 'Invalid stock batch selected!');
                    return back()->withInput();
                }

                // Validate batch matches item category
                if ($batch->stock_item_category_id != $form->stock_item_category_id) {
                    admin_error('Error', 'Stock batch does not match the selected item category!');
                    return back()->withInput();
                }

                // Check if batch has enough quantity
                if ($batch->current_quantity < $form->quantity) {
                    admin_error('Error', "Insufficient stock! Batch only has {$batch->current_quantity} items available.");
                    return back()->withInput();
                }

                // Auto-set offered_at and offered_by_id
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

        // Disable form tools
        $form->tools(function (Form\Tools $tools) {
            $tools->disableView();
        });

        $form->footer(function ($footer) {
            $footer->disableReset();
            $footer->disableViewCheck();
            $footer->disableEditingCheck();
            $footer->disableCreatingCheck();
        });

        // Hide unnecessary fields
        $form->hidden('enterprise_id');
        $form->hidden('user_id');

        return $form;
    }
}
