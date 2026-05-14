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

        // Query scope — admin, dos, hm see all; teachers see only their own
        $isPrivileged = $u->isRole('admin') || $u->isRole('dos') || $u->isRole('hm');
        $grid->model()->where('enterprise_id', $u->enterprise_id);
        if (!$isPrivileged) {
            $grid->model()->where('teacher_id', $u->id);
        }
        $grid->model()->orderBy('term_id', 'desc')->orderBy('week', 'asc');
        $grid->quickSearch('theme', 'topic', 'sub_topic', 'competence_subject', 'competence_language', 'methods')
            ->placeholder('Search theme, topic, subtopic, competences...');

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

        $grid->column('theme', 'Theme')->display(function ($v) {
            return $v ? '<strong>' . Str::limit($v, 30) . '</strong>' : '<span class="text-muted">—</span>';
        });

        $grid->column('topic', 'Topic')->display(function ($v) {
            return $v ? '<strong>' . Str::limit($v, 40) . '</strong>' : '<span class="text-muted">—</span>';
        });

        $grid->column('sub_topic', 'Subtopic')->display(function ($v) {
            return ($v && strlen($v) > 2) ? Str::limit($v, 35) : '<span class="text-muted">—</span>';
        })->hide();

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
        $grid->column('content', 'Content')->display(function ($v) {
            return ($v && strlen($v) > 2) ? Str::limit($v, 50) : '—';
        })->hide();

        $grid->column('competence_subject', 'Competence (Subject)')->display(function ($v) {
            return ($v && strlen($v) > 2) ? Str::limit($v, 50) : '—';
        })->hide();

        $grid->column('competence_language', 'Competence (Language)')->display(function ($v) {
            return ($v && strlen($v) > 2) ? Str::limit($v, 50) : '—';
        })->hide();

        $grid->column('competence', 'Competence (Legacy)')->display(function ($v) {
            return ($v && strlen($v) > 2) ? Str::limit($v, 50) : '—';
        })->hide();

        $grid->column('methods', 'Methods')->display(function ($v) {
            return ($v && strlen($v) > 2) ? Str::limit($v, 50) : '—';
        })->hide();

        $grid->column('life_skills_values', 'Life Skills & Values')->display(function ($v) {
            return ($v && strlen($v) > 2) ? Str::limit($v, 50) : '—';
        })->hide();

        $grid->column('skills', 'Skills (Legacy)')->display(function ($v) {
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
        $show->field('theme', 'Theme');
        $show->field('topic', 'Topic');
        $show->field('sub_topic', 'Subtopic');
        $show->field('content', 'Content')->unescape()->as(function ($v) {
            return nl2br($v ?: ($this->supervisor_comment ?: '-'));
        });
        $show->field('competence_subject', 'Competence (Subject)')->unescape()->as(function ($v) {
            return nl2br($v ?: ($this->competence ?: '-'));
        });
        $show->field('competence_language', 'Competence (Language)')->unescape()->as(function ($v) {
            return nl2br($v ?: '-');
        });
        $show->field('methods', 'Methods & Techniques')->unescape()->as(function ($v) { return nl2br($v ?: '-'); });
        $show->field('life_skills_values', 'Life Skills & Values')->unescape()->as(function ($v) {
            return nl2br($v ?: ($this->skills ?: '-'));
        });
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
        $u    = Admin::user();

        $form->hidden('enterprise_id')->value($u->enterprise_id);

        $active_term = $u->ent->active_term();
        if (!$active_term) {
            admin_error('Error', 'No active term found. Please activate a term first.');
            return $form;
        }

        $isPrivileged = $u->isRole('admin') || $u->isRole('dos') || $u->isRole('hm');
        $userModel    = User::find($u->id);

        // ── Build subject list ──────────────────────────────────────────────
        $subjectRows = $isPrivileged
            ? Subject::where('enterprise_id', $u->enterprise_id)
                ->whereHas('academic_class', fn ($q) => $q->where('academic_year_id', $active_term->academic_year_id))
                ->orderBy('subject_name')->get()
            : $userModel->my_subjects();

        $subjects    = [];
        $templateMap = []; // subjectId => scheme_template
        foreach ($subjectRows as $s) {
            $c = AcademicClass::find($s->academic_class_id);
            $subjects[$s->id]    = $s->subject_name . ' — ' . ($c ? $c->name : 'N/A');
            $templateMap[$s->id] = $s->scheme_template ?: 'auto';
        }

        // Determine initial template (needed for edit mode or pre-selected create)
        $preSelected     = (int) request()->get('subject_id');
        $initialTemplate = 'auto';
        if (!$form->isCreating()) {
            $editId      = request()->route('schems_work_item');
            $editItem    = $editId ? SchemWorkItem::find($editId) : null;
            if ($editItem && $editItem->subject_id) {
                $initialTemplate = $templateMap[$editItem->subject_id] ?? 'auto';
            }
        } elseif ($preSelected && isset($templateMap[$preSelected])) {
            $initialTemplate = $templateMap[$preSelected];
        }
        $isLowerPrimary  = in_array($initialTemplate, ['auto', 'generic']);

        // ── Inject JS for dynamic template switching ────────────────────────
        $tmJson = json_encode($templateMap, JSON_UNESCAPED_UNICODE);
        $initTm = json_encode($initialTemplate);
        Admin::script(<<<JS
(function () {
    var TM = {$tmJson};
    function isLower(t) { return !t || t === 'auto' || t === 'generic'; }

    function applyTemplate(template) {
        var lower = isLower(template);

        /* competence_language row */
        var langRow = document.querySelector('.field_competence_language');
        if (langRow) {
            langRow.style.display = lower ? 'none' : '';
            var langTA = langRow.querySelector('textarea');
            if (langTA && lower) langTA.value = '';
        }

        /* competence_subject label */
        var subjectLbl = document.querySelector('.field_competence_subject label');
        if (subjectLbl) {
            subjectLbl.textContent = lower ? 'Competences' : 'Competence – Subject';
        }

        /* For lower primary: competence_subject takes full width; upper: half */
        var subjectRow = document.querySelector('.field_competence_subject');
        if (subjectRow) {
            subjectRow.style.width = lower ? '100%' : '';
            subjectRow.style.clear = lower ? 'both' : '';
        }
    }

    /* Apply on page load */
    applyTemplate({$initTm});

    /* React to subject select changes (plain + select2) */
    function onSubjectChange(val) { applyTemplate(TM[val] || 'auto'); }

    document.addEventListener('change', function (e) {
        if (e.target && e.target.name === 'subject_id') onSubjectChange(e.target.value);
    });
    if (window.jQuery) {
        jQuery(document).on('change', 'select[name="subject_id"]', function () { onSubjectChange(this.value); });
    }
})();
JS);

        // ── Layout CSS ──────────────────────────────────────────────────────
        Admin::style(<<<'CSS'
            .field_week, .field_period, .field_teacher_status { float:left; width:33.3333%; padding-right:8px; }
            .field_theme, .field_topic, .field_content, .field_competence_subject,
            .field_competence_language, .field_methods, .field_life_skills_values,
            .field_suggested_activity, .field_instructional_material, .field_references {
                float:left; width:50%; padding-right:8px;
            }
            .field_week, .field_theme, .field_sub_topic, .field_content,
            .field_competence_language, .field_life_skills_values,
            .field_instructional_material, .field_teacher_comment { clear:both; }
            .field_topic, .field_competence_subject, .field_methods,
            .field_suggested_activity, .field_references { padding-right:0; }
            .field_sub_topic, .field_teacher_comment { width:100%; float:left; }
            @media (max-width:991px) {
                .field_week, .field_period, .field_teacher_status, .field_theme, .field_topic,
                .field_content, .field_competence_subject, .field_competence_language,
                .field_methods, .field_life_skills_values, .field_suggested_activity,
                .field_instructional_material, .field_references, .field_sub_topic, .field_teacher_comment {
                    float:none; width:100%; clear:both; padding-right:0;
                }
            }
        CSS);

        // ── Term & FK fields (required) ─────────────────────────────────────
        $form->display('_term', 'Term')->default($active_term->name_text);
        $form->hidden('term_id')->value($active_term->id);

        $teachers = User::where(['enterprise_id' => $u->enterprise_id, 'user_type' => 'employee'])
            ->orderBy('first_name')->get()->pluck('name', 'id')->toArray();

        if ($form->isCreating()) {
            if ($isPrivileged) {
                $form->select('teacher_id', 'Teacher')->options($teachers)->default($u->id)->rules('required');
                $form->select('supervisor_id', 'Supervisor')->options($teachers)->default($u->id);
            } else {
                $form->hidden('teacher_id')->value($u->id);
                $form->hidden('supervisor_id')->value($userModel->supervisor_id ?? $u->id);
            }
        } else {
            if ($isPrivileged) {
                $form->select('teacher_id', 'Teacher')->options($teachers)->rules('required');
                $form->select('supervisor_id', 'Supervisor')->options($teachers);
            }
        }

        $subjectField = $form->select('subject_id', 'Subject')->options($subjects)->rules('required');
        if ($form->isCreating() && $preSelected && isset($subjects[$preSelected])) {
            $subjectField->default($preSelected);
        }

        $form->select('week', 'Week')
            ->options(array_combine(range(1, 18), array_map(fn ($i) => "Week $i", range(1, 18))))
            ->rules('required')->default(1)->width(4);

        $form->select('period', 'Periods')
            ->options(array_combine(range(1, 10), array_map(fn ($i) => "$i Period" . ($i > 1 ? 's' : ''), range(1, 10))))
            ->rules('required')->default(1)->width(4);

        $form->select('teacher_status', 'Status')
            ->options(['Pending' => 'Pending', 'Conducted' => 'Conducted', 'Skipped' => 'Skipped'])
            ->default('Pending')->rules('required')->width(4);

        // ── Scheme of Work content (all optional) ───────────────────────────
        $form->divider('Scheme of Work Details');

        $form->text('theme', 'Theme')
            ->placeholder('e.g. Human Body')->width(6);

        $form->text('topic', 'Topic')
            ->placeholder('e.g. Muscular-Skeletal System')->width(6);

        $form->text('sub_topic', 'Subtopic')
            ->placeholder('e.g. Skeleton / Human skeleton');

        $form->textarea('content', 'Content')
            ->rows(4)->help('Lesson content in steps or bullet points.')->width(6);

        // Label is updated by JS for lower primary ('Competences')
        $form->textarea('competence_subject', 'Competence – Subject')
            ->rows(4)->help('Subject-specific competences.')->width(6);

        // Hidden for lower primary via JS
        $form->textarea('competence_language', 'Competence – Language')
            ->rows(4)->placeholder('Language competences for the learner…')->width(6);

        $form->textarea('methods', 'Methods & Techniques')
            ->rows(4)->placeholder('e.g. Guided discovery, Question and answer…')->width(6);

        $form->textarea('life_skills_values', 'Life Skills & Values')
            ->rows(4)->placeholder('e.g. Effective communication, Self-awareness…')->width(6);

        $form->textarea('suggested_activity', 'Suggested Activities')
            ->rows(4)->placeholder('Activity steps for learners…')->width(6);

        $form->textarea('instructional_material', 'Instructional Materials')
            ->rows(4)->placeholder('e.g. Charts, models, textbooks…')->width(6);

        $form->textarea('references', 'References')
            ->rows(4)->placeholder('e.g. P4 curriculum pg. 64, Teacher guide…')->width(6);

        $form->textarea('teacher_comment', 'Remarks')
            ->rows(3)->placeholder('Any remarks for this lesson item…');

        // Legacy sync fields (kept for old views/reports)
        $form->hidden('supervisor_comment')->default('');
        $form->hidden('competence')->default('');
        $form->hidden('skills')->default('');

        if ($form->isCreating()) {
            $form->hidden('supervisor_status')->value('Pending');
            $form->hidden('status')->value('Pending');
        }

        if (!$form->isCreating() && $isPrivileged) {
            $form->divider('Supervisor Review');
            $form->radio('supervisor_status', 'Supervisor Status')
                ->options(['Pending' => 'Pending', 'Approved' => 'Approved', 'Rejected' => 'Rejected'])
                ->default('Pending');
        }

        // ── Saving callback ─────────────────────────────────────────────────
        $form->saving(function (Form $form) use ($templateMap) {
            // Sync legacy fields
            $form->supervisor_comment = $form->content;
            $form->competence         = $form->competence_subject;
            $form->skills             = $form->life_skills_values;

            // For lower primary subjects, competence_language must be empty
            $subjectId = (int) $form->subject_id;
            $tmpl      = $templateMap[$subjectId] ?? 'auto';
            if (in_array($tmpl, ['auto', 'generic'])) {
                $form->competence_language = '';
            }
        });

        $form->saved(function (Form $form) {
            admin_success('Saved', 'Scheme work item saved.');
            return redirect(admin_url('schems-work-items'));
        });

        return $form;
    }
}
