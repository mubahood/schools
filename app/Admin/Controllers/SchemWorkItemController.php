<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Post\ChangeSchemeWorkTopic;
use App\Models\SchemWorkItem;
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
    protected $title = 'Scheme Work Items';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new SchemWorkItem());

        //add ChangeSchemeWorkTopic batch action
        $grid->batchActions(function ($batch) {
            $batch->add(new ChangeSchemeWorkTopic());
            $batch->disableDelete();
        });
        $grid->disableBatchActions();
        //$grid filter
        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->equal('term_id', __('Term'))
                ->select(
                    \App\Models\Term::where([
                        'enterprise_id' => Admin::user()->enterprise_id,
                    ])->get()->pluck('name_text', 'id')
                );
            $filter->equal('subject_id', __('Subject'))
                ->select(
                    \App\Models\Subject::where([
                        'enterprise_id' => Admin::user()->enterprise_id,
                    ])->get()->pluck('name', 'id')
                );
            $filter->equal('teacher_id', __('Teacher'))
                ->select(
                    \App\Models\User::where([
                        'enterprise_id' => Admin::user()->enterprise_id,
                        'user_type' => 'employee'
                    ])
                        ->orderBy('first_name', 'asc')
                        ->get()->pluck('name', 'id')
                );

            $filter->equal('teacher_status', __('Teacher Status'))
                ->select([
                    'Pending' => 'Not yet taught',
                    'Conducted' => 'Taught',
                    'Skipped' => 'Skipped'
                ]);

            //date between created
            $filter->between('created_at', __('Date'))->date();
        });


        $u = Admin::user();
        $conds = [
            'enterprise_id' => $u->enterprise_id,
        ];

        //check if is not dos
        if (!$u->isRole('dos')) {
            $conds['teacher_id'] = $u->id;
        }

        $grid->quickSearch(['topic'])->placeholder('Search by topic');
        $grid->model()
            ->where($conds)
            ->orderBy('created_at', 'desc');
        $grid->column('id', __('Id'))->hide();
        $grid->column('created_at', __('Date'))->sortable()
            ->display(function ($created_at) {
                return date('d-m-Y', strtotime($created_at));
            });
        $grid->column('term_id', __('Term'))
            ->display(function ($term_id) {
                if ($this->term == null) {
                    return 'Term not set';
                }
                return 'Term ' . $this->term->name_text;
            })->sortable();
        $grid->column('subject_id', __('Subject'))
            ->display(function ($subject_id) {
                if ($this->subject == null) {
                    return 'Subject not set';
                }
                return $this->subject->name;
            })->sortable();


        $grid->column('week', __('Week'))->sortable()->editable();
        $grid->column('period', __('Period'))->sortable()->editable();
        $grid->column('topic', __('Topic'))->sortable()->editable();
        $grid->column('methods', __('Methods'))->sortable()->editable('textarea');
        $grid->column('skills', __('Skills'))->sortable()->editable('textarea');
        $grid->column('competence', __('Competence'))->sortable()->editable('textarea');
        $grid->column('suggested_activity', __('Suggested Activities'))->sortable()->editable()->hide();
        $grid->column('instructional_material', __('Instructional Material'))
            ->sortable()
            ->editable();
        $grid->column('references', __('References'))
            ->sortable()
            ->editable()
            ->sortable();

        $grid->column('teacher_id', __('Teacher'))
            ->display(function ($teacher_id) {
                if ($this->teacher == null) {
                    return 'Teacher not set';
                }
                return $this->teacher->name;
            })->sortable()->hide();

        $grid->column('teacher_comment', __('Teacher Comment'))
            ->editable()
            ->sortable();
        $grid->column('supervisor_status', __('Supervisor status'))->hide();

        $grid->column('supervisor_id', __('Supervisor'))
            ->display(function ($supervisor_id) {
                if ($this->supervisor == null) {
                    return 'Supervisor not set';
                }
                return $this->supervisor->name;
            })->sortable()->hide();

        $grid->column('supervisor_comment', __('Supervisor comment'))
            ->editable()
            ->sortable()->hide();

        $grid->column('teacher_status', __('Teacher Status'))
            ->label([
                'Pending' => 'warning',
                'Conducted' => 'success',
            ])->sortable()
            ->filter([
                'Pending' => 'Pending',
                'Conducted' => 'Conducted',
            ])->hide();
        $grid->column('status', __('Status'))->hide();

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

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('term_id', __('Term id'));
        $show->field('subject_id', __('Subject id'));
        $show->field('teacher_id', __('Teacher id'));
        $show->field('supervisor_id', __('Supervisor id'));
        $show->field('teacher_status', __('Teacher status'));
        $show->field('teacher_comment', __('Teacher comment'));
        $show->field('supervisor_status', __('Supervisor status'));
        $show->field('supervisor_comment', __('Supervisor comment'));
        $show->field('status', __('Status'));
        $show->field('week', __('Week'));
        $show->field('period', __('Period'));
        $show->field('topic', __('Topic'));
        $show->field('competence', __('Competence'));
        $show->field('methods', __('Methods'));
        $show->field('skills', __('Skills'));
        $show->field('suggested_activity', __('Suggested activity'));
        $show->field('instructional_material', __('Instructional material'));
        $show->field('references', __('References'));

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
        $form->hidden('enterprise_id', __('Enterprise id'))->value($u->enterprise_id);

        $active_term = Admin::user()->ent->active_term();
        $u = User::find(Admin::user()->id);
        $subs = [];
        foreach ($u->my_subjects() as $key => $value) {
            $subs[$value->id] = $value->name;
        }

        $form->select('term_id', __('Due term'))
            ->options(
                \App\Models\Term::where([
                    'enterprise_id' => $u->enterprise_id,
                ])->get()->pluck('name_text', 'id')
            )
            ->default($active_term->id)
            ->rules('required')
            ->readOnly();



        if ($form->isCreating()) {
            $form->hidden('teacher_id', __('Teacher id'))->value($u->id);
            $form->hidden('supervisor_id', __('Teacher id'))->value($u->supervisor_id);
        }

        $form->select('subject_id', __('Select subject'))
            ->options($subs)
            ->rules('required');
        $form->decimal('week', __('Week'))->required();
        $form->decimal('period', __('Period'))->required();

        $form->text('topic', __('Topic'))->rules('required');

        $form->textarea('competence', __('Competence'));
        $form->textarea('methods', __('Methods'));
        $form->textarea('skills', __('Skills'));
        $form->textarea('suggested_activity', __('Suggested activity'));
        $form->textarea('instructional_material', __('Instructional material'));
        $form->textarea('references', __('References'));

        $form->radio('teacher_status', __('Teacher\'s Status'))->default('Pending')
            ->options([
                'Pending' => 'Lessons not yet taught',
                'Conducted' => 'Lessons taught',
            ])
            ->when('Conducted', function (Form $form) {
                $form->text('teacher_comment', __('Teacher Remarks'))->rules('required');
            });
        $form->hidden('supervisor_status', __('Supervisor status'))->default('Pending');
        $form->hidden('supervisor_comment', __('Supervisor comment'));
        $form->hidden('status', __('Status'))->default('Pending');
        return $form;
    }
}
