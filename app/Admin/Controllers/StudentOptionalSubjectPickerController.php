<?php

namespace App\Admin\Controllers;

use App\Models\StudentHasClass;
use App\Models\StudentOptionalSubjectPicker;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Auth;

class StudentOptionalSubjectPickerController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Student Optional Subject Picker';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {

        Admin::script('window.location.replace("' . admin_url('students-classes') . '");');
        return 'redirecting...';
        $grid = new Grid(new StudentOptionalSubjectPicker());

        $grid->column('id', __('Id'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        $grid->column('enterprise_id', __('Enterprise id'));
        $grid->column('student_has_class_id', __('Student has class id'));
        $grid->column('administrator_id', __('Administrator id'));
        $grid->column('student_class_id', __('Student class id'));
        $grid->column('academic_year_id', __('Academic year id'));
        $grid->column('optional_subjects', __('Optional subjects'));
        $grid->column('optional_secondary_subjects', __('Optional secondary subjects'));

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
        $show = new Show(StudentOptionalSubjectPicker::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('student_has_class_id', __('Student has class id'));
        $show->field('administrator_id', __('Administrator id'));
        $show->field('student_class_id', __('Student class id'));
        $show->field('academic_year_id', __('Academic year id'));
        $show->field('optional_subjects', __('Optional subjects'));
        $show->field('optional_secondary_subjects', __('Optional secondary subjects'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {

        $form = new Form(new StudentOptionalSubjectPicker());
        $u = Auth::user();
        $form->hidden('enterprise_id', __('Enterprise id'))->default($u->enterprise_id);


        $student_has_class_id = 0;
        if (isset($_GET['student_has_class_id'])) {
            $student_has_class_id = $_GET['student_has_class_id'];
        }
        if ($student_has_class_id == 0) {
            $params = request()->route()->parameters();
            if (isset($params['student_optional_subject_picker'])) {
                $student_optional_subject_picker_id = $params['student_optional_subject_picker'];
                $m = StudentOptionalSubjectPicker::find($student_optional_subject_picker_id);
                if ($m != null) {
                    $student_has_class_id = $m->student_has_class_id;
                }
            }
        }

        $studentHasClass = StudentHasClass::find($student_has_class_id);
        if ($studentHasClass == null) {
            return $form;
            $segs = request()->segments();
            throw new \Exception("Student class not found.", 1);
        }

        $student = Administrator::find($studentHasClass->administrator_id);
        if ($student == null) {
            return $form;
            throw new \Exception("Student not found.", 1);
        }

        if ($studentHasClass != null && $student != null) {

            $form->hidden('student_has_class_id', __('Student has class id'))->default($student_has_class_id)->readonly();
            $form->display('student_name', __('Student'))->default($student->name);
            $form->display('class', __('Class'))->default($studentHasClass->class->short_name);

            $subs = [];
            foreach ($studentHasClass->class->getOptionalSubjectsItems() as  $s) {
                $subs[((int)($s->id))] = $s->subject_name . " - " . $s->code;
            }
            $form->divider('Optional Subjects Papers (Old Curriculum)');
            $form->checkbox('optional_subjects', __('Select Optional Subjects Papers (Old Curriculum)'))
                ->options($subs)
                ->stacked();

            $form->divider('Optional Subjects (New Curriculum)');
            $_subs = [];
            foreach ($studentHasClass->class->getNewCurriculumOptionalSubjectsItems() as  $s) {
                $_subs[((int)($s->id))] = $s->subject_name . " - " . $s->code;
            }
            $form->checkbox('optional_secondary_subjects', __('Select Optional Subjects (New Curriculum'))
                ->options($_subs)
                ->stacked();
        } else {
            return $form;
            throw new \Exception("Student class not found.", 1);
        }

        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
            $tools->disableView();
        });
        $form->disableCreatingCheck();
        $form->disableViewCheck();
        $form->disableReset();
        return $form;
    }
}
