<?php

namespace App\Admin\Controllers;

use App\Models\DisciplinaryRecord;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Str;

class DisciplinaryRecordController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Disciplinary Records';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new DisciplinaryRecord());
        $u = Admin::user();
        $grid->disableBatchActions();
        $grid->model()->where('enterprise_id', $u->enterprise_id)->orderBy('id', 'desc');
        $grid->column('created_at', __('Created'))->date('d-M-Y');
        $grid->column('administrator_id', __('Student'))
            ->display(function ($admin_id) {
                if ($this->student == null) {
                    return "N/A";
                }
                return $this->student->name;
            });
        $grid->column('reported_by_id', __('Reported'))
            ->display(function ($admin_id) {
                if ($this->reported_by == null) {
                    return "N/A";
                }
                return $this->reported_by->name;
            });
        $grid->column('academic_year_id', __('Academic Year'))
            ->display(function ($academic_year_id) {
                if ($this->academic_year == null) {
                    return "N/A";
                }
                return $this->academic_year->name;
            });
        $grid->column('term_id', __('Term'))
            ->display(function ($term_id) {
                if ($this->term == null) {
                    return "N/A";
                }
                return $this->term->name;
            });
        $grid->column('type', __('Type'))
            ->label([
                'Good' => 'success',
                'Bad' => 'danger',
            ])->sortable();
        $grid->column('category', __('Type'))
            ->sortable();
        $grid->column('title', __('Title'))
            ->display(function ($title) {
                return Str::limit($title, 30, '...');
            });
        $grid->column('status', __('Status'))
            ->dot([
                'Active' => 'success',
                'Inactive' => 'danger',
            ])->sortable();
        $grid->column('description', __('Description'))
            ->display(function ($description) {
                return Str::limit($description, 30, '...');
            });
        $grid->column('action_taken', __('Action Taken'))
            ->display(function ($action_taken) {
                return Str::limit($action_taken, 30, '...');
            });
        $grid->column('hm_comment', __('Hm comment'))
            ->display(function ($hm_comment) {
                return Str::limit($hm_comment, 30, '...');
            });
        $grid->column('parent_comment', __('Parent Comment'))
            ->display(function ($parent_comment) {
                return Str::limit($parent_comment, 30, '...');
            });
        $grid->column('teacher_comment', __('CLasst Teacher\'s Comment'))
            ->display(function ($teacher_comment) {
                return Str::limit($teacher_comment, 30, '...');
            });
        $grid->column('student_comment', __('Student comment'))
            ->display(function ($teacher_comment) {
                return Str::limit($teacher_comment, 30, '...');
            });
        $grid->column('photo', __('Photo'))->hide();
        $grid->column('file', __('File'))->hide();

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
        $show = new Show(DisciplinaryRecord::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('administrator_id', __('Administrator id'));
        $show->field('reported_by_id', __('Reported by id'));
        $show->field('academic_year_id', __('Academic year id'));
        $show->field('term_id', __('Term id'));
        $show->field('type', __('Type'));
        $show->field('title', __('Title'));
        $show->field('status', __('Status'));
        $show->field('description', __('Description'));
        $show->field('action_taken', __('Action taken'));
        $show->field('hm_comment', __('Hm comment'));
        $show->field('parent_comment', __('Parent comment'));
        $show->field('teacher_comment', __('Teacher comment'));
        $show->field('student_comment', __('Student comment'));
        $show->field('photo', __('Photo'));
        $show->field('file', __('File'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new DisciplinaryRecord());
        $u = Admin::user();
        $ajax_url = url(
            '/api/ajax-users?'
                . 'enterprise_id=' . $u->enterprise_id
                . "&search_by_1=name"
                . "&search_by_2=id"
                . "&user_type=student"
                . "&model=User"
        );
        $ajax_url = trim($ajax_url);
        /* $filter->equal('administrator_id', 'Filter by student')
            ->select(function ($id) {
                $a = Administrator::find($id);
                if ($a) {
                    return [$a->id => $a->name];
                }
            })->ajax($ajax_url); */

        if ($form->isCreating()) {
            $form->select('administrator_id', __('Student'))
                ->options(function ($id) {
                    $a = Administrator::find($id);
                    if ($a) {
                        return [$a->id => $a->name];
                    }
                })->ajax($ajax_url)
                ->rules('required');
            $form->hidden('reported_by_id')->default($u->id);
        } else {
            $form->display('administrator_id', __('Student'))
                ->with(function ($id) {
                    $a = Administrator::find($id);
                    if ($a) {
                        return $a->name;
                    }
                }); 
        }

        $form->radio('type', __('Disciplinary Type'))->options([
            'Good' => 'Good',
            'Bad' => 'Bad',
        ])->rules('required');
        $form->text('title', __('Title'))
            ->rules('required');
        $form->textarea('description', __('Description'))
            ->rules('required');
        $form->divider();
        $form->radio('status', __('Status'))
            ->default('Active')
            ->options([
                'Active' => 'Active',
                'Inactive' => 'Inactive',
            ])->rules('required');

        $form->textarea('action_taken', __('Action taken'));
        $form->textarea('hm_comment', __('Head Teache\'s comment'));
        $form->textarea('parent_comment', __('Parent\'s comment'));
        $form->textarea('teacher_comment', __('Class Teacher\'s comment'));
        $form->textarea('student_comment', __('Student\'s comment'));
        $form->image('photo', __('Photo'));
        $form->file('file', __('File'));

        return $form;
    }
}
