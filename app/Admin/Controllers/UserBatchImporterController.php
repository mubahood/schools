<?php

namespace App\Admin\Controllers;

use App\Models\AcademicClass;
use App\Models\UserBatchImporter;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class UserBatchImporterController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Students batch import';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
       /*  $x = new UserBatchImporter();
        $x->enterprise_id = 6;
        $x->academic_class_id = 1;
        $x->type = 'students';
        $x->file_path = 'files/students.xlsx';
        $x->imported = 0;

        $x->save(); */
        $grid = new Grid(new UserBatchImporter());
        $grid->disableActions();
        $grid->disableBatchActions();
        $grid->disableFilter();
        $grid->disableExport();
        $grid->model()->where(
            [
                'enterprise_id' => Admin::user()->enterprise_id,
                'type' => 'students'
            ]
        )
            ->orderBy('id', 'Desc');

        $grid->column('id', __('Id'))->sortable();

        /*         $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at')); 
        $grid->column('enterprise_id', __('Enterprise id'));*/
        $grid->column('academic_class_id', __('Description'))
            ->display(function ($academic_class_id) {
                $class = AcademicClass::find($academic_class_id);
                $count  = count($this->users);
                $class_name = "-";
                if ($class != null) {
                    $class_name = $class->name;
                }
                return "Imported $count students to $class_name ";
            });
        /*  $grid->column('type', __('Type')); */

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
        $show = new Show(UserBatchImporter::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('academic_class_id', __('Academic class id'));
        $show->field('type', __('Type'));
        $show->field('file_path', __('File path'));
        $show->field('imported', __('Imported'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new UserBatchImporter());

        $form->disableCreatingCheck();
        $form->disableEditingCheck();
        $form->disableReset();
        $form->disableViewCheck();


        $u = Admin::user();
        $form->hidden('enterprise_id', __('Enterprise id'))->default($u->enterprise_id)->rules('required');
        $form->hidden('type', __('type'))->default('students')->rules('required');
        $form->hidden('imported', __('imported'))->default(0)->rules('required');

        $form->select('academic_class_id', 'Import stydent to class')
            ->options(
                AcademicClass::where([
                    'enterprise_id' => $u->enterprise_id
                ])->get()
                    ->pluck('name_text', 'id')
            )->rules('required');

        $form->file('file_path', __('File path'))->rules('required');

        return $form;
    }
}
