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

class SchemWorkItemController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Scheme Work Items (Records)';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $u = Admin::user();
        
        // Get active term
        $active_term = Term::where([
            'enterprise_id' => $u->enterprise_id,
            'is_active' => 1
        ])->first();

        $grid = new Grid(new SchemWorkItem());

        // Add batch actions
        $grid->batchActions(function ($batch) {
            $batch->add(new ChangeSchemeWorkTopic());
        });

        // Header with helpful information
        if ($active_term) {
            $grid->header(function () use ($active_term) {
                return '<div class="alert alert-info">
                    <i class="fa fa-info-circle"></i> 
                    <strong>Active Term:</strong> ' . $active_term->name_text . ' | 
                    <strong>Academic Year:</strong> ' . $active_term->academic_year->name . '
                </div>';
            });
        }

        // Enhanced filter
        $grid->filter(function ($filter) use ($u, $active_term) {
            $filter->disableIdFilter();
            
            // Filter by term
            $filter->equal('term_id', __('Term'))
                ->select(Term::where([
                    'enterprise_id' => $u->enterprise_id,
                ])->orderBy('id', 'desc')->get()->pluck('name_text', 'id'))
                ->default($active_term ? $active_term->id : null);
            
            // Filter by subject
            $filter->equal('subject_id', __('Subject'))
                ->select(Subject::where([
                    'enterprise_id' => $u->enterprise_id,
                ])->orderBy('subject_name')->get()->pluck('subject_name', 'id'));
            
            // Filter by class
            $filter->where(function ($query) {
                $classId = $this->input;
                if ($classId) {
                    $query->whereHas('subject', function ($q) use ($classId) {
                        $q->where('academic_class_id', $classId);
                    });
                }
            }, 'Class')->select(AcademicClass::where([
                'enterprise_id' => $u->enterprise_id
            ])->orderBy('name')->get()->pluck('name', 'id'));
            
            // Filter by teacher
            $filter->equal('teacher_id', __('Teacher'))
                ->select(User::where([
                    'enterprise_id' => $u->enterprise_id,
                    'user_type' => 'employee'
                ])->orderBy('first_name', 'asc')->get()->pluck('name', 'id'));

            // Filter by teacher status
            $filter->equal('teacher_status', __('Lesson Status'))
                ->select([
                    'Pending' => 'Pending (Not Yet Taught)',
                    'Conducted' => 'Conducted (Successfully Taught)',
                    'Skipped' => 'Skipped'
                ]);

            // Filter by week
            $filter->between('week', 'Week Range');

            // Date range filter
            $filter->between('created_at', __('Date Created'))->date();
        });

        // Set query conditions
        $conds = ['enterprise_id' => $u->enterprise_id];

        // Non-DOS users only see their own items
        if (!$u->isRole('dos')) {
            $conds['teacher_id'] = $u->id;
        }

        // Quick search
        $grid->quickSearch(['topic', 'competence', 'methods'])->placeholder('Search by topic, competence, or methods');

        // Model query with proper ordering
        $grid->model()
            ->where($conds)
            ->orderBy('term_id', 'desc')
            ->orderBy('week', 'asc')
            ->orderBy('created_at', 'desc');

        // Grid columns
        $grid->column('id', __('ID'))->sortable()->hide();
        
        $grid->column('created_at', __('Date Created'))
            ->display(function ($created_at) {
                return '<small>' . date('d M Y', strtotime($created_at)) . '</small>';
            })->sortable()->width(100);

        $grid->column('term_id', __('Term'))
            ->display(function ($term_id) {
                if ($this->term == null) {
                    return '<span class="label label-default">Not Set</span>';
                }
                return '<span class="label label-info">Term ' . $this->term->name_text . '</span>';
            })->sortable()->width(100);

        $grid->column('subject_id', __('Subject & Class'))
            ->display(function ($subject_id) {
                if ($this->subject == null) {
                    return '<span class="text-muted">Not Set</span>';
                }
                $class = AcademicClass::find($this->subject->academic_class_id);
                $className = $class ? $class->name : 'N/A';
                return '<strong>' . $this->subject->subject_name . '</strong><br>
                        <small class="text-muted"><i class="fa fa-graduation-cap"></i> ' . $className . '</small>';
            })->sortable();

        $grid->column('week', __('Week'))->sortable()->editable('select', [
            1 => 'Week 1', 2 => 'Week 2', 3 => 'Week 3', 4 => 'Week 4', 
            5 => 'Week 5', 6 => 'Week 6', 7 => 'Week 7', 8 => 'Week 8',
            9 => 'Week 9', 10 => 'Week 10', 11 => 'Week 11', 12 => 'Week 12',
            13 => 'Week 13', 14 => 'Week 14', 15 => 'Week 15', 16 => 'Week 16',
            17 => 'Week 17', 18 => 'Week 18'
        ])->width(80)->display(function ($week) {
            return '<span class="badge bg-blue">W' . $week . '</span>';
        });

        $grid->column('period', __('Periods'))->sortable()->editable('select', [
            1 => '1 Period', 2 => '2 Periods', 3 => '3 Periods', 4 => '4 Periods',
            5 => '5 Periods', 6 => '6 Periods', 7 => '7 Periods', 8 => '8 Periods'
        ])->width(80)->display(function ($period) {
            return '<span class="badge bg-purple">' . $period . 'P</span>';
        });

        $grid->column('topic', __('Topic'))
            ->editable()
            ->display(function ($topic) {
                return '<strong>' . ($topic ?: '<span class="text-muted">No topic</span>') . '</strong>';
            });

        $grid->column('competence', __('Competence'))
            ->editable('textarea')
            ->display(function ($competence) {
                if (!$competence || strlen($competence) < 3) {
                    return '<span class="text-muted">-</span>';
                }
                return str_limit($competence, 50);
            })->hide();

        $grid->column('methods', __('Teaching Methods'))
            ->editable('textarea')
            ->display(function ($methods) {
                if (!$methods || strlen($methods) < 3) {
                    return '<span class="text-muted">-</span>';
                }
                return str_limit($methods, 50);
            })->hide();

        $grid->column('skills', __('Skills'))
            ->editable('textarea')
            ->display(function ($skills) {
                if (!$skills || strlen($skills) < 3) {
                    return '<span class="text-muted">-</span>';
                }
                return str_limit($skills, 50);
            })->hide();

        $grid->column('suggested_activity', __('Suggested Activity'))
            ->editable('textarea')
            ->display(function ($activity) {
                if (!$activity || strlen($activity) < 3) {
                    return '<span class="text-muted">-</span>';
                }
                return str_limit($activity, 50);
            })->hide();

        $grid->column('instructional_material', __('Materials'))
            ->editable('textarea')
            ->display(function ($material) {
                if (!$material || strlen($material) < 3) {
                    return '<span class="text-muted">-</span>';
                }
                return str_limit($material, 50);
            })->hide();

        $grid->column('references', __('References'))
            ->editable('textarea')
            ->display(function ($references) {
                if (!$references || strlen($references) < 3) {
                    return '<span class="text-muted">-</span>';
                }
                return str_limit($references, 50);
            })->hide();

        $grid->column('teacher_id', __('Teacher'))
            ->display(function ($teacher_id) {
                if ($this->teacher == null) {
                    return '<span class="text-muted">Not assigned</span>';
                }
                return '<i class="fa fa-user"></i> ' . $this->teacher->name;
            })->sortable();

        $grid->column('teacher_status', __('Status'))
            ->display(function ($status) {
                $badges = [
                    'Pending' => '<span class="label label-warning"><i class="fa fa-clock-o"></i> Pending</span>',
                    'Conducted' => '<span class="label label-success"><i class="fa fa-check"></i> Conducted</span>',
                    'Skipped' => '<span class="label label-danger"><i class="fa fa-times"></i> Skipped</span>',
                ];
                return $badges[$status] ?? $status;
            })
            ->filter([
                'Pending' => 'Pending',
                'Conducted' => 'Conducted',
                'Skipped' => 'Skipped'
            ])->sortable();

        $grid->column('teacher_comment', __('Teacher\'s Remarks'))
            ->editable('textarea')
            ->display(function ($comment) {
                if (!$comment || strlen($comment) < 3) {
                    return '<span class="text-muted">-</span>';
                }
                return str_limit($comment, 60);
            });

        $grid->column('supervisor_comment', __('Content/Notes'))
            ->editable('textarea')
            ->display(function ($comment) {
                if (!$comment || strlen($comment) < 3) {
                    return '<span class="text-muted">-</span>';
                }
                return str_limit($comment, 60);
            })->hide();

        $grid->column('supervisor_id', __('Supervisor'))
            ->display(function ($supervisor_id) {
                if ($this->supervisor == null) {
                    return '<span class="text-muted">Not assigned</span>';
                }
                return '<i class="fa fa-user-secret"></i> ' . $this->supervisor->name;
            })->sortable()->hide();

        $grid->column('supervisor_status', __('Supervisor Status'))
            ->label([
                'Pending' => 'warning',
                'Approved' => 'success',
                'Rejected' => 'danger'
            ])->hide();

        // Enable export
        $grid->export(function ($export) {
            $export->filename('Scheme_Work_Items_' . date('Y-m-d'));
            $export->except(['actions']);
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
        $show = new Show(SchemWorkItem::findOrFail($id));

        $show->panel()->tools(function ($tools) {
            $tools->disableDelete();
        });

        $show->field('id', __('ID'));
        
        $show->divider();
        $show->field('term_id', __('Term'))->as(function ($term_id) {
            return $this->term ? $this->term->name_text : 'Not Set';
        });
        
        $show->field('subject_id', __('Subject'))->as(function ($subject_id) {
            if (!$this->subject) return 'Not Set';
            $class = AcademicClass::find($this->subject->academic_class_id);
            return $this->subject->subject_name . ' (' . ($class ? $class->name : 'N/A') . ')';
        });

        $show->field('teacher_id', __('Teacher'))->as(function ($teacher_id) {
            return $this->teacher ? $this->teacher->name : 'Not Assigned';
        });

        $show->field('supervisor_id', __('Supervisor'))->as(function ($supervisor_id) {
            return $this->supervisor ? $this->supervisor->name : 'Not Assigned';
        });

        $show->divider();
        $show->field('week', __('Week Number'));
        $show->field('period', __('Number of Periods'));
        $show->field('topic', __('Lesson Topic'));
        
        $show->divider();
        $show->field('supervisor_comment', __('Content/Learning Content'))->unescape()->as(function ($content) {
            return nl2br($content ?: '-');
        });
        
        $show->field('competence', __('Competence/Learning Objectives'))->unescape()->as(function ($comp) {
            return nl2br($comp ?: '-');
        });
        
        $show->field('methods', __('Teaching Methods'))->unescape()->as(function ($methods) {
            return nl2br($methods ?: '-');
        });
        
        $show->field('skills', __('Skills to Develop'))->unescape()->as(function ($skills) {
            return nl2br($skills ?: '-');
        });
        
        $show->field('suggested_activity', __('Suggested Learning Activities'))->unescape()->as(function ($activity) {
            return nl2br($activity ?: '-');
        });
        
        $show->field('instructional_material', __('Instructional Materials/Resources'))->unescape()->as(function ($material) {
            return nl2br($material ?: '-');
        });
        
        $show->field('references', __('References'))->unescape()->as(function ($refs) {
            return nl2br($refs ?: '-');
        });

        $show->divider();
        $show->field('teacher_status', __('Lesson Status'))->badge([
            'Pending' => 'warning',
            'Conducted' => 'success',
            'Skipped' => 'danger'
        ]);
        
        $show->field('teacher_comment', __('Teacher\'s Remarks'))->unescape()->as(function ($comment) {
            return nl2br($comment ?: '-');
        });

        $show->field('supervisor_status', __('Supervisor Approval Status'))->badge([
            'Pending' => 'warning',
            'Approved' => 'success',
            'Rejected' => 'danger'
        ]);

        $show->divider();
        $show->field('created_at', __('Created At'));
        $show->field('updated_at', __('Last Updated'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new SchemWorkItem());
        $u = Admin::user();
        
        // Hidden fields
        $form->hidden('enterprise_id')->value($u->enterprise_id);

        // Get active term
        $active_term = $u->ent->active_term();
        
        if (!$active_term) {
            admin_error('Error', 'No active term found. Please activate a term first.');
            return redirect(admin_url('terms'));
        }

        // Get user's subjects
        $userModel = User::find($u->id);
        $subs = [];
        foreach ($userModel->my_subjects() as $value) {
            $class = AcademicClass::find($value->academic_class_id);
            $className = $class ? $class->name : 'N/A';
            $subs[$value->id] = $value->subject_name . ' (' . $className . ')';
        }

        if (empty($subs)) {
            admin_warning('Warning', 'You have no assigned subjects. Please contact administrator to assign subjects to you.');
        }

        // Display current term
        $form->display('current_term', 'Current Term')->default($active_term->name_text);

        // Hidden term field
        $form->hidden('term_id')->value($active_term->id);

        // Auto-assign teacher and supervisor on creation
        if ($form->isCreating()) {
            $form->hidden('teacher_id')->value($u->id);
            $form->hidden('supervisor_id')->value($userModel->supervisor_id ?? $u->id);
        }

        $form->divider('Lesson Planning Details');

        // Subject selection
        $form->select('subject_id', __('Select Subject'))
            ->options($subs)
            ->rules('required')
            ->help('Select the subject for this scheme work item');

        // Week number
        $form->select('week', __('Week Number'))
            ->options(array_combine(range(1, 18), array_map(function($i) { return "Week $i"; }, range(1, 18))))
            ->rules('required')
            ->default(1)
            ->help('Select the week number for this lesson');

        // Number of periods
        $form->select('period', __('Number of Periods'))
            ->options(array_combine(range(1, 10), array_map(function($i) { return "$i Period" . ($i > 1 ? 's' : ''); }, range(1, 10))))
            ->rules('required')
            ->default(1)
            ->help('How many periods will this lesson take?');

        // Topic
        $form->text('topic', __('Lesson Topic'))
            ->rules('required|min:3')
            ->help('Enter the main topic/title of the lesson')
            ->placeholder('E.g., Introduction to Algebra, Photosynthesis, etc.');

        $form->divider('Learning Content & Objectives');

        // Content (stored in supervisor_comment field)
        $form->textarea('supervisor_comment', __('Learning Content/Description'))
            ->rows(4)
            ->help('Describe what students will learn in this lesson')
            ->placeholder('Enter the main content and key points to be covered...');

        // Competence
        $form->textarea('competence', __('Competence/Learning Objectives'))
            ->rows(4)
            ->help('What competencies or learning objectives should students achieve?')
            ->placeholder('By the end of this lesson, students should be able to...');

        $form->divider('Teaching Approach');

        // Methods
        $form->textarea('methods', __('Teaching Methods'))
            ->rows(4)
            ->help('List the teaching methods you will use')
            ->placeholder('E.g., Explanation, Discussion, Demonstration, Group Work, etc.');

        // Skills
        $form->textarea('skills', __('Skills to Develop'))
            ->rows(4)
            ->help('What skills will students develop?')
            ->placeholder('E.g., Critical thinking, Problem solving, Communication, etc.');

        // Suggested activities
        $form->textarea('suggested_activity', __('Suggested Learning Activities'))
            ->rows(4)
            ->help('What activities will students do?')
            ->placeholder('E.g., Class discussions, practical exercises, homework assignments, etc.');

        $form->divider('Resources & References');

        // Instructional materials
        $form->textarea('instructional_material', __('Instructional Materials/Resources'))
            ->rows(3)
            ->help('What materials or resources are needed?')
            ->placeholder('E.g., Textbooks, charts, models, laboratory equipment, etc.');

        // References
        $form->textarea('references', __('References'))
            ->rows(3)
            ->help('List reference materials')
            ->placeholder('E.g., Textbook pages, websites, articles, etc.');

        $form->divider('Lesson Status');

        // Teacher status
        $form->radio('teacher_status', __('Lesson Status'))
            ->options([
                'Pending' => 'Pending (Not Yet Taught)',
                'Conducted' => 'Conducted (Successfully Taught)',
                'Skipped' => 'Skipped (Will Not Teach)'
            ])
            ->default('Pending')
            ->rules('required')
            ->when('Conducted', function (Form $form) {
                $form->textarea('teacher_comment', __('Teacher\'s Remarks'))
                    ->rules('required|min:10')
                    ->rows(3)
                    ->help('Provide feedback on how the lesson went')
                    ->placeholder('Describe how the lesson went, challenges faced, student responses, etc.');
            })
            ->when('Skipped', function (Form $form) {
                $form->textarea('teacher_comment', __('Reason for Skipping'))
                    ->rules('required|min:5')
                    ->rows(2)
                    ->help('Explain why this lesson was skipped')
                    ->placeholder('Enter reason...');
            })
            ->help('Select the current status of this lesson');

        // Hidden supervisor fields (auto-set by model)
        if ($form->isCreating()) {
            $form->hidden('supervisor_status')->value('Pending');
            $form->hidden('status')->value('Pending');
        }

        // Saving hooks
        $form->saving(function (Form $form) {
            $u = Admin::user();
            
            // Soft check for duplicate (warning only, not blocking)
            if ($form->isCreating()) {
                $duplicate = SchemWorkItem::where([
                    'enterprise_id' => $u->enterprise_id,
                    'term_id' => $form->term_id,
                    'subject_id' => $form->subject_id,
                    'teacher_id' => $form->teacher_id ?? $u->id,
                    'week' => $form->week,
                ])->first();
                
                if ($duplicate) {
                    $subject = Subject::find($form->subject_id);
                    $subjectName = $subject ? $subject->subject_name : 'this subject';
                    
                    admin_warning(
                        'Notice', 
                        "You already have a scheme work item for <strong>{$subjectName}</strong> in <strong>Week {$form->week}</strong>. " .
                        "<a href='" . admin_url("schems-work-items/{$duplicate->id}/edit") . "'>View existing item</a>"
                    );
                }
            }
            
            // Ensure references are properly set
            if ($form->references) {
                $form->model()->references = $form->references;
            }
        });

        $form->saved(function (Form $form) {
            admin_success('Success', 'Scheme work item saved successfully!');
            return redirect(admin_url('schems-work-items'));
        });

        return $form;
    }
}
