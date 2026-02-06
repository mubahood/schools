<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Post\ChangeSchemeWorkTopic;
use App\Models\AcademicClass;
use App\Models\SchemWorkItem;
use App\Models\Subject;
use App\Models\Term;
use App\Models\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Str;

class SchemWorkItemController extends AdminController
{
    protected $title = 'Scheme Work Items';

    protected function grid()
    {
        $u = Admin::user();
        $ent = $u->ent;
        $active_term = Term::where(['enterprise_id' => $u->enterprise_id, 'is_active' => 1])->first();
        $primaryColor = $ent->color ?? '#337ab7';

        $grid = new Grid(new SchemWorkItem());

        // Enterprise color CSS
        Admin::css('/css/scheme-work-custom.css');
        Admin::style("
            .ent-primary { background:{$primaryColor}; color:#fff; border-radius:0; padding:2px 8px; font-size:11px; }
            .ent-dark { background:{$primaryColor}dd; color:#fff; border-radius:0; padding:2px 8px; font-size:11px; }
            .ent-light { background:{$primaryColor}99; color:#fff; border-radius:0; padding:2px 8px; font-size:11px; }
            .ent-muted { background:{$primaryColor}55; color:#fff; border-radius:0; padding:2px 8px; font-size:11px; }
        ");

        // Batch actions
        $grid->batchActions(function ($batch) {
            $batch->add(new ChangeSchemeWorkTopic());
        });

        // Filters
        $grid->filter(function ($filter) use ($u, $active_term) {
            $filter->disableIdFilter();

            $filter->equal('term_id', 'Term')
                ->select(
                    Term::where('enterprise_id', $u->enterprise_id)
                        ->orderBy('id', 'desc')
                        ->get()
                        ->pluck('name_text', 'id')
                )->default($active_term ? $active_term->id : null);

            $filter->equal('subject_id', 'Subject')
                ->select(
                    Subject::where('enterprise_id', $u->enterprise_id)
                        ->orderBy('subject_name')
                        ->get()
                        ->mapWithKeys(function ($s) {
                            $c = AcademicClass::find($s->academic_class_id);
                            return [$s->id => $s->subject_name . ' - ' . ($c ? $c->name : '')];
                        })
                );

            $filter->equal('teacher_id', 'Teacher')
                ->select(
                    User::where(['enterprise_id' => $u->enterprise_id, 'user_type' => 'employee'])
                        ->orderBy('first_name')
                        ->get()
                        ->pluck('name', 'id')
                );

            $filter->equal('teacher_status', 'Status')
                ->select(['Pending' => 'Pending', 'Conducted' => 'Conducted', 'Skipped' => 'Skipped']);

            $filter->equal('week', 'Week')
                ->select(array_combine(range(1, 18), array_map(function ($i) { return "Week $i"; }, range(1, 18))));
        });

        // Query scope
        $conds = ['enterprise_id' => $u->enterprise_id];
        if (!$u->isRole('dos')) {
            $conds['teacher_id'] = $u->id;
        }

        $grid->model()->where($conds)->orderBy('term_id', 'desc')->orderBy('week', 'asc');
        $grid->quickSearch('topic', 'competence', 'methods')->placeholder('Search topic, competence...');

        // Columns
        $grid->column('id', 'ID')->sortable()->hide();

        $grid->column('term_id', 'Term')->display(function () {
            return $this->term ? $this->term->name_text : '-';
        })->sortable();

        $grid->column('subject_id', 'Subject')->display(function () {
            if (!$this->subject) return '-';
            $c = AcademicClass::find($this->subject->academic_class_id);
            return $this->subject->subject_name . ($c ? ' — ' . $c->name : '');
        })->sortable();

        $grid->column('week', 'Week')->display(function ($v) {
            return "<span class='ent-primary'>W{$v}</span>";
        })->sortable();

        $grid->column('period', 'Periods')->display(function ($v) {
            return "<span class='ent-dark'>{$v}P</span>";
        })->sortable();

        $grid->column('topic', 'Topic')->display(function ($v) {
            return $v ? '<strong>' . Str::limit($v, 40) . '</strong>' : '<span class="text-muted">—</span>';
        });

        $grid->column('teacher_id', 'Teacher')->display(function () {
            return $this->teacher ? $this->teacher->name : '-';
        })->sortable();

        $grid->column('teacher_status', 'Status')->display(function ($v) {
            $map = [
                'Pending'   => 'ent-light',
                'Conducted' => 'ent-primary',
                'Skipped'   => 'ent-muted',
            ];
            $cls = $map[$v] ?? 'ent-light';
            return "<span class='{$cls}'>{$v}</span>";
        })->sortable();

        $grid->column('teacher_comment', 'Remarks')->display(function ($v) {
            return ($v && strlen($v) > 2) ? Str::limit($v, 50) : '<span class="text-muted">—</span>';
        });

        // Hidden columns (available via column selector)
        $grid->column('competence', 'Competence')->display(function ($v) {
            return ($v && strlen($v) > 2) ? Str::limit($v, 50) : '—';
        })->hide();

        $grid->column('methods', 'Methods')->display(function ($v) {
            return ($v && strlen($v) > 2) ? Str::limit($v, 50) : '—';
        })->hide();

        $grid->column('skills', 'Skills')->display(function ($v) {
            return ($v && strlen($v) > 2) ? Str::limit($v, 50) : '—';
        })->hide();

        $grid->column('suggested_activity', 'Activities')->display(function ($v) {
            return ($v && strlen($v) > 2) ? Str::limit($v, 50) : '—';
        })->hide();

        $grid->column('instructional_material', 'Materials')->display(function ($v) {
            return ($v && strlen($v) > 2) ? Str::limit($v, 50) : '—';
        })->hide();

        $grid->column('references', 'References')->display(function ($v) {
            return ($v && strlen($v) > 2) ? Str::limit($v, 50) : '—';
        })->hide();

        $grid->column('supervisor_status', 'Approval')->display(function ($v) {
            $map = [
                'Pending'  => 'ent-light',
                'Approved' => 'ent-primary',
                'Rejected' => 'ent-muted',
            ];
            $cls = $map[$v] ?? 'ent-light';
            return "<span class='{$cls}'>{$v}</span>";
        })->hide();

        // Export
        $grid->export(function ($export) {
            $export->filename('Scheme_Work_Items_' . date('Y-m-d'));
            $export->except(['actions']);
        });

        return $grid;
    }

    protected function detail($id)
    {
        $show = new Show(SchemWorkItem::findOrFail($id));

        $show->panel()->tools(function ($tools) {
            $tools->disableDelete();
        });

        $show->field('id', 'ID');

        $show->divider('Term & Subject');
        $show->field('term_id', 'Term')->as(function () {
            return $this->term ? $this->term->name_text : '-';
        });
        $show->field('subject_id', 'Subject')->as(function () {
            if (!$this->subject) return '-';
            $c = AcademicClass::find($this->subject->academic_class_id);
            return $this->subject->subject_name . ' (' . ($c ? $c->name : '') . ')';
        });
        $show->field('teacher_id', 'Teacher')->as(function () {
            return $this->teacher ? $this->teacher->name : '-';
        });
        $show->field('supervisor_id', 'Supervisor')->as(function () {
            return $this->supervisor ? $this->supervisor->name : '-';
        });

        $show->divider('Lesson Plan');
        $show->field('week', 'Week');
        $show->field('period', 'Periods');
        $show->field('topic', 'Topic');
        $show->field('supervisor_comment', 'Content / Notes')->unescape()->as(function ($v) { return nl2br($v ?: '-'); });
        $show->field('competence', 'Competence')->unescape()->as(function ($v) { return nl2br($v ?: '-'); });
        $show->field('methods', 'Teaching Methods')->unescape()->as(function ($v) { return nl2br($v ?: '-'); });
        $show->field('skills', 'Skills')->unescape()->as(function ($v) { return nl2br($v ?: '-'); });
        $show->field('suggested_activity', 'Activities')->unescape()->as(function ($v) { return nl2br($v ?: '-'); });
        $show->field('instructional_material', 'Materials')->unescape()->as(function ($v) { return nl2br($v ?: '-'); });
        $show->field('references', 'References')->unescape()->as(function ($v) { return nl2br($v ?: '-'); });

        $show->divider('Status');
        $show->field('teacher_status', 'Lesson Status');
        $show->field('teacher_comment', 'Teacher Remarks')->unescape()->as(function ($v) { return nl2br($v ?: '-'); });
        $show->field('supervisor_status', 'Supervisor Status');

        $show->divider('Timestamps');
        $show->field('created_at', 'Created');
        $show->field('updated_at', 'Updated');

        return $show;
    }

    protected function form()
    {
        $form = new Form(new SchemWorkItem());
        $u = Admin::user();

        // Enterprise & term
        $form->hidden('enterprise_id')->value($u->enterprise_id);
        $active_term = $u->ent->active_term();

        if (!$active_term) {
            admin_error('Error', 'No active term found. Please activate a term first.');
            return $form;
        }

        // Build subject options for this teacher
        $userModel = User::find($u->id);
        $subjects = [];
        foreach ($userModel->my_subjects() as $s) {
            $c = AcademicClass::find($s->academic_class_id);
            $subjects[$s->id] = $s->subject_name . ' — ' . ($c ? $c->name : 'N/A');
        }

        $preSelected = request()->get('subject_id');

        // === Section: Term & Subject ===
        $form->display('_term', 'Term')->default($active_term->name_text);
        $form->hidden('term_id')->value($active_term->id);

        if ($form->isCreating()) {
            $form->hidden('teacher_id')->value($u->id);
            $form->hidden('supervisor_id')->value($userModel->supervisor_id ?? $u->id);
        }

        $subjectField = $form->select('subject_id', 'Subject')
            ->options($subjects)
            ->rules('required');

        if ($form->isCreating() && $preSelected && isset($subjects[$preSelected])) {
            $subjectField->default($preSelected);
        }

        $form->select('week', 'Week')
            ->options(array_combine(range(1, 18), array_map(function ($i) { return "Week $i"; }, range(1, 18))))
            ->rules('required')
            ->default(1);

        $form->select('period', 'Periods')
            ->options(array_combine(range(1, 10), array_map(function ($i) { return "$i Period" . ($i > 1 ? 's' : ''); }, range(1, 10))))
            ->rules('required')
            ->default(1);

        // === Section: Lesson Details ===
        $form->divider('Lesson Details');

        $form->text('topic', 'Topic')
            ->rules('required|min:3')
            ->placeholder('e.g. Introduction to Algebra');

        $form->textarea('supervisor_comment', 'Content / Notes')
            ->rows(3)
            ->placeholder('Key points to cover in this lesson...');

        $form->textarea('competence', 'Competence / Objectives')
            ->rows(3)
            ->placeholder('By the end of this lesson, students should be able to...');

        $form->textarea('methods', 'Teaching Methods')
            ->rows(3)
            ->placeholder('e.g. Discussion, Demonstration, Group Work...');

        $form->textarea('skills', 'Skills')
            ->rows(3)
            ->placeholder('e.g. Critical thinking, Problem solving...');

        $form->textarea('suggested_activity', 'Activities')
            ->rows(3)
            ->placeholder('e.g. Class discussion, practical exercises...');

        $form->textarea('instructional_material', 'Materials')
            ->rows(2)
            ->placeholder('e.g. Textbooks, charts, lab equipment...');

        $form->textarea('references', 'References')
            ->rows(2)
            ->placeholder('e.g. Textbook pages, websites...');

        // === Section: Status ===
        $form->divider('Lesson Status');

        $form->radio('teacher_status', 'Status')
            ->options([
                'Pending'   => 'Pending',
                'Conducted' => 'Conducted',
                'Skipped'   => 'Skipped',
            ])
            ->default('Pending')
            ->rules('required')
            ->when('Conducted', function (Form $form) {
                $form->textarea('teacher_comment', 'Remarks')
                    ->rows(2)
                    ->rules('required|min:5')
                    ->placeholder('How did the lesson go?');
            })
            ->when('Skipped', function (Form $form) {
                $form->textarea('teacher_comment', 'Reason')
                    ->rows(2)
                    ->rules('required|min:3')
                    ->placeholder('Why was this lesson skipped?');
            });

        if ($form->isCreating()) {
            $form->hidden('supervisor_status')->value('Pending');
            $form->hidden('status')->value('Pending');
        }

        // Save callback
        $form->saved(function (Form $form) {
            admin_success('Saved', 'Scheme work item saved.');
            return redirect(admin_url('schems-work-items'));
        });

        return $form;
    }
}
