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
        $u = Admin::user();

        // Enterprise & term
        $form->hidden('enterprise_id')->value($u->enterprise_id);
        $active_term = $u->ent->active_term();

        if (!$active_term) {
            admin_error('Error', 'No active term found. Please activate a term first.');
            return $form;
        }

        // Match edit/create form layout with popup grid alignment.
        Admin::style(<<<'CSS'
            .field_week,
            .field_period,
            .field_teacher_status,
            .field_theme,
            .field_topic,
            .field_content,
            .field_competence_subject,
            .field_competence_language,
            .field_methods,
            .field_life_skills_values,
            .field_suggested_activity,
            .field_instructional_material,
            .field_references {
                float: left;
                padding-right: 8px;
            }

            .field_week,
            .field_period,
            .field_teacher_status {
                width: 33.3333%;
            }

            .field_theme,
            .field_topic,
            .field_content,
            .field_competence_subject,
            .field_competence_language,
            .field_methods,
            .field_life_skills_values,
            .field_suggested_activity,
            .field_instructional_material,
            .field_references {
                width: 50%;
            }

            .field_week,
            .field_theme,
            .field_sub_topic,
            .field_content,
            .field_competence_language,
            .field_life_skills_values,
            .field_instructional_material,
            .field_teacher_comment {
                clear: both;
            }

            .field_topic,
            .field_competence_subject,
            .field_methods,
            .field_suggested_activity,
            .field_references {
                padding-right: 0;
            }

            .field_sub_topic,
            .field_teacher_comment {
                width: 100%;
            }

            @media (max-width: 991px) {
                .field_week,
                .field_period,
                .field_teacher_status,
                .field_theme,
                .field_topic,
                .field_content,
                .field_competence_subject,
                .field_competence_language,
                .field_methods,
                .field_life_skills_values,
                .field_suggested_activity,
                .field_instructional_material,
                .field_references,
                .field_sub_topic,
                .field_teacher_comment {
                    float: none;
                    width: 100%;
                    clear: both;
                    padding-right: 0;
                }
            }
        CSS);

        // Determine if user is privileged (admin, dos, hm)
        $isPrivileged = $u->isRole('admin') || $u->isRole('dos') || $u->isRole('hm');
        $userModel = User::find($u->id);

        // Build subject options — privileged users see ALL subjects, teachers see only theirs
        $subjects = [];
        if ($isPrivileged) {
            $allSubjects = Subject::where('enterprise_id', $u->enterprise_id)
                ->whereHas('academic_class', function ($q) use ($active_term) {
                    $q->where('academic_year_id', $active_term->academic_year_id);
                })
                ->orderBy('subject_name')
                ->get();
            foreach ($allSubjects as $s) {
                $c = AcademicClass::find($s->academic_class_id);
                $subjects[$s->id] = $s->subject_name . ' — ' . ($c ? $c->name : 'N/A');
            }
        } else {
            foreach ($userModel->my_subjects() as $s) {
                $c = AcademicClass::find($s->academic_class_id);
                $subjects[$s->id] = $s->subject_name . ' — ' . ($c ? $c->name : 'N/A');
            }
        }

        $preSelected = request()->get('subject_id');

        // === Section: Term & Subject ===
        $form->display('_term', 'Term')->default($active_term->name_text);
        $form->hidden('term_id')->value($active_term->id);

        if ($form->isCreating()) {
            if ($isPrivileged) {
                // Privileged users can assign any teacher
                $teachers = User::where(['enterprise_id' => $u->enterprise_id, 'user_type' => 'employee'])
                    ->orderBy('first_name')->get()->pluck('name', 'id')->toArray();
                $form->select('teacher_id', 'Teacher')->options($teachers)->default($u->id)->rules('required');
                $form->select('supervisor_id', 'Supervisor')->options($teachers)->default($u->id);
            } else {
                $form->hidden('teacher_id')->value($u->id);
                $form->hidden('supervisor_id')->value($userModel->supervisor_id ?? $u->id);
            }
        } else {
            // On edit, privileged users can change teacher
            if ($isPrivileged) {
                $teachers = User::where(['enterprise_id' => $u->enterprise_id, 'user_type' => 'employee'])
                    ->orderBy('first_name')->get()->pluck('name', 'id')->toArray();
                $form->select('teacher_id', 'Teacher')->options($teachers)->rules('required');
                $form->select('supervisor_id', 'Supervisor')->options($teachers);
            }
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
            ->default(1)
            ->width(4);

        $form->select('period', 'Periods')
            ->options(array_combine(range(1, 10), array_map(function ($i) { return "$i Period" . ($i > 1 ? 's' : ''); }, range(1, 10))))
            ->rules('required')
            ->default(1)
            ->width(4);

        $form->select('teacher_status', 'Status')
            ->options([
                'Pending' => 'Pending',
                'Conducted' => 'Conducted',
                'Skipped' => 'Skipped',
            ])
            ->default('Pending')
            ->rules('required')
            ->width(4);

        // === Section: Lesson Details ===
        $form->divider('Scheme of Work Details');

        $form->text('theme', 'Theme')
            ->rules('required|min:2')
            ->placeholder('e.g. Human Body')
            ->width(6);

        $form->text('topic', 'Topic')
            ->rules('required|min:2')
            ->placeholder('e.g. Muscular-Skeletal System')
            ->width(6);

        $form->text('sub_topic', 'Subtopic')
            ->rules('required|min:2')
            ->placeholder('e.g. Skeleton / Human skeleton');

        $form->textarea('content', 'Content')
            ->rows(3)
            ->rules('required|min:3')
            ->help('Explain the lesson content in steps or bullet points.')
            ->width(6);

        $form->textarea('competence_subject', 'Competence - Subject')
            ->rows(3)
            ->rules('required|min:3')
            ->help('Describe subject-specific competences clearly.')
            ->width(6);

        $form->textarea('competence_language', 'Competence - Language')
            ->rows(3)
            ->rules('required|min:3')
            ->placeholder('Language competences and vocabulary for the learner...')
            ->width(6);

        $form->textarea('methods', 'Methods & Techniques')
            ->rows(3)
            ->rules('required|min:3')
            ->placeholder('e.g. Guided discovery, Question and answer, Explanation, Discussion...')
            ->width(6);

        $form->textarea('life_skills_values', 'Life Skills & Values')
            ->rows(3)
            ->rules('required|min:3')
            ->placeholder('e.g. Effective communication, Logical reasoning, Self-awareness...')
            ->width(6);

        $form->textarea('suggested_activity', 'Suggested Activities')
            ->rows(3)
            ->rules('required|min:3')
            ->placeholder('Activity steps for learners...')
            ->width(6);

        $form->textarea('instructional_material', 'Instructional Materials')
            ->rows(3)
            ->rules('required|min:2')
            ->placeholder('e.g. Insects, charts, models, textbooks...')
            ->width(6);

        $form->textarea('references', 'References')
            ->rows(3)
            ->rules('required|min:2')
            ->placeholder('e.g. P7 curriculum pg. 64, Oxford Sci dictionary...')
            ->width(6);

        $form->textarea('teacher_comment', 'Remarks (optional)')
            ->rows(3)
            ->placeholder('Any remarks for this lesson item...');

        // Keep legacy fields synchronized for backward compatibility with older reports/views.
        $form->hidden('supervisor_comment')->default('');
        $form->hidden('competence')->default('');
        $form->hidden('skills')->default('');

        if ($form->isCreating()) {
            $form->hidden('supervisor_status')->value('Pending');
            $form->hidden('status')->value('Pending');
        }

        // Privileged users can also set supervisor status
        if (!$form->isCreating() && $isPrivileged) {
            $form->divider('Supervisor Review');
            $form->radio('supervisor_status', 'Supervisor Status')
                ->options([
                    'Pending'  => 'Pending',
                    'Approved' => 'Approved',
                    'Rejected' => 'Rejected',
                ])->default('Pending');
        }

        // Save callback
        $form->saving(function (Form $form) {
            $form->supervisor_comment = $form->content;
            $form->competence = $form->competence_subject;
            $form->skills = $form->life_skills_values;
        });

        $form->saved(function (Form $form) {
            admin_success('Saved', 'Scheme work item saved.');
            return redirect(admin_url('schems-work-items'));
        });

        return $form;
    }
}
