<?php

namespace App\Admin\Controllers;

use App\Models\AcademicClass;
use App\Models\AcademicClassSctream;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Subject;
use App\Models\Term;
use App\Models\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Layout\Content;

class AssignmentController extends AdminController
{
    protected $title = 'Assignments & Homework';

    // ── Grid ───────────────────────────────────────────────────────

    protected function grid()
    {
        $u = Admin::user();
        $grid = new Grid(new Assignment());
        $grid->model()->where('enterprise_id', $u->enterprise_id);

        // Role-based access: admin/dos/hm see all; teachers see only their own
        $isPrivileged = $u->isRole('admin') || $u->isRole('dos') || $u->isRole('hm');
        if (!$isPrivileged) {
            $grid->model()->where('created_by_id', $u->id);
        }

        $grid->model()->orderBy('id', 'desc');
        $grid->model()->with(['subject', 'academicClass', 'stream', 'createdBy', 'term']);

        // ── Filters ────────────────────────────────────────────────
        $grid->filter(function ($filter) use ($u) {
            $filter->disableIdFilter();

            $filter->equal('subject_id', 'Subject')->select(function () use ($u) {
                return Subject::where('enterprise_id', $u->enterprise_id)
                    ->get()
                    ->pluck('subject_name', 'id');
            });

            $filter->equal('academic_class_id', 'Class')->select(function () use ($u) {
                $activeTerm = $u->ent->active_term();
                if (!$activeTerm) return [];
                return AcademicClass::where('academic_year_id', $activeTerm->academic_year_id)
                    ->pluck('name', 'id');
            });

            $filter->equal('type', 'Type')->select([
                'Homework'   => 'Homework',
                'Assignment' => 'Assignment',
                'Project'    => 'Project',
                'Classwork'  => 'Classwork',
                'Quiz'       => 'Quiz',
            ]);

            $filter->equal('status', 'Status')->select([
                'Draft'     => 'Draft',
                'Published' => 'Published',
                'Closed'    => 'Closed',
                'Archived'  => 'Archived',
            ]);

            $filter->between('due_date', 'Due Date')->date();
        });

        // ── Columns ────────────────────────────────────────────────
        $grid->column('id', 'ID')->sortable()->width(60);

        $grid->column('created_at', 'Date')->display(function ($d) {
            return date('M d, Y', strtotime($d));
        })->sortable()->width(100);

        $grid->column('title', 'Title')->sortable()->limit(40)->width(200);

        $grid->column('type', 'Type')->label([
            'Homework'   => 'primary',
            'Assignment' => 'info',
            'Project'    => 'warning',
            'Classwork'  => 'default',
            'Quiz'       => 'success',
        ])->sortable()->width(100);

        $grid->column('subject_id', 'Subject')->display(function () {
            return $this->subject ? $this->subject->subject_name : '-';
        })->width(140);

        $grid->column('academic_class_id', 'Target')->display(function () {
            return $this->target_text;
        })->width(140);

        $grid->column('created_by_id', 'Teacher')->display(function () {
            return $this->createdBy ? $this->createdBy->name : '-';
        })->width(120);

        $grid->column('due_date', 'Due Date')->display(function ($d) {
            if (!$d) return '-';
            $isPast = strtotime($d) < time();
            $formatted = date('M d, Y', strtotime($d));
            $color = $isPast ? 'danger' : 'default';
            return "<span class='label label-{$color}'>{$formatted}</span>";
        })->sortable()->width(110);

        $grid->column('progress', 'Submissions')->display(function () {
            $total = $this->total_students ?: 0;
            $submitted = $this->submitted_count ?: 0;
            $graded = $this->graded_count ?: 0;
            if ($total == 0) return '<span class="label label-default">No students</span>';
            $pct = round(($submitted / $total) * 100);
            return "<small>{$submitted}/{$total} submitted, {$graded} graded ({$pct}%)</small>";
        })->width(180);

        $grid->column('status', 'Status')->display(function () {
            return $this->status_badge;
        })->sortable()->width(90);

        $grid->column('is_assessed', 'Assessed')->label([
            'Yes' => 'success',
            'No'  => 'default',
        ])->width(80);

        $grid->quickSearch('title');

        // ── Disable bulk delete ────────────────────────────────────
        $grid->tools(function ($tools) {
            $tools->batch(function ($batch) {
                $batch->disableDelete();
            });
        });

        $grid->actions(function ($actions) {
            $id = $actions->getKey();
            // Add a "View Submissions" button
            $url = admin_url('assignment-submissions?assignment_id=' . $id);
            $actions->prepend('<a class="btn btn-xs btn-primary" href="' . $url . '" title="View Submissions"><i class="fa fa-list"></i> Submissions</a> ');
        });

        return $grid;
    }

    // ── Detail ─────────────────────────────────────────────────────

    protected function detail($id)
    {
        $show = new Show(Assignment::findOrFail($id));

        $show->panel()->tools(function ($tools) {
            // keep all default tools
        });

        $show->field('id', 'ID');
        $show->field('created_at', 'Created');
        $show->field('title', 'Title');
        $show->field('type', 'Type');
        $show->field('status', 'Status')->unescape();

        $show->divider('Academic Context');
        $show->field('term_id', 'Term')->as(function () {
            return $this->term ? $this->term->name_text : '-';
        });
        $show->field('subject_id', 'Subject')->as(function () {
            return $this->subject ? $this->subject->subject_name : '-';
        });
        $show->field('academic_class_id', 'Target')->as(function () {
            return $this->target_text;
        });
        $show->field('created_by_id', 'Created By')->as(function () {
            return $this->createdBy ? $this->createdBy->name : '-';
        });

        $show->divider('Assignment Details');
        $show->field('description', 'Description')->unescape()->as(function ($v) {
            return nl2br($v ?: '-');
        });
        $show->field('instructions', 'Instructions')->unescape()->as(function ($v) {
            return nl2br($v ?: '-');
        });
        $show->field('issue_date', 'Issue Date');
        $show->field('due_date', 'Due Date');
        $show->field('attachment', 'Attachment')->file();

        $show->divider('Scoring & Submission');
        $show->field('max_score', 'Max Score');
        $show->field('is_assessed', 'Will Be Assessed');
        $show->field('submission_type', 'Submission Type');
        $show->field('marks_display', 'Show Marks to Students/Parents');

        $show->divider('Statistics');
        $show->field('total_students', 'Total Students');
        $show->field('submitted_count', 'Submitted');
        $show->field('graded_count', 'Graded');

        return $show;
    }

    // ── Form ───────────────────────────────────────────────────────

    protected function form()
    {
        $form = new Form(new Assignment());
        $u = Admin::user();

        // Enterprise (hidden)
        $form->hidden('enterprise_id')->value($u->enterprise_id);

        // Active term info
        $activeTerm = $u->ent->active_term();
        $termName = $activeTerm ? $activeTerm->name_text : 'No active term';
        $form->display('_term', 'Current Term')->default($termName);

        // ── Assignment Details ──────────────────────────────────────
        $form->divider('Assignment Details');

        $form->text('title', 'Title')
            ->rules('required|min:3')
            ->placeholder('e.g. Chapter 5 Revision Questions');

        $form->select('type', 'Type')->options([
            'Homework'   => 'Homework',
            'Assignment' => 'Assignment',
            'Project'    => 'Project',
            'Classwork'  => 'Classwork',
            'Quiz'       => 'Quiz',
        ])->default('Homework')->rules('required');

        $form->textarea('description', 'Description')
            ->rows(3)
            ->placeholder('Brief description of the assignment...');

        $form->textarea('instructions', 'Instructions')
            ->rows(4)
            ->placeholder('Detailed instructions for students...');

        // ── Academic Context ────────────────────────────────────────
        $form->divider('Academic Context');

        // Subject select: role-based
        $isPrivileged = $u->isRole('admin') || $u->isRole('dos') || $u->isRole('hm');
        if ($isPrivileged) {
            $subjects = Subject::where('enterprise_id', $u->enterprise_id)
                ->get()
                ->mapWithKeys(function ($s) {
                    $className = $s->academic_class ? $s->academic_class->name : '';
                    return [$s->id => $s->subject_name . ' (' . $className . ')'];
                })->toArray();
        } else {
            // Teacher: only subjects they are assigned to
            $subjects = Subject::where('enterprise_id', $u->enterprise_id)
                ->where(function ($q) use ($u) {
                    $q->where('subject_teacher', $u->id)
                        ->orWhere('teacher_1', $u->id)
                        ->orWhere('teacher_2', $u->id)
                        ->orWhere('teacher_3', $u->id);
                })
                ->get()
                ->mapWithKeys(function ($s) {
                    $className = $s->academic_class ? $s->academic_class->name : '';
                    return [$s->id => $s->subject_name . ' (' . $className . ')'];
                })->toArray();
        }

        $form->select('subject_id', 'Subject')
            ->options($subjects)
            ->rules('required');

        // Class select → cascading to Stream
        $classes = [];
        if ($activeTerm) {
            $classes = AcademicClass::where('academic_year_id', $activeTerm->academic_year_id)
                ->pluck('name', 'id')
                ->toArray();
        }

        $form->select('academic_class_id', 'Target Class')
            ->options($classes)
            ->rules('required')
            ->help('All students in this class will receive the assignment')
            ->load('stream_id', url('/api/streams?enterprise_id=' . $u->enterprise_id));

        $form->select('stream_id', 'Stream (Optional)')
            ->options(function ($id) {
                if (!$id) return [];
                $s = AcademicClassSctream::find($id);
                if ($s) return [$s->id => $s->name_text];
                return [];
            })
            ->help('Leave empty to target the whole class, or select a stream to target only that stream');

        // ── Dates ──────────────────────────────────────────────────
        $form->divider('Timeline');

        $form->date('issue_date', 'Issue Date')
            ->default(date('Y-m-d'))
            ->help('Date the assignment is given to students');

        $form->date('due_date', 'Due Date')
            ->help('Deadline for submissions');

        // ── Scoring & Submission ────────────────────────────────────
        $form->divider('Scoring & Submissions');

        $form->radio('is_assessed', 'Will Be Assessed?')
            ->options(['Yes' => 'Yes - will be scored', 'No' => 'No - for completion only'])
            ->default('Yes')
            ->when('Yes', function (Form $form) {
                $form->decimal('max_score', 'Maximum Score')
                    ->default(100)
                    ->help('Maximum achievable score');
            });

        $form->radio('submission_type', 'How Students Submit')
            ->options([
                'Both' => 'File upload or text (both allowed)',
                'File' => 'File upload only',
                'Text' => 'Text submission only',
                'None' => 'No submission required (in-class)',
            ])
            ->default('Both');

        $form->radio('marks_display', 'Show Scores to Students/Parents?')
            ->options(['Yes' => 'Yes', 'No' => 'No'])
            ->default('No')
            ->help('When enabled, students and parents can see their scores in the mobile app');

        // ── Attachment ──────────────────────────────────────────────
        $form->divider('Reference Material');

        $form->file('attachment', 'Attachment')
            ->uniqueName()
            ->help('Upload a reference document (PDF, image, etc.)');

        // ── Status ─────────────────────────────────────────────────
        $form->divider('Status');

        $form->radio('status', 'Assignment Status')
            ->options([
                'Draft'     => 'Draft (not visible to students yet)',
                'Published' => 'Published (visible to students & parents)',
                'Closed'    => 'Closed (no more submissions accepted)',
                'Archived'  => 'Archived',
            ])
            ->default('Published');

        $form->textarea('details', 'Additional Notes')
            ->rows(2)
            ->placeholder('Any extra notes...');

        // ── Teacher assignment ──────────────────────────────────────
        if ($isPrivileged) {
            $form->divider('Teacher');
            $teachers = User::where('enterprise_id', $u->enterprise_id)
                ->where('user_type', 'employee')
                ->pluck('name', 'id')
                ->toArray();
            $form->select('created_by_id', 'Created By (Teacher)')
                ->options($teachers)
                ->default($u->id);
        } else {
            $form->hidden('created_by_id')->value($u->id);
        }

        // ── Post-save ──────────────────────────────────────────────
        $form->saved(function (Form $form) {
            admin_success('Success', 'Assignment saved. Student submission records have been generated.');
            return redirect(admin_url('assignments'));
        });

        return $form;
    }

    // ── Custom: Regenerate Submissions ─────────────────────────────

    public function regenerateSubmissions($id)
    {
        $assignment = Assignment::findOrFail($id);
        $u = Admin::user();

        if ($assignment->enterprise_id != $u->enterprise_id) {
            admin_error('Error', 'Unauthorized.');
            return redirect(admin_url('assignments'));
        }

        $assignment->regenerateSubmissions();
        admin_success('Success', "Submissions regenerated. Total: {$assignment->total_students} students.");
        return redirect(admin_url('assignments/' . $id));
    }
}
