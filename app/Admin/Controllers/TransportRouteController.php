<?php

namespace App\Admin\Controllers;

use App\Models\TransportRoute;
use App\Models\TransportStage;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class TransportRouteController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Stages';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new TransportRoute());

        $u = Admin::user();
        $grid->model()->where('enterprise_id', $u->enterprise_id);
        $grid->disableBatchActions();
        $grid->quickSearch();
        $grid->column('name', __('Name'))->sortable();
        $grid->column('stage_id', __('Route'))->display(function ($stage) {
            if ($this->route == null) {
                return 'N/A';
            }
            return $this->route->name;
        })->sortable();
        $grid->column('single_trip_fare', __('Single trip fare (UGX)'))
        ->display(function ($single_trip_fare) {
            return number_format($single_trip_fare, 2);
        })->sortable();
        $grid->column('round_trip_fare', __('Round trip fare (UGX)'))
        ->display(function ($round_trip_fare) {
            return number_format($round_trip_fare, 2);
        })->sortable();

        //SUBCRIBERS
        $grid->column('subscribers_count', __('Number of subscribers'))->display(function ($subscribers_count) {
            return '<span class="label label-info">' . count($this->subscribers) . '</span>';
        });
        $grid->column('description', __('Description'))->hide(); //


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
        $show = new Show(TransportRoute::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('name', __('Name'));
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
        $form = new Form(new TransportRoute());
        $u = Admin::user();

        $_stages = [];
        $stages = TransportStage::where('enterprise_id', $u->enterprise_id)->get();
        foreach ($stages as $key => $stage) {
            $_stages[$stage->id] = $stage->name;
        }
        $form->hidden('enterprise_id', __('Enterprise id'))->value($u->enterprise_id);
        $form->text('name', __('Name'))->rules('required')->required();
        $form->select('stage_id', 'Select route')->options($_stages)->rules('required')->required();
        $form->decimal('single_trip_fare', __('Single trip fare'))->rules('required');
        $form->decimal('round_trip_fare', __('Round trip fare'))->rules('required');
        $form->textarea('description', __('Description'));


        return $form;
    }
}
