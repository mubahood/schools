<?php

namespace App\Admin\Controllers;

use App\Models\AcademicClass;
use App\Models\Participant;
use App\Models\Service;
use App\Models\Session;
use App\Models\Subject;
use App\Models\TheologyClass;
use App\Models\TheologyStream;
use App\Models\User;
use App\Models\Utils;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Http\Request;

class SessionController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Roll-calls';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {


        $grid = new Grid(new Session());
        $grid->quickSearch('target_text')
            ->placeholder('Search by target');
        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $u = Admin::user();

            //filter by secular stream (learn from form)
            $streams = [];
            foreach (
                \App\Models\AcademicClassSctream::where([
                    'enterprise_id' => $u->enterprise_id,
                ])
                    ->orderBy('id', 'desc')
                    ->get() as $key => $s
            ) {
                $streams[$s->id] = $s->name_text;
            }
            $filter->equal('secular_stream_id', __('Filter by Secular Stream'))->select(
                $streams
            );

            //filter by theology stream (learn from form)
            $theology_streams = [];
            foreach (
                TheologyStream::where([
                    'enterprise_id' => $u->enterprise_id,
                ])
                    ->orderBy('id', 'desc')
                    ->get() as $key => $s
            ) {
                $theology_streams[$s->id] = $s->name_text;
            }
            $filter->equal('theology_stream_id', __('Filter by Theology Stream'))->select(
                $theology_streams
            );


            // Filter by service
            $services = [];
            foreach (
                Service::where([
                    'enterprise_id' => $u->enterprise_id,
                ])->get() as $key => $s
            ) {
                $services[$s->id] = "#" . $s->id . " " . $s->name;
            }
            $filter->equal('service_id', __('Filter by Service'))->select(
                $services
            );

            // Filter by session type
            $filter->equal('type', __('Filter by Session Type'))->select([
                'STUDENT_REPORT' => 'Student Report at School',
                'STUDENT_LEAVE' => 'Student Leave School',
                'STUDENT_MEAL' => 'Student Meals Session',
                'CLASS_ATTENDANCE' => 'Class Attendance',
                'THEOLOGY_ATTENDANCE' => 'Theology Class Attendance',
                'ACTIVITY_ATTENDANCE' => 'Activity Participation',
            ]);


            // Filter by due date range
            $filter->between('due_date', __('Filter by Due Date'))->datetime();

            // Filter by creation date range
            $filter->between('created_at', __('Filter by Created At'))->datetime();
        });

        $u = Admin::user();
        $activeSession = Session::where([
            'enterprise_id' => $u->enterprise_id,
            'administrator_id' => $u->id,
        ])->first();
        if ($activeSession  != null) {

            // return redirect(url("roll-calling?roll_call_session_id={$activeSession->id}"));
            //alert
            // Admin::script("window.onload = function() { window.location.href = '" . admin_url("sessions/{$activeSession->id}/edit") . "'; }");
            // admin_error('Active Session', 'You have an active session, please complete it before creating a new one.'); 
        }


        $grid->export(function ($export) {

            $export->filename('roll-calls.csv');
            $export->except(['is_open','session_decision']);
        });

        // $grid->disableActions();
        $grid->disableBatchActions();
        $grid->model()->where([
            'enterprise_id' => Admin::user()->enterprise_id,
        ])
            ->orderBy('id', 'Desc');

        $grid->column('id', __('Id'))->sortable();
        $grid->column('created_at', __('Created'))
            ->display(function () {
                return Utils::my_date($this->created_at);
            })
            ->hide()
            ->sortable();

        $grid->column('due_date', __('Date'))
            ->display(function () {
                return Utils::my_date($this->due_date);
            })
            ->sortable();
        $grid->column('type', __('Session Type'))->sortable();



        $grid->column('term_id', __('Term'))
            ->display(function () {
                return "Term " . $this->term->name_text;
            })
            ->sortable()
            ->hide();

        $grid->column('target_text', __('Target'))
            ->sortable();


        $grid->column('service_id', __('Service'))
            ->display(function () {
                if ($this->service == null) {
                    return '-';
                }
                return $this->service->name;
            })
            ->sortable()
            ->hide();

        $grid->column('expcted', __('Expcted'))
            ->display(function () {
                return count($this->expcted());
            });


        $grid->column('attended', __('Present'))
            ->display(function () {
                return count($this->present());
            });
        $grid->column('absent', __('Absent'))
            ->display(function () {
                return count($this->absent());
            });


        $grid->column('academic_year_id', __('Academic year'))->hide();
        $grid->column('administrator_id', __('Conducted by'))
            ->display(function () {
                return $this->created_by->name;
            })
            ->sortable();

        //session_decision status
        $grid->column('session_decision', __('Session Status'))
            ->display(function () {
                if ($this->session_decision == 'Yes') {
                    return "<span class='label label-success'>Closed</span>";
                }
                return "<span class='label label-warning'>Ongoing</span>";
            })
            ->sortable(); 
        
        //condut session column
        $grid->column('is_open', __('Results'))
            ->display(function () {
                //conduct session
                //if conducted, return a button to view results
                if ($this->is_open == 'No' && $this->session_decision == 'Yes') {
                    $url = admin_url("participants?session_id={$this->id}");
                    return "<a href='{$url}'
                    target='_blank'
                    class='btn btn-success btn-sm'>View Results</a>";
                }

                $url = url("sessions/{$this->id}/edit");
                return "<a href='{$url}'  class='btn btn-primary btn-sm'>Conduct Roll-call</a>";
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
        $show = new Show(Session::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('administrator_id', __('Administrator id'));
        $show->field('academic_year_id', __('Academic year id'));
        $show->field('term_id', __('Term id'));
        $show->field('academic_class_id', __('Academic class id'));
        $show->field('subject_id', __('Subject id'));
        $show->field('service_id', __('Service id'));
        $show->field('due_date', __('Due date'));
        $show->field('title', __('Title'));
        $show->field('is_open', __('Is open'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {

        /* $ses = Session::find(2);
        $ses->prepared = 0;
        $ses->details .= '.';
        $ses->save();
        dd($ses); */

        $form = new Form(new Session());
        $u = Admin::user();



        $term = $u->ent->active_term();
        if ($term == null) {
            return admin_error('Ooops!', 'No active term.');
        }
        $form->hidden('enterprise_id', __('Enterprise id'))->default($u->enterprise_id)->rules('required');

        if ($form->isCreating()) {
            $form->hidden('administrator_id', __('Enterprise id'))->default($u->id)->rules('required');
            $form->hidden('source', __('Source'))->default('WEB')->rules('required');
            $form->hidden('is_open', __('Is open'))->default('Yes')->rules('required');
        }

        if (!$form->isCreating()) {
            $id = ((int)(request()->route('session')));
            if ($id != null) {
                $session = Session::find($id);
                if ($session != null) {

                    //display conducted by 
                    $form->display('administrator_text', __('Conducted by'))
                        ->default($session->administrator->name_text ?? '-');
                    //dp due_date
                    $form->display('due_date', __('Due date'))
                        ->default(Utils::my_date($session->due_date));
                    //type
                    $form->display('type', __('Roll-call type'))
                        ->default($session->type);

                    //target
                    $form->display('target', __('Target Type'))
                        ->default($session->target);

                    //display target
                    $form->display('target_text', __('Target'));

                    //check if is already conducted and redirect to table
                    if ($session->is_open == 'No' && $session->session_decision == 'Yes') {
                        $form->disableSubmit();
                        //javascript to redirect to table
                        // Admin::script("window.onload = function() { window.location.href = '" . admin_url("sessions") . "'; }");
                        // return $form;
                    } else {
                        //display due date
                        $form->display('due_date', __('Due date'))
                            ->default(Utils::my_date($session->due_date));
                    }
                }
            }
        }



        $form->disableCreatingCheck();
        // $form->disableEditingCheck();
        $form->disableReset();
        $form->disableViewCheck();

        if ($form->isCreating()) {
            $form->radio('type', __('Roll-call type'))
                ->options([
                    'STUDENT_REPORT' => 'Student Report at School',
                    'STUDENT_LEAVE' => 'Student Leave School',
                    'STUDENT_MEAL' => 'Student Meals Session',
                    'CLASS_ATTENDANCE' => 'Class attendance',
                    'THEOLOGY_ATTENDANCE' => 'Theology Class attendance',
                    'ACTIVITY_ATTENDANCE' => 'Activity participation',
                ])
                ->required()
                ->rules('required');

            $form->radio('target', __('Target'))
                ->options([
                    'ENTIRE_SCHOOL' => 'All active students', //done
                    'SECULAR_CLASSES' => 'Specific secular classes', //done
                    'THEOLOGY_CLASSES' => 'Specific theology classes', //done
                    'SECULAR_STREAM' => 'Specific secular stream', //done
                    'THEOLOGY_STREAM' => 'Specific theology stream', //done
                    'SERVICE' => 'Specific service subscribers', //done
                ])
                ->required()
                ->rules('required')
                ->when('SECULAR_CLASSES', function ($form) {
                    $classes = [];
                    $u = Admin::user();
                    $term = $u->ent->active_term();
                    foreach (
                        AcademicClass::where([
                            'academic_year_id' => $term->academic_year_id,
                            'enterprise_id' => $u->enterprise_id,
                        ])->get() as $key => $class
                    ) {
                        $classes[$class->id] = $class->name_text;
                    }
                    $form->multipleSelect('secular_casses', 'Class')->options($classes)->rules('required');
                })
                ->when('THEOLOGY_CLASSES', function ($form) {
                    $classes = [];
                    $u = Admin::user();
                    $term = $u->ent->active_term();
                    foreach (
                        TheologyClass::where([
                            'academic_year_id' => $term->academic_year_id,
                            'enterprise_id' => $u->enterprise_id,
                        ])->get() as $key => $class
                    ) {
                        $classes[$class->id] = $class->name_text;
                    }
                    $form->multipleSelect('theology_classes', 'Class')->options($classes)->rules('required');
                })
                ->when('SERVICE', function ($form) {

                    $u = Admin::user();
                    $services = [];
                    foreach (
                        Service::where([
                            'enterprise_id' => $u->enterprise_id,
                        ])->get() as $key => $s
                    ) {
                        $services[$s->id] = "#" . $s->id . " " . $s->name;
                    }
                    $form->select('service_id', 'Service')->options($services)->rules('required');
                })
                ->when('SECULAR_STREAM', function ($form) {
                    $u = Admin::user();
                    $streams = [];
                    $term = $u->ent->active_term();
                    $active_classes_ids = [];
                    foreach (
                        AcademicClass::where([
                            'academic_year_id' => $term->academic_year_id,
                            'enterprise_id' => $u->enterprise_id,
                        ])->get() as $key => $class
                    ) {
                        $active_classes_ids[] = $class->id;
                    }
                    foreach (
                        \App\Models\AcademicClassSctream::whereIn('academic_class_id', $active_classes_ids)->get() as $key => $s
                    ) {
                        $streams[$s->id] =  $s->name_text;
                    }
                    $form->select('secular_stream_id', 'Secular Stream')->options($streams)->rules('required');
                })->when('THEOLOGY_STREAM', function ($form) {
                    $u = Admin::user();
                    $streams = [];
                    $term = $u->ent->active_term();
                    $active_classes_ids = [];
                    foreach (
                        TheologyClass::where([
                            'academic_year_id' => $term->academic_year_id,
                            'enterprise_id' => $u->enterprise_id,
                        ])->get() as $key => $class
                    ) {
                        $active_classes_ids[] = $class->id;
                    }
                    foreach (
                        TheologyStream::whereIn('theology_class_id', $active_classes_ids)->get() as $key => $s
                    ) {
                        $streams[$s->id] =  $s->name_text;
                    }
                    $form->select('theology_stream_id', 'Theology Stream')->options($streams)->rules('required');
                });



            // $form->text('title', __('Session title'))->rules('required');
            $form->text('details', __('Session Description'))
                ->rules('required')
                ->help('Add more details about this session, like the reason for the roll-call, or specify the activity, etc.')
                ->required();
            $form->hidden('academic_year_id', __('Academic year id'))->default($term->academic_year_id);
            $form->hidden('term_id', __('Term id'))->default($term->id);
            $form->datetime('due_date', __('Due date'))->default(date('Y-m-d H:i:s'))->rules('required');
            $form->radio('notify_present', 'Notify parent if present')->default('No')->required()
                ->options([
                    'No' => 'No',
                    'Yes' => 'Yes',
                ]);
            $form->radio('notify_absent', 'Notify parent if absent')->default('No')->required()
                ->options([
                    'No' => 'No',
                    'Yes' => 'Yes',
                ]);
        } else {


            $form->display('type', __('Session type'));
            // $form->display('title', __('Session title'));
        }







        if ($form->isCreating()) {
        } else {


            $id = ((int)(request()->route('session')));
            $session = Session::find($id);
            if ($session == null) {
                return admin_error('Error', 'Session not found.');
            }
            $participants = [];
            $candidates = Participant::where([
                'session_id' => $session->id,
            ])->get();

            foreach ($candidates as $key => $c) {
                $user = User::find($c->administrator_id);
                if ($user == null) {
                    continue;
                }
                $participants[$c->administrator_id] = $user ->user_number . " - " . $user->name_text;
            }
             


            $form->listbox('participants', 'Participants')->options($participants)
                ->help("Select members who participated in this activity")
                ->rules('required');

            $form->radio('session_decision', __('Session is status'))
                ->options([
                    'Yes' => "Close session",
                    'No' => "Active session",
                ])->when('Yes', function ($form) {
                    $form->radio('is_open', __('Are your you want to close this session?'))
                        ->options([
                            'No' => "Yes close this session",
                            'Yes' => "No, keep it open",
                        ]);
                });
        }

        return $form;
    }
}
