<?php

namespace App\Admin\Controllers;

use App\Models\AcademicClass;
use App\Models\LessonPlan;
use App\Models\Subject;
use App\Models\Term;
use App\Models\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class LessonPlanController extends AdminController
{
    protected $title = 'Lesson Plans';

    private function isPrivileged($u)
    {
        return $u->isRole('admin') || $u->isRole('dos') || $u->isRole('hm');
    }

    private function scopeForUser($query, $u, $isPrivileged)
    {
        $query->where('enterprise_id', $u->enterprise_id);

        if (!$isPrivileged) {
            $query->where(function ($q) use ($u) {
                $q->where('teacher_id', $u->id)
                    ->orWhere('supervisor_id', $u->id);
            });
        }

        return $query;
    }

    private function findAccessiblePlanOrFail($id, $u, $isPrivileged)
    {
        $q = LessonPlan::with(['term', 'academic_class', 'subject', 'teacher', 'supervisor', 'reviewer'])
            ->where('id', $id)
            ->where('enterprise_id', $u->enterprise_id);

        if (!$isPrivileged) {
            $q->where(function ($sq) use ($u) {
                $sq->where('teacher_id', $u->id)
                    ->orWhere('supervisor_id', $u->id);
            });
        }

        return $q->firstOrFail();
    }

    protected function grid()
    {
        $u = Admin::user();
        $isPrivileged = $this->isPrivileged($u);

        $grid = new Grid(new LessonPlan());
        $grid->disableBatchActions();

        $this->scopeForUser($grid->model(), $u, $isPrivileged)
            ->orderBy('plan_date', 'desc')
            ->orderBy('id', 'desc');

        $grid->tools(function ($tools) {
            $tools->append('<a href="' . admin_url('lesson-plans-dashboard') . '" class="btn btn-sm btn-info" style="margin-right:8px;"><i class="fa fa-dashboard"></i> Workflow Dashboard</a>');
        });

        $grid->filter(function ($filter) use ($u) {
            $filter->disableIdFilter();

            $filter->equal('template_type', 'Template')->select([
                'upper' => 'Upper',
                'lower' => 'Lower',
                'language' => 'Language',
                'nursery' => 'Nursery',
            ]);

            $filter->equal('term_id', 'Term')->select(
                Term::where('enterprise_id', $u->enterprise_id)
                    ->orderBy('id', 'desc')
                    ->get()
                    ->pluck('name_text', 'id')
            );

            $filter->equal('academic_class_id', 'Class')->select(
                AcademicClass::where('enterprise_id', $u->enterprise_id)
                    ->orderBy('name')
                    ->get()
                    ->pluck('name', 'id')
            );

            $filter->equal('status', 'Status')->select([
                'Draft' => 'Draft',
                'Submitted' => 'Submitted',
                'Changes Requested' => 'Changes Requested',
                'Approved' => 'Approved',
            ]);

            $filter->equal('supervisor_id', 'Supervisor')->select(
                \Encore\Admin\Auth\Database\Administrator::where([
                    'enterprise_id' => $u->enterprise_id,
                    'user_type'     => 'employee',
                ])
                    ->orderBy('name')
                    ->get()
                    ->pluck('name', 'id')
            );

            $filter->between('plan_date', 'Date')->date();
        });

        $grid->quickSearch('theme', 'topic', 'sub_topic', 'sub_theme', 'learning_area', 'content')
            ->placeholder('Search lesson plans...');

        $grid->column('id', 'ID')->sortable();

        $grid->column('plan_date', 'Date')->display(function ($v) {
            return $v ? date('d M Y', strtotime($v)) : '<span class="text-muted">-</span>';
        })->sortable();

        $grid->column('template_type', 'Template')->display(function ($v) {
            $labels = [
                'upper' => 'Upper',
                'lower' => 'Lower',
                'language' => 'Language',
                'nursery' => 'Nursery',
            ];
            $label = $labels[$v] ?? ucfirst((string) $v);
            return '<span class="label label-primary">' . e($label) . '</span>';
        })->sortable();

        $grid->column('academic_class_id', 'Class')->display(function () {
            return optional($this->academic_class)->name ?? '<span class="text-muted">-</span>';
        })->sortable();

        $grid->column('subject_id', 'Subject / Learning Area')->display(function () {
            if ($this->template_type === 'nursery') {
                return $this->learning_area ?: '<span class="text-muted">-</span>';
            }
            return optional($this->subject)->subject_name ?? '<span class="text-muted">-</span>';
        });

        $grid->column('teacher_id', 'Teacher')->display(function () {
            return optional($this->teacher)->name ?? '<span class="text-muted">-</span>';
        })->sortable();

        $grid->column('supervisor_id', 'Supervisor')->display(function () {
            return optional($this->supervisor)->name ?? '<span class="text-muted">-</span>';
        })->hide();

        $grid->column('theme', 'Theme')->display(function ($v) {
            return $v ?: '<span class="text-muted">-</span>';
        })->hide();

        $grid->column('topic', 'Topic')->display(function ($v) {
            return $v ?: '<span class="text-muted">-</span>';
        });

        $grid->column('sub_topic', 'Sub Topic')->display(function ($v) {
            return $v ?: '<span class="text-muted">-</span>';
        })->hide();

        $grid->column('time_text', 'Time');
        $grid->column('no_of_pupils', 'Pupils');

        $grid->column('status', 'Status')->label([
            'Draft' => 'default',
            'Submitted' => 'warning',
            'Changes Requested' => 'danger',
            'Approved' => 'success',
        ])->sortable();

        $grid->column('workflow_actions', 'Workflow')->display(function () use ($u, $isPrivileged) {
            $id = (int) $this->id;
            $status = (string) $this->status;
            $isTeacher = (int) $this->teacher_id === (int) $u->id;
            $canReview = $isPrivileged || (int) $this->supervisor_id === (int) $u->id;

            $html = '';

            if ($isTeacher && in_array($status, ['Draft', 'Changes Requested'])) {
                $submitUrl = admin_url('lesson-plans/' . $id . '/submit');
                $html .= '<a class="btn btn-xs btn-warning" href="' . $submitUrl . '" onclick="return confirm(\'Submit this lesson plan for review?\')"><i class="fa fa-send"></i> Submit</a> ';
            }

            if ($canReview && $status === 'Submitted') {
                $approveUrl = admin_url('lesson-plans/' . $id . '/review?action=approve');
                $changesUrl = admin_url('lesson-plans/' . $id . '/review?action=changes');
                $html .= '<a class="btn btn-xs btn-success" href="' . $approveUrl . '" onclick="return confirm(\'Approve this lesson plan?\')"><i class="fa fa-check"></i> Approve</a> ';
                $html .= '<a class="btn btn-xs btn-danger" href="' . $changesUrl . '" onclick="return confirm(\'Request changes from teacher?\')"><i class="fa fa-reply"></i> Request Changes</a>';
            }

            return $html ?: '<span class="text-muted">-</span>';
        });

        $grid->column('print_plan', 'Print')->display(function () {
            $url = admin_url('lesson-plans/' . $this->id . '/print');
            return '<a class="btn btn-xs btn-primary" target="_blank" rel="noopener" href="' . $url . '"><i class="fa fa-print"></i> Print</a>';
        });

        $grid->actions(function ($actions) {
            $id = $actions->getKey();
            $url = admin_url('lesson-plans/' . $id . '/print');
            $actions->append('<a href="' . $url . '" target="_blank" rel="noopener" title="Print lesson plan" style="margin-left:6px;"><i class="fa fa-print"></i></a>');
        });

        return $grid;
    }

    public function dashboard(Content $content)
    {
        $u = Admin::user();
        $isPrivileged = $this->isPrivileged($u);

        // base: all plans for this enterprise
        $base = LessonPlan::query()->where('enterprise_id', $u->enterprise_id);

        // Privileged users see enterprise-wide stats; teachers see only their own
        $statsBase  = $isPrivileged ? (clone $base) : (clone $base)->where('teacher_id', $u->id);
        $reviewBase = $isPrivileged
            ? (clone $base)
            : (clone $base)->where('supervisor_id', $u->id);

        $stats = [
            'my_total'     => (clone $statsBase)->count(),
            'my_draft'     => (clone $statsBase)->where('status', 'Draft')->count(),
            'my_submitted' => (clone $statsBase)->where('status', 'Submitted')->count(),
            'my_changes'   => (clone $statsBase)->where('status', 'Changes Requested')->count(),
            'my_approved'  => (clone $statsBase)->where('status', 'Approved')->count(),
            'to_review'    => (clone $reviewBase)->where('status', 'Submitted')->count(),
        ];

        $pendingReviews = (clone $reviewBase)
            ->where('status', 'Submitted')
            ->with(['teacher', 'subject', 'academic_class'])
            ->orderBy('submitted_at', 'desc')
            ->limit(20)
            ->get();

        // Recent plans: enterprise-wide for privileged, own only for teachers
        $recentMine = (clone $statsBase)
            ->with(['subject', 'academic_class', 'teacher'])
            ->orderBy('updated_at', 'desc')
            ->limit(15)
            ->get();

        return $content
            ->title('Lesson Plans Workflow Dashboard')
            ->description('Clear accountability for submission and approval')
            ->body(view('admin.lesson-plans-dashboard', [
                'stats'          => $stats,
                'pendingReviews' => $pendingReviews,
                'recentMine'     => $recentMine,
                'isPrivileged'   => $isPrivileged,
                'currentUserId'  => $u->id,
            ]));
    }

    public function submit($id, Request $request)
    {
        $u = Admin::user();

        $plan = LessonPlan::where('enterprise_id', $u->enterprise_id)
            ->where('id', $id)
            ->where('teacher_id', $u->id)
            ->firstOrFail();

        if (!in_array($plan->status, ['Draft', 'Changes Requested'])) {
            admin_warning('Not allowed', 'Only Draft or Changes Requested plans can be submitted.');
            return back();
        }

        $comment = trim((string) ($request->get('comment', '') ?: $plan->submission_comment));
        if ($comment === '') {
            admin_warning('Comment required', 'Add a submission comment before submitting for review.');
            return redirect(admin_url('lesson-plans/' . $plan->id . '/edit'));
        }

        $plan->status = 'Submitted';
        $plan->submission_comment = $comment;
        $plan->submitted_at = now();
        $plan->reviewed_at = null;
        $plan->reviewed_by = null;
        $plan->save();

        admin_success('Submitted', 'Lesson plan submitted to supervisor for review.');
        return redirect(admin_url('lesson-plans/' . $plan->id));
    }

    public function review($id, Request $request)
    {
        $u = Admin::user();
        $isPrivileged = $this->isPrivileged($u);

        $plan = LessonPlan::where('enterprise_id', $u->enterprise_id)
            ->where('id', $id)
            ->firstOrFail();

        if (!$isPrivileged && (int) $plan->supervisor_id !== (int) $u->id) {
            abort(403, 'You are not allowed to review this lesson plan.');
        }

        if ($plan->status !== 'Submitted') {
            admin_warning('Not allowed', 'Only Submitted lesson plans can be reviewed. Current status: ' . $plan->status);
            return redirect(admin_url('lesson-plans/' . $plan->id));
        }

        $action = (string) $request->get('action', '');
        if (!in_array($action, ['approve', 'changes'])) {
            admin_warning('Invalid action', 'Unknown review action.');
            return redirect(admin_url('lesson-plans/' . $plan->id));
        }

        $plan->status = $action === 'approve' ? 'Approved' : 'Changes Requested';
        $plan->reviewed_by = $u->id;
        $plan->reviewed_at = now();
        if ($action === 'approve' && empty($plan->supervisor_comment)) {
            $plan->supervisor_comment = 'Approved by ' . $u->name . ' on ' . now()->format('d M Y') . '.';
        }
        if ($action === 'changes' && empty($plan->supervisor_comment)) {
            $plan->supervisor_comment = 'Please revise and resubmit with improvements.';
        }

        $plan->save();

        admin_success('Reviewed', $action === 'approve' ? 'Lesson plan approved successfully.' : 'Changes requested from teacher.');
        return redirect(admin_url('lesson-plans/' . $plan->id));
    }

    public function print($id)
    {
        $u = Admin::user();
        $isPrivileged = $this->isPrivileged($u);
        $plan = $this->findAccessiblePlanOrFail($id, $u, $isPrivileged);

        $ent = $u->ent;
        $pdf = App::make('dompdf.wrapper');
        $pdf->setPaper('A4', 'portrait');
        $pdf->loadHTML(view('print.lesson-plan-print', [
            'plan'  => $plan,
            'ent'   => $ent,
            'isPdf' => true,
        ]));

        $name = 'lesson-plan-' . $plan->id . '-' . date('Ymd') . '.pdf';
        return $pdf->stream($name);
    }

    protected function detail($id)
    {
        $u = Admin::user();
        $isPrivileged = $this->isPrivileged($u);
        $plan = $this->findAccessiblePlanOrFail($id, $u, $isPrivileged);
        $canReview = $isPrivileged || (int) $plan->supervisor_id === (int) $u->id;
        $isTeacher = (int) $plan->teacher_id === (int) $u->id;

        $show = new Show($plan);

        $show->panel()->tools(function ($tools) use ($id, $isTeacher, $canReview, $plan) {
            $tools->append('<a href="' . admin_url('lesson-plans-dashboard') . '" class="btn btn-sm btn-info" style="margin-right:8px;"><i class="fa fa-dashboard"></i>&nbsp;Dashboard</a>');
            $url = admin_url('lesson-plans/' . $id . '/print');
            $tools->append('<a href="' . $url . '" target="_blank" rel="noopener" class="btn btn-sm btn-primary" style="margin-right:8px;"><i class="fa fa-print"></i>&nbsp;Print</a>');

            if ($isTeacher && in_array($plan->status, ['Draft', 'Changes Requested'])) {
                $submitUrl = admin_url('lesson-plans/' . $id . '/submit');
                $tools->append('<a href="' . $submitUrl . '" class="btn btn-sm btn-warning" style="margin-right:8px;" onclick="return confirm(\'Submit this lesson plan for review?\')"><i class="fa fa-send"></i>&nbsp;Submit for Review</a>');
            }

            if ($canReview && $plan->status === 'Submitted') {
                $approveUrl = admin_url('lesson-plans/' . $id . '/review?action=approve');
                $changesUrl = admin_url('lesson-plans/' . $id . '/review?action=changes');
                $tools->append('<a href="' . $approveUrl . '" class="btn btn-sm btn-success" style="margin-right:8px;" onclick="return confirm(\'Approve this lesson plan?\')"><i class="fa fa-check"></i>&nbsp;Approve</a>');
                $tools->append('<a href="' . $changesUrl . '" class="btn btn-sm btn-danger" style="margin-right:8px;" onclick="return confirm(\'Request changes from teacher?\')"><i class="fa fa-reply"></i>&nbsp;Request Changes</a>');
            }
        });

        $show->field('id', 'ID');
        $show->field('plan_date', 'Date');
        $show->field('template_type', 'Template');
        $show->field('term_id', 'Term')->as(function () { return optional($this->term)->name_text; });
        $show->field('academic_class_id', 'Class')->as(function () { return optional($this->academic_class)->name; });
        $show->field('subject_id', 'Subject')->as(function () { return optional($this->subject)->subject_name; });
        $show->field('teacher_id', 'Teacher')->as(function () { return optional($this->teacher)->name; });
        $show->field('supervisor_id', 'Supervisor')->as(function () { return optional($this->supervisor)->name; });
        $show->field('time_text', 'Time');
        $show->field('no_of_pupils', 'No. of Pupils');

        $show->divider('Core Plan');
        $show->field('theme', 'Theme');
        $show->field('topic', 'Topic');
        $show->field('sub_topic', 'Sub Topic');
        $show->field('sub_theme', 'Sub Theme');
        $show->field('aspect', 'Aspect');
        $show->field('language_skill', 'Language Skill');
        $show->field('learning_area', 'Learning Area');
        $show->field('learning_outcome', 'Learning Outcome')->unescape()->as(function ($v) { return nl2br(e($v ?: '-')); });

        $show->field('subject_competences', 'Subject Competences')->unescape()->as(function ($v) { return nl2br(e($v ?: '-')); });
        $show->field('language_competences', 'Language Competences')->unescape()->as(function ($v) { return nl2br(e($v ?: '-')); });
        $show->field('competences', 'Competences (Nursery)')->unescape()->as(function ($v) { return nl2br(e($v ?: '-')); });
        $show->field('methods_techniques', 'Methods / Techniques')->unescape()->as(function ($v) { return nl2br(e($v ?: '-')); });
        $show->field('content', 'Content')->unescape()->as(function ($v) { return nl2br(e($v ?: '-')); });
        $show->field('skills_values', 'Skills and Values')->unescape()->as(function ($v) { return nl2br(e($v ?: '-')); });
        $show->field('developmental_activities', 'Developmental Activities')->unescape()->as(function ($v) { return nl2br(e($v ?: '-')); });
        $show->field('teaching_activities', 'Teaching Activities')->unescape()->as(function ($v) { return nl2br(e($v ?: '-')); });
        $show->field('learning_aids', 'Learning Aids')->unescape()->as(function ($v) { return nl2br(e($v ?: '-')); });
        $show->field('references', 'References')->unescape()->as(function ($v) { return nl2br(e($v ?: '-')); });

        $show->field('lesson_procedure', 'Lesson Procedure')->unescape()->as(function ($rows) {
            if (!is_array($rows) || count($rows) === 0) {
                return '<span class="text-muted">No procedure rows added.</span>';
            }

            $html = '<table class="table table-bordered table-condensed"><thead><tr><th style="width:15%">Duration</th><th style="width:15%">Step</th><th style="width:35%">Teacher Activity</th><th style="width:35%">Pupil Activity</th></tr></thead><tbody>';
            foreach ($rows as $r) {
                $duration = e($r['duration'] ?? '');
                $step = e($r['step'] ?? '');
                $teacher = nl2br(e($r['teacher_activity'] ?? ''));
                $pupil = nl2br(e($r['pupil_activity'] ?? ''));
                $html .= "<tr><td>{$duration}</td><td>{$step}</td><td>{$teacher}</td><td>{$pupil}</td></tr>";
            }
            $html .= '</tbody></table>';
            return $html;
        });

        $show->divider('Self Evaluation');
        $show->field('self_strengths', 'Strengths')->unescape()->as(function ($v) { return nl2br(e($v ?: '-')); });
        $show->field('self_areas_improvement', 'Areas of Improvements')->unescape()->as(function ($v) { return nl2br(e($v ?: '-')); });
        $show->field('self_strategies', 'Strategies')->unescape()->as(function ($v) { return nl2br(e($v ?: '-')); });

        $show->field('status', 'Status');
        $show->field('submission_comment', 'Submission Comment')->unescape()->as(function ($v) { return nl2br(e($v ?: '-')); });
        $show->field('supervisor_comment', 'Supervisor Review Comment')->unescape()->as(function ($v) { return nl2br(e($v ?: '-')); });
        $show->field('submitted_at', 'Submitted At');
        $show->field('reviewed_at', 'Reviewed At');
        $show->field('reviewed_by', 'Reviewed By')->as(function () { return optional($this->reviewer)->name ?: '-'; });
        $show->field('created_at', 'Created At');
        $show->field('updated_at', 'Updated At');

        return $show;
    }

    protected function form()
    {
        $form = new Form(new LessonPlan());
        $u = Admin::user();
        $isPrivileged = $this->isPrivileged($u);
        $userModel = User::find($u->id);
        $activeTerm = Term::where(['enterprise_id' => $u->enterprise_id, 'is_active' => 1])->first();

        $editingPlan = null;
        if ($form->isEditing()) {
            $routeId = request()->route('lesson_plan');
            $editingPlan = LessonPlan::where('enterprise_id', $u->enterprise_id)->find($routeId);
        }
        $canReview = $editingPlan ? ($isPrivileged || (int) $editingPlan->supervisor_id === (int) $u->id) : $isPrivileged;

        $form->hidden('enterprise_id')->default($u->enterprise_id);

        $form->select('template_type', 'Template Type')
            ->options([
                'upper' => 'Upper',
                'lower' => 'Lower',
                'language' => 'Language',
                'nursery' => 'Nursery',
            ])
            ->default('upper')
            ->rules('required');

        $termOptions = Term::where('enterprise_id', $u->enterprise_id)
            ->orderBy('id', 'desc')
            ->get()
            ->pluck('name_text', 'id');
        $form->select('term_id', 'Term')->options($termOptions)->default(optional($activeTerm)->id)->rules('required');

        $classOptions = AcademicClass::where('enterprise_id', $u->enterprise_id)
            ->orderBy('name')
            ->get()
            ->pluck('name', 'id');
        $form->select('academic_class_id', 'Class')->options($classOptions)->rules('required');

        $subjectOptions = Subject::where('enterprise_id', $u->enterprise_id)
            ->orderBy('subject_name')
            ->get()
            ->pluck('subject_name', 'id');
        $form->select('subject_id', 'Subject')->options($subjectOptions);
        $form->text('learning_area', 'Learning Area');

        if ($isPrivileged) {
            $teachers = \Encore\Admin\Auth\Database\Administrator::where([
                'enterprise_id' => $u->enterprise_id,
                'user_type' => 'employee',
            ])->orderBy('name')->get()->pluck('name', 'id');
            $form->select('teacher_id', 'Teacher')->options($teachers)->default($u->id)->rules('required');
            $form->select('supervisor_id', 'Supervisor')->options($teachers)->default($u->id)->rules('required');
        } else {
            $form->hidden('teacher_id')->default($u->id);
            $form->hidden('supervisor_id')->default($userModel->supervisor_id ?: $u->id);
            $form->display('_teacher', 'Teacher')->default($u->name);
        }

        $form->date('plan_date', 'Date')->default(date('Y-m-d'))->rules('required|date')->width(4);
        $form->text('time_text', 'Time')->help('Example: 10:00am - 10:40am')->width(4);
        $form->number('no_of_pupils', 'No. of Pupils')->min(0)->width(4);

        $form->divider('Planning Content');
        $form->text('theme', 'Theme')->width(6);
        $form->text('topic', 'Topic')->width(6);
        $form->text('sub_topic', 'Sub Topic')->width(6);
        $form->text('sub_theme', 'Sub Theme')->width(6);
        $form->text('aspect', 'Aspect')->width(6);
        $form->text('language_skill', 'Language Skill')->width(6);
        $form->textarea('learning_outcome', 'Learning Outcome')->rows(3);

        $form->textarea('subject_competences', 'Subject Competences')->rows(4)->rules('required|min:3');
        $form->textarea('language_competences', 'Language Competences')->rows(4);
        $form->textarea('competences', 'Competences (Nursery)')->rows(4);
        $form->textarea('methods_techniques', 'Methods / Techniques')->rows(4)->rules('required|min:3');
        $form->textarea('content', 'Content')->rows(6)->rules('required|min:3');
        $form->textarea('skills_values', 'Skills and Values')->rows(4)->rules('required|min:3');
        $form->textarea('developmental_activities', 'Developmental Activities')->rows(4);
        $form->textarea('teaching_activities', 'Teaching Activities')->rows(4);
        $form->textarea('learning_aids', 'Learning Aids (Resources)')->rows(4)->rules('required|min:2');
        $form->textarea('references', 'References')->rows(4)->rules('required|min:2');

        $form->divider('Lesson Procedure');
        $form->table('lesson_procedure', 'Procedure Rows', function ($table) {
            $table->text('duration', 'Duration');
            $table->text('step', 'Step');
            $table->textarea('teacher_activity', 'Teacher Activity');
            $table->textarea('pupil_activity', 'Pupil Activity');
        })->help('Add each lesson procedure row: Duration, Step, Teacher Activity, and Pupil Activity.');

        $form->divider('Self Evaluation');
        $form->textarea('self_strengths', 'Strengths')->rows(4);
        $form->textarea('self_areas_improvement', 'Areas of Improvements')->rows(4);
        $form->textarea('self_strategies', 'Strategies')->rows(4);

        $form->divider('Workflow');
        $form->textarea('submission_comment', 'Teacher Submission Comment')
            ->rows(3)
            ->help('Teacher adds a short status note, then clicks Submit.');

        if ($canReview) {
            $form->textarea('supervisor_comment', 'Supervisor Review Comment')
                ->rows(3)
                ->help('Supervisor comment shown to teacher for accountability.');

            $form->radio('status', 'Workflow Status')
                ->options([
                    'Draft' => 'Draft',
                    'Submitted' => 'Submitted',
                    'Changes Requested' => 'Changes Requested',
                    'Approved' => 'Approved',
                ])
                ->default('Draft')
                ->rules('required');
        } else {
            if ($form->isCreating()) {
                $form->hidden('status')->value('Draft');
            }
            $form->display('status', 'Workflow Status');
            $form->hidden('supervisor_comment')->default(optional($editingPlan)->supervisor_comment);
        }

        $form->display('submitted_at', 'Submitted At');
        $form->display('reviewed_at', 'Reviewed At');

        $form->saving(function (Form $form) use ($u, $isPrivileged, $canReview, $editingPlan) {
            if ($form->template_type === 'nursery') {
                if (empty($form->learning_area) || strlen(trim((string) $form->learning_area)) < 2) {
                    throw new \Exception('Learning Area is required for Nursery template.');
                }
                $form->subject_id = null;
            } else {
                if (empty($form->subject_id)) {
                    throw new \Exception('Subject is required for Upper, Lower, and Language templates.');
                }
            }

            if (empty($form->supervisor_id) && !empty($form->teacher_id)) {
                $teacher = User::find($form->teacher_id);
                $form->supervisor_id = $teacher ? ($teacher->supervisor_id ?: $form->teacher_id) : $form->teacher_id;
            }

            if (!$isPrivileged && $editingPlan) {
                $isTeacher = (int) $editingPlan->teacher_id === (int) $u->id;
                $isSupervisor = (int) $editingPlan->supervisor_id === (int) $u->id;

                if (!$isTeacher && !$isSupervisor) {
                    throw new \Exception('You are not allowed to edit this lesson plan.');
                }

                if ($isTeacher && !$isSupervisor) {
                    if (in_array((string) $form->status, ['Approved', 'Changes Requested'])) {
                        throw new \Exception('Teachers cannot set approval states directly. Use Submit workflow.');
                    }
                }
            }

            if (!$canReview && $editingPlan) {
                $form->status = $editingPlan->status;
            } elseif (empty($form->status)) {
                $form->status = 'Draft';
            }

            if ((string) $form->status === 'Submitted' && empty($form->submitted_at)) {
                $form->submitted_at = now();
            }

            if (in_array((string) $form->status, ['Approved', 'Changes Requested']) && $canReview) {
                $form->reviewed_by = $u->id;
                $form->reviewed_at = now();
            }
        });

        return $form;
    }
}
