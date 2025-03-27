<?php

namespace App\Admin\Controllers;

use App\Models\AcademicClass;
use App\Models\BulkPhotoUploadItem;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class BulkPhotoUploadItemController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'BulkPhotoUploadItem';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new BulkPhotoUploadItem());
        $u = Admin::user();
        $grid->model()->where('enterprise_id', $u->enterprise_id);
        $grid->model()->orderBy('id', 'desc');
        $grid->disableBatchActions();
        $grid->disableExport();

        $grid->filter(function ($filter) {
            $filter->disableIdFilter(); 
            $filter->equal('status', 'Status')->select([
                'Pending' => 'Pending',
                'Success' => 'Success',
                'Failed' => 'Failed',
            ]);

            $filter->between('created_at', 'Created Date')->date();
        });

        $grid->column('created_at', __('Created'))
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
        $grid->column('bulk_photo_upload_id', __('Bulk Photo Upload Ref.'))
            ->display(function () {
                return "#" . $this->bulk_photo_upload_id;
            })->sortable();
        $grid->column('student_id', __('Student'))
            ->display(function () {
                $student = $this->get_student();
                if ($student == null) {
                    return "N/A";
                }
                return $student->name;
            })->sortable();
        $grid->column('new_image_path', __('New Photo'))
            ->display(function ($new_image_path) {
                $app_url = env('APP_URL');
                $url = $app_url . "/storage/" . $new_image_path;
                return "<a href='$url' target='_blank'><img src='$url' style='max-width: 80px; max-height: 80px;'></a>";
            });
        $grid->column('status', __('Status'))->sortable()
            ->label([
                'Pending' => 'info',
                'Success' => 'success',
                'Failed' => 'danger',
            ]);
        $grid->column('error_message', __('Error message'))->hide();
        $grid->column('naming_type', __('Naming type'))
            ->label([
                'student_no' => 'info',
                'name' => 'success',
                'school_pay' => 'danger',
            ])->sortable();
        $grid->column('file_name', __('File name'))
            ->filter('like')->sortable();


        //action buttons
        $grid->column('action_buttons', __('Action buttons'))->display(function () {
            if ($this->status != 'Success') {
                $url = url("bulk-photo-upload-item-process?id={$this->id}");
                return "<a href='{$url}' class='btn btn-primary' 
            target='_blank'
            >Process Photos Now</a>";
            }
            $url = url("bulk-photo-upload-item-process?id={$this->id}");
            return "<a href='{$url}' class='btn btn-primary'
            target='_blank'
            >Process Photos Again</a>";
        });

        $grid->disableCreation();
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
        $show = new Show(BulkPhotoUploadItem::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('academic_class_id', __('Academic class id'));
        $show->field('bulk_photo_upload_id', __('Bulk photo upload id'));
        $show->field('student_id', __('Student id'));
        $show->field('new_image_path', __('New image path'));
        $show->field('old_image_path', __('Old image path'));
        $show->field('status', __('Status'));
        $show->field('error_message', __('Error message'));
        $show->field('naming_type', __('Naming type'));
        $show->field('file_name', __('File name'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new BulkPhotoUploadItem());

        if (!$form->isCreating()) {
            $form->display('student_id', __('Student'))->with(function ($student_id) {
                $student = $this->get_student();
                if ($student == null) {
                    return "N/A";
                }
                return $student->name;
            });
        }
        $form->image('new_image_path', __('New image path'))
            ->uniqueName()
            ->rules('required')
            ->required();
        $form->radio('status', __('Status'))->default('Pending')
            ->options([
                'Pending' => 'Pending',
                'Success' => 'Success',
                'Failed' => 'Failed',
            ]);

        return $form;
    }
}
