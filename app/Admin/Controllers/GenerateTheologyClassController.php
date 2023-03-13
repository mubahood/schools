<?php

namespace App\Admin\Controllers;

use App\Models\AcademicYear;
use App\Models\Enterprise;
use App\Models\GenerateTheologyClass;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Tymon\JWTAuth\Contracts\Providers\Auth;

class GenerateTheologyClassController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Generate Theology Classes';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
     /*    $gen = new GenerateTheologyClass();
        $gen->academic_year_id = 3;
        $gen->enterprise_id = Admin::user()->enterprise_id;
        $gen->P1 = 'Yes';
        $gen->P2 = 'Yes';
        $gen->P3 = 'Yes';
        $gen->P4 = rand(1, 10000);
        $gen->save();
        dd('done'); */

        $grid = new Grid(new GenerateTheologyClass());
        $grid->model()->where('enterprise_id', Admin::user()->enterprise_id)
            ->orderBy('id', 'Desc');
        $grid->column('id', __('Id'));
        $grid->disableBatchActions();

        $grid->column('academic_year_id', __('Academic year'))->display(function () {
            return $this->academic_year->name;
        });
        $grid->column('BC', __('BC'));
        $grid->column('MC', __('MC'));
        $grid->column('TC', __('TC'));
        $grid->column('P1', __('P1'));
        $grid->column('P2', __('P2'));
        $grid->column('P3', __('P3'));
        $grid->column('P4', __('P4'));
        $grid->column('P5', __('P5'));
        $grid->column('P6', __('P6'));
        $grid->column('P7', __('Shuubah'));

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
        $show = new Show(GenerateTheologyClass::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('academic_year_id', __('Academic year id'));
        $show->field('BC', __('BC'));
        $show->field('MC', __('MC'));
        $show->field('TC', __('TC'));
        $show->field('P1', __('P1'));
        $show->field('P2', __('P2'));
        $show->field('P3', __('P3'));
        $show->field('P4', __('P4'));
        $show->field('P5', __('P5'));
        $show->field('P6', __('P6'));
        $show->field('P7', __('Shuubah'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new GenerateTheologyClass());

        $u = Admin::user();
        $ent = Enterprise::find($u->enterprise_id);
        $year = $ent->active_academic_year();
        $form->hidden('enterprise_id')->rules('required')->default(Admin::user()->enterprise_id)
            ->value($u->enterprise_id);

        $form->disableCreatingCheck();
        $form->disableEditingCheck();
        $form->disableReset();
        $form->disableViewCheck();
        if ($form->isCreating()) {
            $form->select('academic_year_id', 'Academic year')
                ->options(
                    AcademicYear::where([
                        'enterprise_id' => $u->enterprise_id,
                    ])
                        ->orderBy('id', 'desc')
                        ->get()
                        ->pluck('name', 'id')
                )
                ->default($year->id)
                ->readOnly()
                ->rules('required');
        } else {
            $form->select('academic_year_id', 'Academic year')
                ->readOnly()
                ->options(
                    AcademicYear::where([
                        'enterprise_id' => $u->enterprise_id,
                    ])
                        ->orderBy('id', 'desc')
                        ->get()
                        ->pluck('name', 'id')
                )->rules('required');
        }

        $form->radio('BC', __('BC'))->options(['Yes' => 'Yes', 'No' => 'No'])->rules('required');
        $form->radio('MC', __('MC'))->options(['Yes' => 'Yes', 'No' => 'No'])->rules('required');
        $form->radio('TC', __('TC'))->options(['Yes' => 'Yes', 'No' => 'No'])->rules('required');
        $form->radio('P1', __('P1'))->options(['Yes' => 'Yes', 'No' => 'No'])->rules('required');
        $form->radio('P2', __('P2'))->options(['Yes' => 'Yes', 'No' => 'No'])->rules('required');
        $form->radio('P3', __('P3'))->options(['Yes' => 'Yes', 'No' => 'No'])->rules('required');
        $form->radio('P4', __('P4'))->options(['Yes' => 'Yes', 'No' => 'No'])->rules('required');
        $form->radio('P5', __('P5'))->options(['Yes' => 'Yes', 'No' => 'No'])->rules('required');
        $form->radio('P6', __('P6'))->options(['Yes' => 'Yes', 'No' => 'No'])->rules('required');
        $form->radio('P7', __('Shuubah'))->options(['Yes' => 'Yes', 'No' => 'No'])->rules('required');

        return $form;
    }
}
