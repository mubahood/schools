<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Post\ChangeStudentsClass;
use App\Models\AcademicClass;
use App\Models\AcademicClassSctream;
use App\Models\AcademicYear;
use App\Models\Course;
use App\Models\SecondarySubject;
use App\Models\StudentHasClass;
use App\Models\StudentHasOptionalSubject;
use App\Models\User;
use App\Models\Utils;
use Dflydev\DotAccessData\Util;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Exception;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request as FacadesRequest;

class StudentHasClassController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Student\'s class';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {


        /*   $enterprise_id = 8;
        $users = StudentHasClass::where([
            'enterprise_id' => $enterprise_id
        ])->get();

        foreach ($users as $key => $c) {
            if ($c->class->streams->count() < 1) {
                continue;
            }
            $stream = $c->class->streams[rand(0, ($c->class->streams->count() - 1))];
            $c->stream_id = $stream->id;
            $c->save();
            echo $c->id . "<hr>";
        }
        dd($users); */

        //Utils::display_checklist(Utils::students_checklist(Admin::user()));

        $grid = new Grid(new StudentHasClass());

        //$grid->paginate(500);

        $grid->model()->where('enterprise_id', Admin::user()->enterprise_id)
            ->orderBy('id', 'Desc');
        if (!Admin::user()->isRole('dos')) {
            $grid->disableCreateButton();
            $grid->disableExport();
            $grid->disableActions();
        }

        $u = Admin::user();
        $year = $u->ent->active_academic_year();
        if ($year == null) {
            die('No active academic year found.');
        }

        if ((!isset($_GET['academic_year_id'])) && (!isset($_GET['academic_class_id']))) {
            $grid->model()->where('academic_year_id', $year->id);
        }

        // $grid->actions(function ($actions) {
        //     $actions->disableDelete();
        // });

        $grid->batchActions(function ($batch) {
            $batch->disableDelete();
            $batch->add(new ChangeStudentsClass());
        });

        $grid->disableExport();
        $grid->disableBatchActions();

        $grid->filter(function ($filter) {
            // Remove the default id filter
            $filter->disableIdFilter();

            // Add a column filter
            $u = Admin::user();
            $filter->equal('academic_class_id', 'Filter by class')->select(AcademicClass::where([
                'enterprise_id' => $u->enterprise_id
            ])->orderBy('id', 'Desc')->get()->pluck('name_text', 'id'));

            $year = $u->ent->active_academic_year();
            if ($year == null) {
                die('No active academic year found.');
            }

            $streams = [];
            foreach (
                AcademicClassSctream::where(
                    [
                        'enterprise_id' => $u->enterprise_id,
                    ]
                )
                    ->orderBy('id', 'desc')
                    ->get() as $ex
            ) {
                if ($ex->academic_class == null) {
                    continue;
                }
                if ($ex->academic_class->academic_year_id != $year->id) {
                    continue;
                }
                $streams[$ex->id] = $ex->academic_class->short_name . " - " . $ex->name;
            }

            $filter->equal('stream_id', 'Filter by Stream')->select($streams);


            $filter->equal('academic_year_id', 'Filter by academic year')->select(AcademicYear::where([
                'enterprise_id' => $u->enterprise_id
            ])->orderBy('id', 'Desc')->get()->pluck('name', 'id'));

            $u = Admin::user();
            $ajax_url = url('/api/ajax-users?enterprise_id=' . $u->enterprise_id . "&user_type=student");

            $filter->equal('administrator_id', 'Student')->select(function ($id) {
                $a = User::find($id);
                if ($a) {
                    return [$a->id => $a->name_text];
                }
            })->ajax($ajax_url);
        });



        $grid->model()->where([
            'enterprise_id' => Admin::user()->enterprise_id,
        ])
            ->orderBy('id', 'Desc');


        $grid->column('student.avatar', __('Photo'))
            ->lightbox(['width' => 60, 'height' => 60]);


        /*   $grid->column('id', __('Id'))
            ->display(function ($title) {
                $u = Admin::user();
                if ($this->class->enterprise_id != $u->enterprise_id) {
                    $this->delete();
                }
                return $title;
            })
            ->sortable(); */
        /*    $grid->column('done_selecting_option_courses', __('FROM P7'))
        ->using([
            1 => 'From P.7',
            0 => 'From P.6',
        ])
        ->filter([
            1 => 'From P.7',
            0 => 'From P.6',
        ])
        ->dot([
            1 => 'success',
            0 => 'danger',
        ])
        ->sortable(); */

        $grid->column('administrator_id', __('Student'))->display(function () {
            if (!$this->student) {
                return "-";
            }
            return  $this->student->name;
        });

        $grid->column('academic_class_id', __('Class'))->display(function () {
            if (!$this->class) {
                return "-";
            }
            return  $this->class->name_text;
        })->sortable();
        $grid->column('stream_id', __('Stream'))->display(function () {
            if (!$this->stream) {
                return "-";
            }
            //active year
            $year = $this->student->ent->active_academic_year();
            $class = $this->stream->academic_class;
            if ($year->id == $this->academic_year_id) {
                if ($class->academic_year_id != $year->id) {
                    try {
                        $this->stream_id = null;
                        $this->save();
                    } catch (\Throwable $th) {
                        //throw $th;
                    }
                    try {
                        $this->student->stream_id = null;
                        $this->student->save();
                    } catch (\Throwable $th) {
                        //throw $th;
                    }

                    return "-";
                }
                return  $this->stream->name . " - " . $class->name_text;
            }
            return  $this->stream->name_text;
        })->sortable();
        $grid->column('academic_year_id', __('Academic year'))->display(function () {
            if (!$this->year) {
                return "-";
            }
            return  $this->year->name;
        })->sortable();
        $u = Admin::user();

        if ($u->enterprise->type != 'Primary') {

            $grid->column('optional_subjects_picked', __('Selected Optional Subjects'))
                ->display(function ($title) {
                    if ($title == 1) {
                        return "<span style='color:green'>Done</span>";
                    } else {
                        return "<span style='color:red'>Not done</span>";
                    }
                })
                ->filter([
                    1 => 'Done',
                    0 => 'Not done',
                ])
                ->sortable();

            $grid->column('pick_subject', __('Pick Optional Subjects'))
                ->display(function ($title) {
                    $preFrix = "";
                    if ($this->optional_subjects_picked == 1) {
                        $subjetcs =  $subs = SecondarySubject::wherein(
                            'id',
                            $this->new_curriculum_optional_subjects,
                        )->get()->pluck('subject_name')->toarray();
                        if (count($subjetcs) > 0) {
                            $preFrix = implode(',', $subjetcs) . "<br>";
                        }
                    }
                    $url = admin_url('students-classes/' . $this->id . '/edit');
                    return "$preFrix<a href='$url' ><b>Select Subjects</b></a>";
                });
        }

        $grid->column('student.status', __('Status'))->display(function ($title) {
            if ($title . "" == '1') {
                return "<span style='color:green'>Active</span>";
            } else {
                return "<span style='color:red'>Not active</span>";
            }
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
        $show = new Show(StudentHasClass::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('academic_class_id', __('Academic class'));
        $show->field('administrator_id', __('Administrator id'));
        $show->field('stream_id', __('Stream id'));
        $show->field('updated_at', __('Updated at'));
        $show->field('created_at', __('Created at'));
        $show->field('academic_year_id', __('Academic year id'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {

        $form = new Form(new StudentHasClass());




        $form->disableCreatingCheck();
        $form->disableReset();
        $form->disableViewCheck();

        $u = Admin::user();
        $form->hidden('enterprise_id')->default($u->enterprise_id)
            ->value($u->enterprise_id);

        if ($form->isCreating()) {



            $ajax_url = url(
                '/api/ajax-users?'
                    . 'enterprise_id=' . $u->enterprise_id
                    . "&search_by_1=name"
                    . "&search_by_2=id"
                    . "&user_type=student"
                    . "&model=User"
            );

            $form->select('administrator_id', "Student")
                ->options(function ($id) {
                    $a = Administrator::find($id);
                    if ($a) {
                        return [$a->id => "#" . $a->id . " - " . $a->name];
                    }
                })
                ->ajax($ajax_url)->rules('required');

            $classes = [];
            foreach (
                AcademicClass::where([
                    'enterprise_id' => Admin::user()->enterprise_id,
                ])
                    ->orderBy('id', 'desc')
                    ->get() as $key => $value
            ) {
                $classes[$value->id] = $value->name_text;
            }


            $form->select('academic_class_id', 'Class')->options($classes)->load(
                'stream_id',
                url('/api/streams?enterprise_id=' . $u->enterprise_id)
            );
            $form->select('stream_id', __('Stream'))->options(function ($id) {
                $streams = [];
                $u = Admin::user();
                foreach (
                    AcademicClassSctream::where(
                        [
                            'enterprise_id' => $u->enterprise_id,
                        ]
                    )
                        ->orderBy('id', 'desc')
                        ->get() as $ex
                ) {
                    $streams[$ex->id] = $ex->name_text;
                }
                return $streams;
            });
        } else {

            $id = 0;
            foreach (explode('/', $_SERVER['REQUEST_URI']) as $key => $v) {
                if ((int)($v) > 0) {
                    $id = (int)($v);
                    break;
                }
            }
            $hasClass = StudentHasClass::find($id);
            if ($hasClass == null) {
                throw new Exception("Has class not found.", 1);
            }
            if ($hasClass->class == null) {
                throw new Exception("Class not found.", 1);
            }

            $streams = [];
            $u = Admin::user();
            foreach (
                AcademicClassSctream::where(
                    [
                        'enterprise_id' => $u->enterprise_id,
                    ]
                )
                    ->orderBy('id', 'desc')
                    ->get() as $ex
            ) {
                $streams[$ex->id] = $ex->name_text;
            }
            $form->display('administrator_id', 'Class')->with(function ($value) {
                return Administrator::find($value)->name;
            });
            $form->display('academic_class_id', 'Class')->with(function ($value) {
                return AcademicClass::find($value)->name_text;
            });


            $form->select('stream_id', __('Stream'))->options($streams);
        }


        if ($form->isEditing()) {
            if (Admin::user()->enterprise->type != 'Primary') {
                // $form->divider('Old Curriculum - Optional subjects');
                /* $form->morphMany('optional_subjects', 'Click to add optional subject', function (Form\NestedForm $form) {
                    $id = ((int)(FacadesRequest::segment(2)));
                    if ($id < 1) {
                        $id = ((int)(FacadesRequest::segment(1)));
                    }
                    if ($id < 1) {
                        $id = ((int)(FacadesRequest::segment(0)));
                    }
                    if ($id < 1) {
                        $id = ((int)(FacadesRequest::segment(3)));
                    }
                    if ($id < 1) {
                        $id = ((int)(FacadesRequest::segment(4)));
                    }
                    if ($id < 1) {
                        die("Class not found.");
                    }
                    $class = StudentHasClass::find($id);

                    if ($class == null) {
                        die("Class not found..");
                    }

                    $academic_class = AcademicClass::find($class->academic_class_id);
                    if ($academic_class == null) {
                        die("Academic class not found.");
                    }

                    $subs = [];
                    foreach ($academic_class->getNewCurriculumOptionalSubjectsItems() as  $s) {
                        $subs[((int)($s->course_id))] = $s->subject_name . " - " . $s->code;
                    }

                    $u = Admin::user();

                    $form->hidden('enterprise_id')->default($u->enterprise_id);
                    $form->hidden('administrator_id')->default($class->administrator_id);
                    $form->hidden('student_has_class_id')->default($class->id);


                    $form->select('subject_id', 'Select subject')
                        ->options(
                            $subs
                        );
                }); */
                $form->divider('New Curriculum - Optional subjects');
                /* $form->morphMany('new_curriculum_optional_subjects', 'Click to add optional subject', function (Form\NestedForm $form) {
                    $id = ((int)(FacadesRequest::segment(2)));
                    if ($id < 1) {
                        $id = ((int)(FacadesRequest::segment(1)));
                    }
                    if ($id < 1) {
                        $id = ((int)(FacadesRequest::segment(0)));
                    }
                    if ($id < 1) {
                        $id = ((int)(FacadesRequest::segment(3)));
                    }
                    if ($id < 1) {
                        $id = ((int)(FacadesRequest::segment(4)));
                    }
                    if ($id < 1) {
                        die("Class not found.");
                    }
                    $class = StudentHasClass::find($id);

                    if ($class == null) {
                        die("Class not found..");
                    }

                    $academic_class = AcademicClass::find($class->academic_class_id);
                    if ($academic_class == null) {
                        die("Academic class not found.");
                    }

                    $subs = [];
                    foreach ($academic_class->getNewCurriculumOptionalSubjectsItems() as  $s) {
                        $subs[((int)($s->id))] = $s->subject_name . " - " . $s->code;
                    }

                    $u = Admin::user();

                    $form->hidden('enterprise_id')->default($u->enterprise_id);
                    $form->hidden('administrator_id')->default($class->administrator_id);
                    $form->hidden('student_has_class_id')->default($class->id);


                    $form->select('secondary_subject_id', 'Select subject')
                        ->options(
                            $subs
                        );
                }); */




                $id = ((int)(FacadesRequest::segment(2)));
                if ($id < 1) {
                    $id = ((int)(FacadesRequest::segment(1)));
                }
                if ($id < 1) {
                    $id = ((int)(FacadesRequest::segment(0)));
                }
                if ($id < 1) {
                    $id = ((int)(FacadesRequest::segment(3)));
                }
                if ($id < 1) {
                    $id = ((int)(FacadesRequest::segment(4)));
                }
                if ($id < 1) {
                    die("Class not found.");
                }
                $class = StudentHasClass::find($id);

                if ($class == null) {
                    die("Class not found..");
                }

                $academic_class = AcademicClass::find($class->academic_class_id);
                if ($academic_class == null) {
                    die("Academic class not found.");
                }

                $subs = [];
                foreach ($academic_class->getNewCurriculumOptionalSubjectsItems() as  $s) {
                    $class_text = "";
                    if ($s->academic_class != null) {
                        $class_text = " - (" . $s->academic_class->short_name . ")";
                    }
                    $subs[((int)($s->id))] = $s->subject_name . " - " . $s->code . $class_text;
                }

                $form->checkbox('new_curriculum_optional_subjects', __('Select Optional Subjects (New Curriculum'))
                    ->options($subs)
                    ->stacked();
            }
        }




        return $form;
    }
}
