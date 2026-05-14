<?php

namespace App\Admin\Controllers;

use App\Models\BatchServiceSubscription;
use App\Models\Service;
use App\Models\StockItemCategory;
use App\Models\Term;
use App\Models\TransportRoute;
use App\Models\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class BatchServiceSubscriptionController extends AdminController
{
    protected $title = 'Batch Service Subscriptions';

    protected function grid()
    {
        $grid = new Grid(new BatchServiceSubscription());

        $u = Admin::user();
        $grid->model()
            ->where('enterprise_id', $u->enterprise_id)
            ->orderBy('id', 'desc');

        $grid->disableBatchActions();

        $grid->column('id', 'ID')->sortable()->hide();
        $grid->column('created_at', 'Created')->display(function () {
            return $this->created_at ? $this->created_at->format('d M Y') : '-';
        })->sortable()->hide();

        $grid->column('service_id', 'Service')->display(function ($v) {
            $s = Service::find($v);
            return $s ? $s->name_text : "<span class='badge badge-danger'>Service not found</span>";
        })->sortable();

        $grid->column('quantity', 'Qty/Sub')->sortable();

        $grid->column('due_term_id', 'Term')->display(function ($v) {
            $t = Term::find($v);
            return $t ? $t->name_text : "<span class='badge badge-danger'>Term not found</span>";
        })->sortable();

        $grid->column('administrators', 'Subscribers')->display(function ($administrators) {
            $count = is_array($administrators) ? count($administrators) : 0;
            if ($count === 0) return '<span class="text-muted">None</span>';
            $names = [];
            foreach (array_slice($administrators, 0, 3) as $id) {
                $u = User::find($id);
                if ($u) $names[] = $u->name_text;
            }
            $preview = implode(', ', $names);
            return $count > 3 ? "{$preview} <em>+&nbsp;" . ($count - 3) . "&nbsp;more</em> ({$count})" : "{$preview} ({$count})";
        });

        $grid->column('to_be_managed_by_inventory', 'Inventory')->display(function ($v) {
            return $v === 'Yes'
                ? "<span class='label label-info'>Yes</span>"
                : "<span class='label label-default'>No</span>";
        });

        $grid->column('success_count', 'Success')->sortable();
        $grid->column('fail_count', 'Failed')->sortable();
        $grid->column('total_count', 'Total')->sortable();

        $grid->column('processed_notes', 'Notes')->limit(40)->hide();

        $grid->column('processed_button', 'Status')->display(function () {
            if ($this->is_processed === 'Yes') {
                return "<span class='badge badge-success'>Processed</span>";
            }
            $url = url('process-batch-service-subscriptions?id=' . $this->id);
            return "<a target='_blank' href='{$url}' class='btn btn-sm btn-primary'>
                        <i class='fa fa-play'></i> Process
                    </a>";
        });

        $grid->actions(function ($actions) {
            $actions->disableView();
            // Keep edit available even after processing so quantity/details can be corrected
        });

        return $grid;
    }

    protected function detail($id)
    {
        $show = new Show(BatchServiceSubscription::findOrFail($id));

        $show->field('id', 'ID');
        $show->field('service_id', 'Service')->as(function ($v) {
            $s = Service::find($v);
            return $s ? $s->name_text : $v;
        });
        $show->field('quantity', 'Quantity per Subscriber');
        $show->field('due_term_id', 'Term')->as(function ($v) {
            $t = Term::find($v);
            return $t ? $t->name_text : $v;
        });
        $show->field('to_be_managed_by_inventory', 'Managed by Inventory');
        $show->field('is_processed', 'Processed');
        $show->field('success_count', 'Successes');
        $show->field('fail_count', 'Failures');
        $show->field('total_count', 'Total Attempted');
        $show->field('processed_notes', 'Notes');
        $show->field('created_at', 'Created');

        return $show;
    }

    protected function form()
    {
        $form = new Form(new BatchServiceSubscription());
        $u    = Admin::user();

        $form->hidden('enterprise_id')->default($u->enterprise_id);

        // ── Term / Service / Quantity ────────────────────────────────────────

        $terms       = [];
        $active_term = 0;
        foreach (Term::where('enterprise_id', $u->enterprise_id)->orderBy('id', 'desc')->get() as $term) {
            $terms[$term->id] = $term->name_text . ' — ' . optional($term->academic_year)->name;
            if ($term->is_active) {
                $active_term = $term->id;
            }
        }

        $services = Service::where('enterprise_id', $u->enterprise_id)
            ->orderBy('name')
            ->get()
            ->pluck('name_text', 'id')
            ->toArray();

        if ($form->isCreating()) {
            $form->select('due_term_id', 'Due Term')
                ->options($terms)
                ->default($active_term)
                ->rules('required');

            $form->select('service_id', 'Service')
                ->options($services)
                ->rules('required');

            $form->number('quantity', 'Quantity per Subscriber')
                ->default(1)
                ->rules('required|integer|min:1')
                ->help('Number of units each subscriber receives (e.g. 1 uniform, 2 books).');
        } else {
            // Edit — term and service are fixed, quantity is editable
            $form->display('due_term_id', 'Due Term')->with(function ($v) {
                $t = Term::find($v);
                return $t ? $t->name_text : $v;
            });

            $form->display('service_id', 'Service')->with(function ($v) {
                $s = Service::find($v);
                return $s ? $s->name_text : $v;
            });

            // ── EDITABLE QUANTITY ────────────────────────────────────────────
            $form->number('quantity', 'Quantity per Subscriber')
                ->rules('required|integer|min:1')
                ->help('You can update this before or after processing. Re-process to apply to new subscribers.');

            // Show current processing status
            $batch = $form->model();
            if ($batch && $batch->is_processed === 'Yes') {
                $form->html("<div class='alert alert-success'>
                    <i class='fa fa-check-circle'></i>
                    <strong>Already processed.</strong>
                    Success: {$batch->success_count} | Failed: {$batch->fail_count} | Total: {$batch->total_count}
                </div>");
            }
        }

        // ── Inventory Management (create AND edit) ───────────────────────────

        $form->divider('Inventory Management');

        $stockItems = StockItemCategory::where('enterprise_id', $u->enterprise_id)
            ->orderBy('name')
            ->get()
            ->pluck('name', 'id')
            ->toArray();

        $form->radio('to_be_managed_by_inventory', 'Manage by Inventory?')
            ->options([
                'No'  => 'No — Regular service subscription',
                'Yes' => 'Yes — Track inventory items per subscriber',
            ])
            ->default('No')
            ->when('Yes', function (Form $form) use ($stockItems) {
                $form->hasMany('batchItems', 'Items to Offer (per subscriber)', function (Form\NestedForm $form) use ($stockItems) {
                    $form->select('stock_item_category_id', 'Stock Item')
                        ->options($stockItems)
                        ->rules('required');
                    $form->number('quantity', 'Quantity')
                        ->default(1)
                        ->rules('required|integer|min:1');
                });
            })
            ->help('When "Yes", these item quantities will be created as tracking records for each subscriber during processing.');

        // ── Transport Linking ────────────────────────────────────────────────

        $form->radioCard('link_with', 'Link subscription with?')
            ->options([
                'Transport' => 'Transport',
                'Hostel'    => 'Hostel',
                'None'      => 'None',
            ])
            ->default('None')
            ->when('Transport', function (Form $form) {
                $u      = Admin::user();
                $routes = TransportRoute::where('enterprise_id', $u->enterprise_id)
                    ->orderBy('name')
                    ->pluck('name', 'id')
                    ->toArray();
                $form->select('transport_route_id', 'Transport Route')
                    ->options($routes)
                    ->rules('required');
                $form->radio('trip_type', 'Trip Type')
                    ->options([
                        'To School'   => 'To School',
                        'From School' => 'From School',
                        'Round Trip'  => 'Round Trip (To & Fro)',
                    ])
                    ->rules('required');
            });

        // ── Subscribers ──────────────────────────────────────────────────────

        $form->divider('Subscribers');

        $ajax_url = url('/api/ajax-users?user_type=student&enterprise_id=' . $u->enterprise_id);

        $form->multipleSelect('administrators', 'Select Subscribers')
            ->options(function ($ids) {
                if (!is_array($ids) || empty($ids)) {
                    return [];
                }
                return User::whereIn('id', $ids)
                    ->get()
                    ->pluck('name_text', 'id')
                    ->toArray();
            })
            ->ajax($ajax_url)
            ->rules('required')
            ->help('Search and select students to include in this batch.');

        // ── Processing control (edit only) ───────────────────────────────────

        if ($form->isEditing()) {
            $form->divider('Processing Control');
            $form->radio('is_processed', 'Mark as Processed?')
                ->options(['No' => 'No — Allow re-processing', 'Yes' => 'Yes — Already processed'])
                ->default('No')
                ->help('Set to "No" to allow re-running the batch (already-subscribed students will be skipped).');
        }

        $form->hidden('total')->default(0);
        $form->disableReset();
        $form->disableViewCheck();

        return $form;
    }
}
