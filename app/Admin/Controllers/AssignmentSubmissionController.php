<?php

namespace App\Admin\Controllers;

use App\Models\AcademicClass;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Subject;
use App\Models\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Auth;

class AssignmentSubmissionController extends AdminController
{
    protected $title = 'Assignment Submissions';

    // ── Grid ───────────────────────────────────────────────────────

    protected function grid()
    {
        $u = Admin::user();
        $grid = new Grid(new AssignmentSubmission());
        $grid->model()->where('enterprise_id', $u->enterprise_id);

        // Role-based: teachers see only submissions for their assignments
        $isPrivileged = $u->isRole('admin') || $u->isRole('dos') || $u->isRole('hm');
        if (!$isPrivileged) {
            $myAssignmentIds = Assignment::where('enterprise_id', $u->enterprise_id)
                ->where('created_by_id', $u->id)
                ->pluck('id')
                ->toArray();
            $grid->model()->whereIn('assignment_id', $myAssignmentIds);
        }

        $grid->model()->orderBy('id', 'desc');
        $grid->model()->with(['assignment', 'student', 'academicClass', 'stream', 'subject', 'gradedBy']);

        // ── Filters ────────────────────────────────────────────────
        $grid->filter(function ($filter) use ($u) {
            $filter->disableIdFilter();

            // Filter by assignment
            $filter->equal('assignment_id', 'Assignment')->select(function () use ($u) {
                return Assignment::where('enterprise_id', $u->enterprise_id)
                    ->orderBy('id', 'desc')
                    ->pluck('title', 'id');
            });

            // Filter by student (ajax)
            $ajax_url = url(
                '/api/ajax-users?'
                    . 'enterprise_id=' . $u->enterprise_id
                    . '&search_by_1=name'
                    . '&search_by_2=id'
                    . '&user_type=student'
                    . '&model=User'
            );
            $filter->equal('student_id', 'Student')->select(function ($id) {
                $a = User::find($id);
                if ($a) return [$a->id => $a->name];
                return [];
            })->ajax($ajax_url);

            // Filter by class
            $filter->equal('academic_class_id', 'Class')->select(function () use ($u) {
                $activeTerm = $u->ent->active_term();
                if (!$activeTerm) return [];
                return AcademicClass::where('academic_year_id', $activeTerm->academic_year_id)
                    ->pluck('name', 'id');
            });

            // Filter by subject
            $filter->equal('subject_id', 'Subject')->select(function () use ($u) {
                return Subject::where('enterprise_id', $u->enterprise_id)
                    ->get()
                    ->pluck('subject_name', 'id');
            });

            // Filter by status
            $filter->equal('status', 'Status')->select([
                'Pending'       => 'Pending',
                'Submitted'     => 'Submitted',
                'Graded'        => 'Graded',
                'Returned'      => 'Returned',
                'Late'          => 'Late',
                'Not Submitted' => 'Not Submitted',
            ]);
        });

        // ── Columns ────────────────────────────────────────────────
        $grid->column('id', 'ID')->sortable()->width(60);

        $grid->column('assignment_id', 'Assignment')->display(function () {
            return $this->assignment ? $this->assignment->title : '#' . $this->assignment_id;
        })->width(200);

        $grid->column('student_id', 'Student')->display(function () {
            return $this->student ? $this->student->name : '#' . $this->student_id;
        })->width(160);

        $grid->column('academic_class_id', 'Class')->display(function () {
            $text = $this->academicClass ? $this->academicClass->name : '-';
            if ($this->stream) $text .= ' - ' . $this->stream->name;
            return $text;
        })->width(130);

        $grid->column('subject_id', 'Subject')->display(function () {
            return $this->subject ? $this->subject->subject_name : '-';
        })->width(120);

        $grid->column('status', 'Status')->display(function () {
            return $this->status_badge;
        })->sortable()->width(100);

        $grid->column('score', 'Score')->display(function () {
            return $this->score_text;
        })->sortable()->width(80);

        $grid->column('submitted_at', 'Submitted At')->display(function ($d) {
            return $d ? date('M d, Y H:i', strtotime($d)) : '-';
        })->sortable()->width(130);

        $grid->column('feedback', 'Feedback')->limit(40)->width(160);

        $grid->quickSearch(['student_id']);

        // Disable direct create (submissions are auto-generated)
        $grid->disableCreateButton();

        $grid->tools(function ($tools) {
            $tools->batch(function ($batch) {
                $batch->disableDelete();
            });
        });

        // Export
        $grid->export(function ($export) {
            $export->filename('Assignment_Submissions');
            $export->column('assignment_id', function ($value, $original) {
                $a = Assignment::find($original);
                return $a ? $a->title : $original;
            });
            $export->column('student_id', function ($value, $original) {
                $s = User::find($original);
                return $s ? $s->name : $original;
            });
            $export->column('academic_class_id', function ($value, $original) {
                $c = AcademicClass::find($original);
                return $c ? $c->name : $original;
            });
            $export->column('subject_id', function ($value, $original) {
                $s = Subject::find($original);
                return $s ? $s->subject_name : $original;
            });
        });

        return $grid;
    }

    // ── Detail ─────────────────────────────────────────────────────

    protected function detail($id)
    {
        $show = new Show(AssignmentSubmission::findOrFail($id));

        $show->field('id', 'ID');
        $show->field('created_at', 'Created');

        $show->divider('Assignment');
        $show->field('assignment_id', 'Assignment')->as(function () {
            return $this->assignment ? $this->assignment->title : '-';
        });
        $show->field('subject_id', 'Subject')->as(function () {
            return $this->subject ? $this->subject->subject_name : '-';
        });

        $show->divider('Student');
        $show->field('student_id', 'Student')->as(function () {
            return $this->student ? $this->student->name : '-';
        });
        $show->field('academic_class_id', 'Class')->as(function () {
            $text = $this->academicClass ? $this->academicClass->name : '-';
            if ($this->stream) $text .= ' - ' . $this->stream->name;
            return $text;
        });

        $show->divider('Submission');
        $show->field('status', 'Status');
        $show->field('submission_text', 'Submitted Text')->unescape()->as(function ($v) {
            return nl2br($v ?: '-');
        });
        $show->field('attachment', 'Submitted File')->file();
        $show->field('submitted_at', 'Submitted At');

        $show->divider('Grading');
        $show->field('score', 'Score');
        $show->field('max_score', 'Max Score');
        $show->field('feedback', 'Teacher Feedback')->unescape()->as(function ($v) {
            return nl2br($v ?: '-');
        });
        $show->field('teacher_comment', 'Teacher Comment')->unescape()->as(function ($v) {
            return nl2br($v ?: '-');
        });
        $show->field('graded_by_id', 'Graded By')->as(function () {
            return $this->gradedBy ? $this->gradedBy->name : '-';
        });
        $show->field('graded_at', 'Graded At');

        $show->divider('Parent');
        $show->field('parent_comment', 'Parent Comment')->unescape()->as(function ($v) {
            return nl2br($v ?: '-');
        });

        return $show;
    }

    // ── Form (Edit only — for grading) ─────────────────────────────

    protected function form()
    {
        $form = new Form(new AssignmentSubmission());
        $u = Admin::user();

        // Display-only fields (not editable)
        $form->display('assignment_title', 'Assignment')->default(function ($form) {
            $model = AssignmentSubmission::find(request()->route('assignment_submission'));
            return $model && $model->assignment ? $model->assignment->title : '-';
        });

        $form->display('student_name', 'Student')->default(function ($form) {
            $model = AssignmentSubmission::find(request()->route('assignment_submission'));
            return $model && $model->student ? $model->student->name : '-';
        });

        $form->divider('Submission Content');
        $form->textarea('submission_text', 'Submitted Text')
            ->rows(4)
            ->readonly()
            ->help('Submitted by the student');
        $form->file('attachment', 'Submitted File')
            ->uniqueName()
            ->help('File submitted by the student');

        $form->divider('Grading');

        $form->select('status', 'Status')
            ->options([
                'Pending'       => 'Pending',
                'Submitted'     => 'Submitted',
                'Graded'        => 'Graded',
                'Returned'      => 'Returned (needs revision)',
                'Late'          => 'Late',
                'Not Submitted' => 'Not Submitted',
            ])
            ->rules('required');

        $form->decimal('score', 'Score')
            ->help('Score awarded to the student');

        $form->display('max_score_display', 'Max Score')->default(function () {
            $model = AssignmentSubmission::find(request()->route('assignment_submission'));
            return $model ? $model->max_score : '-';
        });

        $form->textarea('feedback', 'Feedback to Student')
            ->rows(3)
            ->placeholder('Well done! You can improve on...');

        $form->textarea('teacher_comment', 'Teacher Comment (Internal)')
            ->rows(2)
            ->placeholder('Internal notes...');

        $form->hidden('graded_by_id')->value($u->id);

        // If status is set to Graded, auto-set graded_at
        $form->saving(function (Form $form) use ($u) {
            if ($form->status === 'Graded') {
                $form->graded_by_id = $u->id;
                $form->graded_at = now();
            }
        });

        $form->saved(function (Form $form) {
            $sub = $form->model();
            admin_success('Success', 'Submission updated.');
            return redirect(admin_url('assignment-submissions?assignment_id=' . $sub->assignment_id));
        });

        return $form;
    }
}
