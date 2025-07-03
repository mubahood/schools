<?php

namespace App\Admin\Controllers;

use App\Models\FundRequisition;
use App\Models\StockBatch;
use App\Models\StockItemCategory;
use App\Models\Term;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class StockBatchController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Stock batches';

    /** 
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new StockBatch());




        $grid->filter(function ($filter) {
            // Remove the default id filter
            $filter->disableIdFilter();

            $filter->equal('stock_item_category_id', 'Filter by Item')->select(
                StockItemCategory::all()
                    ->pluck('name', 'id')
            );

            $terms = [];
            foreach (
                Term::where(
                    'enterprise_id',
                    Admin::user()->enterprise_id
                )->orderBy('id', 'desc')->get() as $key => $term
            ) {
                $terms[$term->id] = "Term " . $term->name . " - " . $term->academic_year->name;
            }

            $filter->equal('term_id', 'Filter by term')
                ->select($terms);
        });


        $grid->actions(function ($actions) {
            $actions->disableView();
        });

        if (!Admin::user()->isRole('admin')) {
            $grid->disableActions();
        };


        //$grid->disableActions();

        if (!Admin::user()->isRole('admin')) {
            $grid->model()->where([
                'enterprise_id' => Admin::user()->enterprise_id,
                'manager' => Admin::user()->id,
            ])
                ->orderBy('id', 'Desc');
        } else {
            $grid->model()->where([
                'enterprise_id' => Admin::user()->enterprise_id,
            ])
                ->orderBy('id', 'Desc');
        }


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

        //stock-batches-archived
        $segs = request()->segments();
        //check if stock-batches-archived is in array of segments
        if (in_array('stock-batches-archived', $segs)) {
            $grid->model()->where('is_archived', 'Yes');
        } else {
            $grid->model()->where('is_archived', 'No');
        }

        // $grid->disableBatchActions();
        $grid->column('id', __('Batch Number'))->sortable();
        $grid->column('stock_item_category_id', __('Item'))->display(function () {
            return $this->cat->name;
        })->sortable();

        $grid->column('original_quantity', __('Original Quantity'))
            ->display(function ($x) {
                return number_format($x) . " " . Str::plural($this->cat->measuring_unit);
            })->sortable()->totalRow(function ($x) {
                return number_format($x);
            });
        $grid->column('price', __('Unit Price'))
            ->display(function ($x) {
                return number_format($x);
            })->sortable();
        $grid->column('current_quantity', __('Current Quantity'))
            ->display(function ($x) {
                return number_format($x) . " " . Str::plural($this->cat->measuring_unit);
            })->sortable()->totalRow(function ($x) {
                return number_format($x);
            });



        $grid->column('worth', __('Current Worth'))
            ->display(function ($x) {
                return 'UGX ' . number_format($x);
            })->sortable()->totalRow(function ($x) {
                return 'UGX ' . number_format($x);
            });
        $grid->column('term_id', __('Due Term'))
            ->display(function ($x) {
                if ($this->term == null) {
                    return $x;
                }
                return $this->term->name_text;
            })->sortable();

        $grid->column('description', __('Description'))->hide();

        $grid->column('supplier_id', __('Supplier'))->display(function () {
            return $this->supplier->name . " " . $this->supplier->phone_number_1;
        })->sortable();

        $grid->column('manager', __('Stock manager'))->display(function () {
            return $this->stock_manager->name;
        })->sortable()->hide();

        $grid->column('purchase_date', __('Date'));

        $grid->column('photo', __('Photo'))->hide();
        $grid->column('fund_requisition_id', __('Requisition form ID'))->hide();
        $grid->column('term_id', __('Due term'))->display(function ($x) {
            $t = Term::find($x);
            if ($t == null) {
                return "N/A";
            }
            return 'Term ' . $t->name;
        })->sortable();
        $grid->column('record', __('Records'))->display(function () {
            $add_recordUrl = admin_url('stock-records/create?stock_batch_id=' . $this->id);
            $view_recordUrl = admin_url('stock-records?stock_batch_id=' . $this->id);
            return "<a target='_blank' href='$add_recordUrl' class='btn btn-xs btn-success'>Add record</a> <a target='_blank' href='$view_recordUrl' class='btn btn-xs btn-primary'>View records</a>";
        });

        $grid->column('is_archived', __('Is archived'))
            ->display(function ($x) {
                if ($x == 'Yes') {
                    return "<span class='label label-danger'>Yes</span>";
                } else {
                    return "<span class='label label-success'>No</span>";
                }
            })->sortable()->filter([
                'Yes' => 'Yes',
                'No' => 'No',
            ]);
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
        $show = new Show(StockBatch::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('purchase_date', __('Created'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('stock_item_category_id', __('Stock item category id'));
        $show->field('original_quantity', __('Original quantity'));
        $show->field('current_quantity', __('Current quantity'));
        $show->field('photo', __('Photo'));
        $show->field('description', __('Description'));
        $show->field('deleted_at', __('Deleted at'));
        $show->field('fund_requisition_id', __('Fund requisition id'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new StockBatch());

        $u = Auth::user();

        $date_of_last_rec = null;
        $last_rec = StockBatch::where([
            'enterprise_id' => $u->enterprise_id
        ])->orderBy('id', 'desc')->first();
        if ($last_rec != null) {
            $date_of_last_rec = $last_rec->purchase_date;
        }

        $form->date('purchase_date', __('Date'))->rules('required')
            ->default($date_of_last_rec);
        $term = $u->ent->active_term();
        $form->select('term_id', "Due term")
            ->options(Term::where([
                'enterprise_id' => $u->enterprise_id
            ])
                ->orderBy('id', 'desc')
                ->get()
                ->pluck('name_text', 'id'))
            ->default($term->id)
            ->rules('required');



        $form->divider();
        $form->hidden('enterprise_id')->rules('required')->default(Admin::user()->enterprise_id)
            ->value(Admin::user()->enterprise_id);

        $cats = [];
        foreach (
            StockItemCategory::where([
                'enterprise_id' => Admin::user()->enterprise_id,
            ])->get() as $val
        ) {
            $p = Str::plural($val->measuring_unit);
            $cats[$val->id] = $val->name . " - (in $p)";
        }

        $form->select('stock_item_category_id', 'Item')
            ->options($cats)->rules('required');




        $ads = [];
        foreach (
            Administrator::where([
                'enterprise_id' => Admin::user()->enterprise_id,
                'user_type' => 'supplier'
            ])->get() as $ad
        ) {
            if ($ad->isRole('supplier')) {
                $ads[$ad->id] = $ad->name . " - ID #{$ad->id}";
            };
        }


        $employees = [];
        foreach (
            Administrator::where([
                'enterprise_id' => Admin::user()->enterprise_id,
                'user_type' => 'employee'
            ])->get() as $ad
        ) {
            $employees[$ad->id] = $ad->name . " - ID #{$ad->id}";
        }


        $form->select('supplier_id', __('Supplier'))
            ->options(
                $ads
            )
            ->rules('required');



        /*         $forms = [];
        foreach (FundRequisition::where([
            'enterprise_id' => Admin::user()->enterprise_id,
        ])
            ->orderBy('id', 'Desc')
            ->get() as $val) {
            $forms[$val->id] = $val->cat->name . " UGX " . number_format($val->total_amount)
                . " - " . $val->created_at;
        } */



        $form->decimal('original_quantity', __('Quantity (in Units)'))
            ->attribute('type', 'number')
            ->rules('required');

        $form->decimal('price', __('Unit Price (in UGX)'))
            ->attribute('type', 'number')
            ->rules('required');

        if (Admin::user()->isRole('admin')) {
            $form->select('manager', __('Stock Manager'))
                ->options(
                    $employees
                )
                ->default($u->id)
                ->rules('required');
        } else {
            $form->select('manager', __('Stock Manager'))
                ->options(
                    $employees
                )
                ->default(Admin::user()->id)
                ->readOnly()
                ->rules('required');
        }



        $form->text('description', __('Stock Description'));

        $form->image('photo', __('Stock Photo'));

        /* $form->select('fund_requisition_id', 'Funds requisition form')
            ->options($forms); */

        if ($form->isCreating()) {
            $form->hidden('is_archived')->default('No');
        } else {
            $form->radio('is_archived', __('Is archived'))
                ->options([
                    'Yes' => 'Yes',
                    'No' => 'No',
                ])->default('No');
        }




        $form->disableReset();
        $form->disableViewCheck();
        return $form;
    }
}
