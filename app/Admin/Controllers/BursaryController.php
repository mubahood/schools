<?php

namespace App\Admin\Controllers;

use App\Models\Bursary;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Auth;

class BursaryController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Bursary schemes';

    /**
     * Make a grid builder. 
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Bursary());
        $grid->disableBatchActions();
        $grid->model()
            ->where([
                'enterprise_id' => Auth::user()->enterprise_id
            ])->orderBy('id', 'desc');


        $grid->column('id', __('ID'))->hide();
        $grid->column('name', __('Name'));
        $grid->column('fund', __('Fund'))
            ->display(function ($f) {
                return "UGX " . number_format($f);
            })
            ->sortable();
        $grid->column('beneficiaries', __('Beneficiaries'))
            ->display(function ($f) {
                return count($this->beneficiaries);
            }); 
        $grid->column('is_termly', __('Offer Typs'))->using([
            1 => 'Termly',
            2 => 'One time offer',
        ])->sortable(); 

        $grid->column('description', __('Description'))->hide();

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
        $show = new Show(Bursary::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('is_termly', __('Is termly'));
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
        $form = new Form(new Bursary());

        $form->hidden('enterprise_id', __('Enterprise id'))->default(Auth::user()->ent->id);

        $form->text('name', __('Bursary Name'))->rules('required');
        $form->decimal('fund', __('Bursary fund (Per beneficiary)'))->rules('required');
        $form->radio('is_termly', __('Offer type'))
            ->options([
                1 => 'Termly offer',
                2 => 'One time offer',
            ])
            ->rules('required');

        $form->textarea('description', __('Description'))->rules('required');

        return $form;
    }
}
