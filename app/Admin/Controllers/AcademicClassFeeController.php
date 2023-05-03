<?php

namespace App\Admin\Controllers;

use App\Models\AcademicClass;
use App\Models\AcademicClassFee;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class AcademicClassFeeController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'School fees - billing';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {

        /* 
        $fee = new AcademicClassFee;
        $fee->enterprise_id = Admin::user()->enterprise_id;
        $fee->academic_class_id = 1;
        $fee->name = 'Test fee';
        $fee->amount = 20000;
        $fee->save();  
        */



        $grid = new Grid(new AcademicClassFee());
        $grid->model()->where('enterprise_id', Admin::user()->enterprise_id)
            ->orderBy('id', 'Desc');

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $u = Admin::user();

            $filter->equal('academic_class_id', 'Fliter by class')->select(AcademicClass::where([
                'enterprise_id' => $u->enterprise_id
            ])->get()
                ->pluck('name_text', 'id'));
            $filter->group('amount', "Fliter by amount", function ($group) {
                $group->gt('Greater than');
                $group->lt('Less than');
                $group->equal('Equal to');
            });
        });


        $grid->disableBatchActions();
        $grid->column('id', __('#Fee ID'))->sortable();
        $grid->column('academic_class_id', __('Class'))->display(function () {
            return $this->academic_class->name_text;
        })->sortable();

        $grid->column('name', __('Fee Name'));
        $grid->column('amount', __('Amount'))->display(function () {
            return '<span style="float: right;">' . $this->amount_text . '</span>';
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
        $show = new Show(AcademicClassFee::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('academic_class_id', __('Academic class id'));
        $show->field('name', __('Name'));
        $show->field('amount', __('Amount'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new AcademicClassFee());
        $u = Admin::user();
        $form->hidden('enterprise_id', __('Enterprise id'))->default($u->enterprise_id)->rules('required');

        $form->select('academic_class_id', 'Application to Class')
            ->options(
                AcademicClass::where([
                    'enterprise_id' => $u->enterprise_id
                ])->get()
                    ->pluck('name_text', 'id')
            )->rules('required');

        $form->text('name', __('Fee name'))->rules('required');
        $form->text('amount', __('Amount'))->rules('required|int');



        return $form;
    }
}
