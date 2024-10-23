<?php

namespace App\Admin\Controllers;

use App\Models\AcademicClass;
use App\Models\Participant;
use App\Models\Service;
use App\Models\Session;
use App\Models\Subject;
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
        $grid->disableExport();
        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $u = Admin::user();
            $filter->equal('academic_class_id', 'Fliter by class')->select(AcademicClass::where([
                'enterprise_id' => $u->enterprise_id
            ])->get()
                ->pluck('name_text', 'id'));
            $filter->equal('subject_id', __('Subject'))
                ->select(
                    \App\Models\Subject::where([
                        'enterprise_id' => Admin::user()->enterprise_id,
                    ])->get()->pluck('name', 'id')
                );
            $filter->equal('is_open', __('Is open'))->select([
                1 => 'Open',
                0 => 'Closed',
            ]);
            $filter->equal('type', __('Type'))->select([
                'STUDENT_REPORT' => 'Student Report at School',
                'STUDENT_LEAVE' => 'Student Leave School',
                'STUDENT_MEAL' => 'Student Meals Session',
                'CLASS_ATTENDANCE' => 'Class attendance',
                'THEOLOGY_ATTENDANCE' => 'Theology Class attendance',
                'SECONDARY_CLASS_ATTENDANCE' => 'Secondary Class attendance',
                'ACTIVITY_ATTENDANCE' => 'Activity participation',
            ]);
            $filter->between('due_date', __('Due date'))->datetime();
            //created_at range
            $filter->between('created_at', __('Created at'))->datetime();
        });

        $activeSession = Session::where([
            'enterprise_id' => Admin::user()->enterprise_id,
            'administrator_id' => Admin::user()->id,
            'is_open' => 1,
        ])->first();

        if ($activeSession  != null) {

            return redirect(url("roll-calling?roll_call_session_id={$activeSession->id}"));
            return redirect(admin_url("sessions/{$activeSession->id}/edit"));
        }

        // $grid->disableActions();
        $grid->disableBatchActions();
        $grid->model()->where([
            'enterprise_id' => Admin::user()->enterprise_id,
            'is_open' => 0,
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
            ->sortable();

        $grid->column('subject_id', __('Subject'))
            ->display(function () {
                if ($this->subject == null) {
                    return '-';
                }
                return $this->subject->subject_name;
            })
            ->sortable();

        $grid->column('academic_class_id', __('Class'))
            ->display(function () {
                if ($this->academic_class == null) {
                    return '-';
                }
                return $this->academic_class->name;
            })
            ->sortable();

        $grid->column('service_id', __('Service'))
            ->display(function () {
                if ($this->service == null) {
                    return '-';
                }
                return $this->service->name;
            })
            ->sortable();

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
        //condut session column
        $grid->column('is_open', __('Conduct Session'))
            ->display(function () {
                //conduct session
                $url = url("roll-calling?roll_call_session_id={$this->id}");
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

        /*         $s = Session::find(1);
        Session::process_attendance($s);
        die('.'); */
        /*         $s = Session::find(1);
        $s->title .= "1";
        $s->save();  */

        $form = new Form(new Session());
        $u = Admin::user();

        /* $s = Session::find(1);
        if ($s == null) {
            $s = new Session();
            $s->enterprise_id = $u->enterprise_id;
            $s->administrator_id = $u->id;
            $s->academic_class_id = null;
            $s->subject_id = null;
            $s->stream_id = null;
            $s->prepared = 0;
            $s->notify_present = "Yes";
            $s->type = "STUDENT_REPORT";
            $s->notify_absent = "No";
            $s->due_date = date('Y-m-d');
            try {
                $s->save();
            } catch (\Throwable $th) {
                throw $th;
            }
        }
        $s->type = "STUDENT_REPORT";
        $s->save();
        Session::create_participants($s);
        die("here 1. =>{$s->id}<="); */
        $term = $u->ent->active_term();
        if ($term == null) {
            return admin_error('Ooops!', 'No active term.');
        }
        $form->hidden('enterprise_id', __('Enterprise id'))->default($u->enterprise_id)->rules('required');
        $form->hidden('administrator_id', __('Enterprise id'))->default($u->id)->rules('required');
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
                    'SECONDARY_CLASS_ATTENDANCE' => 'Secondary Class attendance',
                    'ACTIVITY_ATTENDANCE' => 'Activity participation',
                ])
                ->stacked()
                ->rules('required')
                ->when('Activity participation', function ($form) {

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
                ->when('Class attendance', function ($form) {
                    $classes = [];
                    $u = Admin::user();
                    $term = $u->ent->active_term();
                    foreach (
                        AcademicClass::where([
                            'academic_year_id' => $term->academic_year_id,
                            'enterprise_id' => $u->enterprise_id,
                        ])->get() as $key => $class
                    ) {
                        $classes[$class->id] = $class->name_text . " => " . $class->id;
                    }


                    $form->select('academic_class_id', 'Class')->options($classes)->load('subject_id', '/api/class-subject/?enterprise_id=' . $u->enterprise_id)->rules('required');

                    $form->select('subject_id', 'Subject')
                        ->options(function ($id) {
                            $obj  = Subject::find($id);
                            if ($obj != null) {
                                return [
                                    $obj->id => $obj->subject_name
                                ];
                            }
                        })
                        ->rules('required');
                });
            /*             $form->text('title', __('Session title'))->rules('required'); */
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


        $segments = request()->segments();
        $m = new Session();
        $candidates = [];
        if (isset($segments[1])) {
            $id = ((int)($segments[1]));
            $m = Session::find($id);
            if ($m != null) {
                $cands = Participant::where([
                    'session_id' => $m->id,
                ])->get();
                $candidates = [];
                foreach ($cands as $key => $c) {
                    if ($c->user == null) {
                        continue;
                    }
                    $candidates[$c->administrator_id] = $c->user->user_number . " - " . $c->user->name_text;
                }
            }
        }






        if ($form->isCreating()) {
            $form->hidden('is_open', __('Is open'))->default(1);
        } else {

            $form->listbox('participants', 'Participants')->options($candidates)
                ->help("Select members who participated in this activity")
                ->rules('required');

            $form->radio('session_decision', __('Session is status'))
                ->options([
                    2 => "Close session",
                    1 => "Active session",
                ])->when(2, function ($form) {
                    $form->radio('is_open', __('Are your you want to close this session?'))
                        ->options([
                            0 => "Yes close this session",
                        ]);
                })->when(3, function ($form) {
                    $form->radio('is_open', __('Session is open'))
                        ->options([
                            1 => "Opened",
                        ]);
                })->default('1');
        }

        $form->ignore('session_decision');
        return $form;
    }
}
