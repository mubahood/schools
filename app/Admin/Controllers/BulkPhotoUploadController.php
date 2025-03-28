<?php

namespace App\Admin\Controllers;

use App\Models\AcademicClass;
use App\Models\BulkPhotoUpload;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class BulkPhotoUploadController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'BulkPhotoUpload';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new BulkPhotoUpload());

        $u = Admin::user();
        $grid->model()->where('enterprise_id', $u->enterprise_id);
        $grid->disableBatchActions();
        $grid->disableExport();
        $grid->model()->orderBy('id', 'desc');
        $grid->column('id', __('Id'))->hide();
        $grid->column('created_at', __('DATE'))
            ->display(function ($created_at) {
                return date('d-m-Y', strtotime($created_at));
            })->sortable();

        $grid->column('academic_class_id', __('Class'))
            ->display(function () {
                $class = AcademicClass::find($this->academic_class_id);
                if ($class == null) {
                    return "N/A";
                }
                return $class->name_text;
            })->sortable();
        $grid->column('file_path', __('File path'))->hide();
        $grid->column('file_name', __('File name'))->hide();
        $grid->column('naming_type', __('Naming Type'))
            ->label([
                'student_no' => 'info',
                'name' => 'success',
                'school_pay' => 'danger',
            ])->sortable();
        $grid->column('status', __('Status'));
        $grid->column('error_message', __('Error message'))->hide();
        $grid->column('total_images', __('Total images'))->hide();
        $grid->column('success_images', __('Success images'))->hide();
        $grid->column('failed_images', __('Failed images'))->hide();
        $grid->column('action_buttons', __('Action buttons'))->display(function () {
            if ($this->status != 'Completed') {
                $url = url("bulk-photo-uploads-process?id={$this->id}");
                return "<a href='{$url}' class='btn  btn-primary' 
                target='_blank'
                >Process Photos Now</a>";
            }
            //make the word process again
            $url = url("bulk-photo-uploads-process?id={$this->id}");
            return "<a href='{$url}' class='btn  btn-primary'
            target='_blank'
            >Process Photos Again</a>";
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
        $show = new Show(BulkPhotoUpload::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('academic_class_id', __('Academic class id'));
        $show->field('file_path', __('File path'));
        $show->field('file_name', __('File name'));
        $show->field('naming_type', __('Naming type'));
        $show->field('status', __('Status'));
        $show->field('error_message', __('Error message'));
        $show->field('total_images', __('Total images'));
        $show->field('success_images', __('Success images'));
        $show->field('failed_images', __('Failed images'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new BulkPhotoUpload());
        $u = Admin::user();
        $form->hidden('enterprise_id')->default($u->enterprise_id);

        $active_academic_year = $u->ent->active_academic_year();
        if ($active_academic_year == null) {
            die("No active academic year");
        }
        $classes = [];
        foreach (
            AcademicClass::where([
                'enterprise_id' => $u->enterprise_id,
                'academic_year_id' => $active_academic_year->id,
            ])->get() as $class
        ) {
            if (((int)($class->academic_year->is_active)) != 1) {
                continue;
            }
            $classes[$class->id] = $class->name_text;
        }


        $form->radio('academic_class_id', 'Select Class')->options($classes)->rules('required')->required();

        if ($form->isCreating()) {
            /* 
           $table->string('file_type')->nullable();
            $table->longText('images')->nullable();
*/
            $form->radio('file_type', 'Select File Type')->options([
                'zip' => 'Zip',
                'images' => 'Images',
            ])->rules('required')->required();
            $form->file('file_path', __('Select zip file'))
                ->uniqueName()
                ->help('Select a zip file containing images. The images should be named as per the naming type selected below. The zip file should not contain any subdirectories and should contain only image files. The zip file should not be password protected"');

            $form->multipleImage('images', __('Select images'))
                ->removable()
                ->help('Select images. The images should be named as per the naming type selected below. The images should not be password protected"');
        }
        // $form->textarea('file_name', __('File name')); file_name
        $form->radio('naming_type', __('Image naming type'))->options([
            'student_no' => 'Student ID number',
            'name' => 'First name - Last name',
            'school_pay' => 'School Pay Code',
        ])->rules('required')->required();


        if ($form->isCreating()) {
            $form->hidden('status')->default('Pending');
            $form->hidden('total_images')->default(0);
            $form->hidden('success_images')->default(0);
            $form->hidden('failed_images')->default(0);
        } else {
            $form->radio('status', __('Status'))->options([
                'Pending' => 'Pending',
                'Processing' => 'Processing',
                'Completed' => 'Completed',
                'Failed' => 'Failed',
            ])->rules('required')->required();
        }

        $form->disableReset();
        $form->disableViewCheck();
        $form->disableCreatingCheck();
        $form->disableEditingCheck();
        return $form;
    }
}
