<?php

namespace App\Admin\Controllers;

use App\Models\StockBatch;
use App\Models\StockItemCategory;
use App\Models\StockRecord;
use App\Models\Term;
use App\Models\Utils;
use Dflydev\DotAccessData\Util;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Str;

class StockRecordController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Stock records';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        //set max time
        /* set_time_limit(0);
        //set max memory
        ini_set('memory_limit', '1024M');
        foreach (StockRecord::where('quanity', '>', 0)->get() as $key => $value) {
            //$value->
            if ($value->description == null) {
                $value->description = "";
            } else {
                $value->description .= ".";
            }
            $value->save();
        } */
        //Utils::reset_account_names();
        //die("as");
        $grid = new Grid(new StockRecord());
        // $grid->disableBatchActions();




        if (Admin::user()->isRole('admin')) {
            $grid->model()->where('enterprise_id', Admin::user()->enterprise_id)
                ->orderBy('record_date', 'Desc');
        } else {
            $grid->disableActions();
            $grid->model()->where([
                'enterprise_id' => Admin::user()->enterprise_id,
                'created_by' => Admin::user()->id,
            ])
                ->orderBy('record_date', 'Desc');
        }


        $terms = [];
        $active_term = 0;
        foreach (Term::where(
            'enterprise_id',
            Admin::user()->enterprise_id
        )->orderBy('id', 'desc')->get() as $key => $term) {
            $terms[$term->id] = "Term " . $term->name . " - " . $term->academic_year->name;
            if ($term->is_active) {
                $active_term = $term->id;
            }
        }
        if (!isset($_GET['due_term_id'])) {
            //$grid->model()->where('due_term_id', $active_term);
        }


        $grid->filter(function ($filter) {
            // Remove the default id filter
            $filter->disableIdFilter();

            $u = Admin::user();
            $ajax_url = url(
                '/api/ajax?'
                    . 'enterprise_id=' . $u->enterprise_id
                    . "&search_by_1=name"
                    . "&search_by_2=id"
                    . "&model=User"
            );

            $filter->equal('created_by', 'Supplied by')->select(function ($id) {
                $a = Administrator::find($id);
                if ($a) {
                    return [$a->id => $a->name];
                }
            })->ajax($ajax_url);
            $filter->equal('received_by', 'Received by')->select(function ($id) {
                $a = Administrator::find($id);
                if ($a) {
                    return [$a->id => $a->name];
                }
            })->ajax($ajax_url);

            $cats = [];
            foreach (StockItemCategory::where([
                'enterprise_id' => Admin::user()->enterprise_id,
            ])->get() as $val) {
                $p = Str::plural($val->measuring_unit);
                $cats[$val->id] = $val->name . " - (in $p)";
            }

            $filter->equal('stock_item_category_id', 'Stock category')->select($cats);

            $terms = [];
            foreach (Term::where(
                'enterprise_id',
                Admin::user()->enterprise_id
            )->orderBy('id', 'desc')->get() as $key => $term) {
                $terms[$term->id] = "Term " . $term->name . " - " . $term->academic_year->name;
            }

            $filter->equal('due_term_id', 'Filter by term')
                ->select($terms);

            //stock_batch_id
            $ajax_url = url(
                '/api/ajax?'
                    . 'enterprise_id=' . $u->enterprise_id
                    . "&search_by_1=name"
                    . "&search_by_2=id"
                    . "&model=StockBatch"
            );
            $filter->equal('stock_batch_id', 'Filter by stock batch')
                ->select(function ($id) {
                    $a = StockBatch::find($id);
                    if ($a) {
                        return [$a->id => $a->cat->name . " Stock ID #" . $a->id];
                    }
                })->ajax($ajax_url);


            $filter->between('record_date', 'Date')->date();
        });



        $grid->column('id', __('#ID'))->sortable()->hide();

        $grid->column('record_date', __('Date'))->display(function ($date) {
            return Utils::my_date_time($date);
        })->sortable();



        $grid->column('stock_batch_id', __('Stock batch'))
            ->hide()
            ->display(function () {
                return $this->batch->cat->name . " Stock ID #" . $this->batch->id;
            })->sortable();

        $grid->column('quanity', __('Quanity'))
            ->display(function ($x) {
                return number_format($x) . " " . Str::plural($this->cat->measuring_unit);
            })->sortable()->totalRow(function ($x) {
                return number_format($x);
            });


        $grid->column('stock_item_category_id', __('Stock category'))
            ->display(function () {
                return $this->batch->cat->name;
            })->sortable();
        //type
        $grid->column('type', __('Type'))
            ->label([
                'IN' => 'success',
                'OUT' => 'danger',
            ])->sortable()
            ->filter([
                'IN' => 'IN',
                'OUT' => 'OUT',
            ]);


        if (Admin::user()->isRole('admin')) {
            $grid->column('created_by', __('Supplied by'))
                ->display(function () {
                    return $this->createdBy->name . " - #" . $this->createdBy->id;
                })->sortable();
        }


        $grid->column('received_by', __('Received by'))
            ->display(function () {
                return $this->receivedBy->name . " - #" . $this->receivedBy->id;
            })->sortable();


        $grid->column('description', __('Description'))->hide();
        $grid->column('due_term_id', __('Due term'))->display(function ($x) {
            $t = Term::find($x);
            if ($t == null) {
                return "N/A";
            }
            return 'Term ' . $t->name;
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
        $show = new Show(StockRecord::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('stock_batch_id', __('Stock batch id'));
        $show->field('stock_item_category_id', __('Stock item category id'));
        $show->field('created_by', __('Created by'));
        $show->field('received_by', __('Received by'));
        $show->field('quanity', __('Quanity'));
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
        $form = new Form(new StockRecord());

        $u = Auth('admin')->user();
        $due_term = $u->ent->active_term();
        $form->hidden('due_term_id')->default($due_term->id)->value($due_term->id);
        // due_term_id



        $form->hidden('enterprise_id')->rules('required')->default(Admin::user()->enterprise_id)
            ->value(Admin::user()->enterprise_id);

        $form->hidden('created_by')->rules('required')->default(Admin::user()->id)
            ->value(Admin::user()->id);

        $cats = [];
        foreach (StockBatch::where([
            'enterprise_id' => Admin::user()->enterprise_id,
        ])
            ->where('current_quantity', '>', 0)
            ->get() as $val) {
            $p = Str::plural($val->cat->measuring_unit);
            $cats[$val->id] = $val->cat->name . " " . number_format($val->current_quantity) . " $p - STOCK ID #{$val->id}";
        }

        if ($form->isCreating()) {
            $stock_batch_id = null;
            if (isset($_GET['stock_batch_id'])) {
                $stock_batch_id = $_GET['stock_batch_id'];
            }
            $form->select('stock_batch_id', 'Stock batch')
                ->options($cats)->rules('required')
                ->default($stock_batch_id);
            $form->radio('type', 'Type')->options([
                'IN' => 'IN',
                'OUT' => 'OUT',
            ])->rules('required')->required();
        } else {
            $form->select('stock_batch_id', 'Stock batch')
                ->options($cats)->readonly()->rules('required');
            $form->select('type', 'Type')->options([
                'IN' => 'IN',
                'OUT' => 'OUT',
            ])->readonly()->required();
        }


        $form->datetime('record_date', __('Date'))->rules('required');


        $u = Admin::user();
        $ajax_url = url(
            '/api/ajax?'
                . 'enterprise_id=' . $u->enterprise_id
                . "&search_by_1=name"
                . "&search_by_2=id"
                . "&model=User"
        );

        $form->decimal('quanity', __('Quanity'))->rules('required');

        $form->select('received_by', "Received by")
            ->options(function ($id) {
                $a = Administrator::find($id);
                if ($a) {
                    return [$a->id => "#" . $a->id . " - " . $a->name];
                }
            })
            ->ajax($ajax_url)->rules('required');

        $form->textarea('description', __('Description'));

        return $form;
    }
}
