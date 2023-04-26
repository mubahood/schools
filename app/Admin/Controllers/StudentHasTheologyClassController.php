<?php

namespace App\Admin\Controllers;

use App\Models\TheologyClass;
use App\Models\TheologyClassSctream;
use App\Models\AcademicYear;
use App\Models\Course;
use App\Models\StudentHasTheologyClass;
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
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request as FacadesRequest;

class StudentHasTheologyClassController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Student\'s thelogy classes';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {



        Utils::display_checklist(Utils::students_checklist(Admin::user()));

        $grid = new Grid(new StudentHasTheologyClass());



        $dpYear   =  Admin::user()->ent->dpYear();
        $grid->model()->where([
            'enterprise_id' => Admin::user()->enterprise_id,
        ])
            ->orderBy('id', 'Desc');
        if (!Admin::user()->isRole('dos')) {
            $grid->disableCreateButton();
            $grid->disableExport();
            $grid->disableActions();
        }

        $grid->actions(function ($actions) {
            $actions->disableView();
        });

        $grid->disableBatchActions();
        $grid->disableExport();



        $grid->filter(function ($filter) {
            // Remove the default id filter
            $filter->disableIdFilter();

            // Add a column filter
            $u = Admin::user();
            $filter->equal('theology_class_id', 'Filter by class')->select(TheologyClass::where([
                'enterprise_id' => $u->enterprise_id
            ])->orderBy('id', 'Desc')->get()->pluck('name_text', 'id'));


            $u = Admin::user();
            $ajax_url = url(
                '/api/ajax?'
                    . 'enterprise_id=' . $u->enterprise_id
                    . "&search_by_1=name"
                    . "&search_by_2=id"
                    . "&model=User"
            );

            $filter->equal('administrator_id', 'Student')->select()->ajax($ajax_url);
        });



        $grid->model()->where([
            'enterprise_id' => Admin::user()->enterprise_id,
        ])
            ->orderBy('id', 'Desc');

        $grid->column('id', __('Id'))->sortable();

        $grid->column('administrator_id', __('Student'))->display(function () {
            if (!$this->student) {
                return "-";
            }
            return  $this->student->name;
        });

        $grid->column('theology_class_id', __('Class'))->display(function () {
            if (!$this->class) {
                return "-";
            }
            return  $this->class->name_text;
        })->sortable();


        $grid->column('stream', __('Stream'))->display(function () {
            if (!$this->stream) {
                return '<a class="bg-danger badge  badge-danger">No Stream</a>';
            }
            return  $this->stream->name;
        })->sortable();


        $u = Admin::user();

        if ($u->enterprise->type != 'Primary') {

            $grid->column('optional_subjects_picked', __('Selected optional subjects'))
                ->display(function ($title) {

                    if ($title == 1) {
                        return "<span style='color:green'>Done</span>";
                    } else {
                        return "<span style='color:red'>Not done</span>";
                    }
                })
                ->sortable();
        }

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
        $show = new Show(StudentHasTheologyClass::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('theology_class_id', __('Academic class id'));
        $show->field('administrator_id', __('Administrator id'));
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

        $form = new Form(new StudentHasTheologyClass());


        if ($form->isCreating()) {
            $form->select('administrator_id', 'Student')->options(function () {
                return Administrator::where([
                    'enterprise_id' => Admin::user()->enterprise_id,
                    'user_type' => 'student',
                    'status' => 1
                ])->get()->pluck('name', 'id');
            })
                ->rules('required');

            $dpYear   =  Admin::user()->ent->dpYear();
            $years = [];
            foreach (TheologyClass::where([
                'enterprise_id' => Admin::user()->enterprise_id,
                'academic_year_id' => $dpYear->id
            ])->get() as $key => $value) {
                $years[$value->id] = $value->name_text;
            }
            $form->select('theology_class_id', 'Class')->options($years)->rules('required');
        } else { 
            $form->select('administrator_id', 'Student')->options(function () {
                return Administrator::where([
                    'enterprise_id' => Admin::user()->enterprise_id,
                    'user_type' => 'student',
                    'status' => 1
                ])->get()->pluck('name', 'id');
            })
                ->readOnly()
                ->rules('required');

            $dpYear   =  Admin::user()->ent->dpYear();
            $years = [];
            foreach (TheologyClass::where([
                'enterprise_id' => Admin::user()->enterprise_id,
            ])->get() as $key => $value) {
                $years[$value->id] = $value->name_text;
            }
            $form->select('theology_class_id', 'Class')
                ->readOnly()
                ->options($years)->rules('required');

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
                $StudentHasTheologyClass = StudentHasTheologyClass::find($id);

                if ($StudentHasTheologyClass == null) {
                    die("Class not found..");
                }


                $TheologyClass = TheologyClass::find($StudentHasTheologyClass->theology_class_id);


                if ($TheologyClass == null || $StudentHasTheologyClass == null) {
                    die("Classes not found.");
                } 
                $strems = [];
                foreach ($TheologyClass->streams as $key => $s) {
                    $strems[$s->id] = $s->name;
                }
                $form->select('theology_stream_id', 'Stream')->options($strems);  


        }
        return $form;

        // callback before save
        $form->submitted(function (Form $form) {

            if (isset($_POST['optional_subjects'])) {
                foreach ($_POST['optional_subjects'] as $key => $post) {

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
                    $StudentHasTheologyClass = StudentHasTheologyClass::find($id);

                    if ($StudentHasTheologyClass == null) {
                        die("Class not found..");
                    }


                    $TheologyClass = TheologyClass::find($StudentHasTheologyClass->theology_class_id);


                    if ($TheologyClass == null || $StudentHasTheologyClass == null) {
                        die("Classes not found.");
                    }
                    $administrator_id = $StudentHasTheologyClass->administrator_id;


                    $course_id = $post['course_id'];
                    $admin = StudentHasTheologyClass::find($administrator_id);
                    $course = Course::find($course_id);
                    if ($course == null) {
                        die("course not found.");
                    }

                    $admin = Administrator::find($administrator_id);
                    if ($admin == null) {
                        die("Admin not found.");
                    }
                    $exists = StudentHasOptionalSubject::where([
                        'theology_class_id' => $TheologyClass->id,
                        'course_id' => $course_id,
                        'administrator_id' => $administrator_id,
                    ])->first();
                    if ($exists == null) {
                        $optional =  new StudentHasOptionalSubject();
                        $optional->enterprise_id  = $admin->enterprise_id;
                        $optional->theology_class_id  = $TheologyClass->id;
                        $optional->course_id  = $course->id;
                        $optional->administrator_id  = $admin->id;
                        $optional->main_course_id  = $course->main_course_id;
                        $optional->student_has_class_id  = $StudentHasTheologyClass->id;
                        $optional->save();
                    }
                }
                return redirect(admin_url('students-classes'));
            }
        });


        $form->tab('Class information', function ($form) {

            $form->disableCreatingCheck();
            $form->disableEditingCheck();
            $form->disableReset();
            $form->disableViewCheck();


            $form->saving(function (Form $form) {

                if ($form->isCreating()) {
                    $class = StudentHasTheologyClass::where([
                        'administrator_id' => $form->administrator_id,
                        'enterprise_id' => $form->theology_class_id,

                    ])->first();
                    if ($class != null) {
                        return Redirect::back()->withInput()->withErrors([
                            'theology_class_id' => 'Selected student is already registered in this class.'
                        ]);
                    }
                }
            });


            $u = Admin::user();
            $form->hidden('enterprise_id')->rules('required')->default($u->enterprise_id)
                ->value($u->enterprise_id);

            if ($form->isCreating()) {

 

                    $u = Admin::user();
                    $ajax_url = url(
                        '/api/ajax?'
                            . 'enterprise_id=' . $u->enterprise_id
                            . "&search_by_1=name"
                            . "&search_by_2=id"
                            . "&model=User"
                    );
             
                    $form->decimal('quanity', __('Quanity'))->rules('required');
            
                    $form->select('administrator_id', 'Student')->options(function ($id) {  
                            $a = Administrator::find($id);
                            if ($a) {
                                return [$a->id => "#" . $a->id . " - " . $a->name];
                            }
                        })
                        ->ajax($ajax_url)->rules('required');
                        

                $form->select('theology_class_id', 'Class')->options(function () {
                    return TheologyClass::where([
                        'enterprise_id' => Admin::user()->enterprise_id,
                    ])->get()->pluck('name', 'id');
                });
            } else {

                die("nto creating");

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
                $StudentHasTheologyClass = StudentHasTheologyClass::find($id);

                if ($StudentHasTheologyClass == null) {
                    die("Class not found..");
                }


                $TheologyClass = TheologyClass::find($StudentHasTheologyClass->theology_class_id);


                if ($TheologyClass == null || $StudentHasTheologyClass == null) {
                    die("Classes not found.");
                }

                dd($TheologyClass);

                $form->select('administrator_id', 'Student')->options(function () {
                    return Administrator::where([
                        'enterprise_id' => Admin::user()->enterprise_id,
                        'user_type' => 'student',
                    ])->get()->pluck('name', 'id');
                })
                    ->readOnly()
                    ->rules('required');

                $form->select('theology_class_id', 'Class')->options(function () {
                    return TheologyClass::where([
                        'enterprise_id' => Admin::user()->enterprise_id,
                    ])->get()->pluck('name', 'id');
                })
                    ->readOnly();

                    
            }
        });

        if (Admin::user()->enterprise->type != 'Primary') {
            $form->tab('Optional subjects', function ($form) {

                $form->morphMany('optional_subjects', 'Click to add optional subject', function (Form\NestedForm $form) {

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
                    $class = StudentHasTheologyClass::find($id);

                    if ($class == null) {
                        die("Class not found..");
                    }

                    $academic_class = TheologyClass::find($class->theology_class_id);
                    if ($academic_class == null) {
                        die("Academic class not found.");
                    }

                    $subs = [];
                    foreach ($academic_class->getOptionalSubjectsItems() as  $s) {
                        $subs[((int)($s->course_id))] = $s->subject_name . " - " . $s->code;
                    }

                    $u = Admin::user();

                    $form->hidden('enterprise_id')->default($u->enterprise_id);



                    $form->select('course_id', 'Select subject')
                        ->options(
                            $subs
                        )->rules('required');
                });
            });
        }






        return $form;
    }
}
