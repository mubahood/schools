<?php

namespace App\Admin\Controllers;

use App\Models\Project;
use App\Models\Task;
use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Grid\Model;
use Encore\Admin\Show;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request as FacadesRequest;

class TaskController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Task';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Task());



        $grid->filter(function ($filter) {
            $admins = [];
            foreach (Administrator::all() as $key => $v) {
                $admins[$v->id] = $v->name . " - " . $v->id . " - ({$v->username})";
            }
            $filter->equal('assigned_to')->select($admins);
            $filter->between('created_at', 'Created between')->datetime();
            $filter->between('start_date', 'Starts between')->datetime();
            $filter->between('submit_before', 'To be submited between')->datetime();
            $filter->equal('submision_status')->select([
                0 => 'Pending or missed',
                1 => 'Submitted',
                2 => 'Submitted late',
            ]);
            $filter->equal('review_status')->select([
                1 => 'Done',
                2 => 'Partially done',
                0 => 'Not Done',
            ]);

            $projects = [];
            foreach (Project::all() as $key => $v) {
                $projects[$v->id] = $v->name . " - " . $v->short_name;
            }
            $filter->equal('project_id', 'Project')->select($projects);
        });


        $grid->column('id', __('Id'))->sortable();


        $grid->column('created_at', __('Created'))
            ->sortable()
            ->display(function () {
                return Carbon::parse($this->created_at)->toFormattedDateString();
            });

        $grid->column('start_date', __('Starts'))
            ->sortable()
            ->display(function () {
                return Carbon::parse($this->start_date)->toFormattedDateString();
            });

        $grid->column('end_date', __('Ends'))
            ->sortable()
            ->display(function () {
                return Carbon::parse($this->end_date)->toFormattedDateString();
            });

        $grid->column('submit_before', __('Submit before'))
            ->sortable()
            ->display(function () {
                return Carbon::parse($this->submit_before)->diffForHumans();
            });






        $grid->column('assigned_to', __('Assigned to'))
            ->sortable()
            ->display(function () {
                return $this->assignedTo->name;
            });

        $grid->column('assigned_by', __('Assigned by'))
            ->sortable()
            ->display(function () {
                return $this->assignedTo->name;
            });

        $grid->column('submision_status', __('Submision status'))
            ->sortable()
            ->display(function () {
                return $this->get_status();
            });
        $grid->column('title', __('Title'));
        $grid->column('review_status', __('Review status'))->sortable()
            ->display(function () {
                return $this->get_review_status();
            });
        $grid->column('review_comment', __('Review comment'));
        $grid->column('project_id', __('Project'))
            ->sortable()
            ->display(function () {
                return $this->project->short_name;
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
        $show = new Show(Task::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('assigned_to', __('Assigned to'));
        $show->field('assigned_by', __('Assigned by'));
        $show->field('submision_status', __('Submision status'));
        $show->field('body', __('Body'));
        $show->field('review_comment', __('Review comment'));
        $show->field('start_date', __('Start date'));
        $show->field('end_date', __('End date'));
        $show->field('submit_before', __('Submit before'));
        $show->field('review_status', __('Review status'));
        $show->field('value', __('Value'));
        $show->field('category_id', __('Category id'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Task());

        $m = new Task();
        $id = ((int)(FacadesRequest::segment(3)));
        if ($id > 0) {
            $m = Task::findOrFail($id);
        }

        $u = Auth::user();
        $is_manager = false;
        if ($u->isRole('manager')) {
            $is_manager = true;
        }
        $is_manager = true;

        $admins = [];
        $u = Admin::user();
        foreach (Administrator::where([
            'enterprise_id' => $u->enterprise_id
        ])->get() as $key => $v) {
            $admins[$v->id] = $v->name . " - " . $v->id . " - ({$v->username})";
        }

        $projects = [];
        foreach (Project::where([
            'enterprise_id' => $u->enterprise_id
        ])->get() as $key => $v) {
            $projects[$v->id] = $v->name . " - " . $v->short_name;
        }

        $form->text('enterprise_id')
            ->rules('required')
            ->default($u->enterprise_id)
            ->value($u->enterprise_id);


        if ($form->isCreating()) {
            $form->hidden('assigned_by', __('Task title'))
                ->default($u->id)->required();

            if ($is_manager) {
                $form->select('assigned_to', __('Assigned to'))
                    ->options($admins)
                    ->default($u->id)
                    ->value($u->id)
                    ->required();
            } else {
                $form->select('assigned_to', __('Assigned to'))
                    ->options($admins)
                    ->default($u->id)
                    ->readOnly()
                    ->value($u->id)
                    ->required();
            }


            $form->select('project_id', __('Project'))
                ->options($projects)
                ->required();
            $form->text('title', __('Task title'))
                ->help("Enter a precice but complete task title")
                ->required();
            $form->textarea('body', __('Task desscription'))
                ->help("Explain the task")
                ->required();

            $form->datetime('start_date', __('Shedule start date'))->default(date('Y-m-d'))->required();
            $form->datetime('end_date', __('Schedule end date'))->required();
            $form->datetime('submit_before', __('Submit before'));
        } else {

            if ($m->assigned_to == $u->id) {
                $form->textarea('submission_comment', __('Submision comment'))
                    ->required();
                $form->radio('submision_status', __('Submision status'))
                    ->options([1 => 'I submit'])
                    ->required();
            }

            if ($m->assignedTo->supervisor_id == $u->id) {
                $form->textarea('review_comment', __('Review comment'))
                    ->required();
                $form->select('review_status', __('Review status'))
                    ->options([
                        1 => 'Done',
                        2 => 'Partially Done',
                        3 => 'Not done',
                    ])
                    ->required();
            }





            $form->hidden('value', __('Value'))->value(1)->default(1);
            $form->hidden('category_id', __('Value'))->value(1)->default(1);
        }







        return $form;
    }
}
