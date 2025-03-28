<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Post\ChangeStudentsStatus;
use App\Admin\Actions\Post\PromoteStudentsClass;
use App\Admin\Actions\Post\StudentsChangeGender;
use App\Admin\Actions\Post\UpdateStudentsSecularStream;
use App\Admin\Actions\Post\UpdateStudentsTheologyStream;
use App\Models\AcademicClass;
use App\Models\AcademicClassSctream;
use App\Models\AcademicYear;
use App\Models\AdminRole;
use App\Models\AdminRoleUser;
use App\Models\StudentHasClass;
use App\Models\Subject;
use App\Models\TheologyClass;
use App\Models\TheologySubject;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserBatchImporter;
use App\Models\Utils;
use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Tab;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;


class StudentsController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Students';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        /*  $u = User::find(2317);
        $u->religion .= '.'; 
        $u->update_theo_classes();
 
        die(); */

        /*
        $u = Administrator::find(2334);
        $u->status =  1;
        $u->save();
        dd($u);  
        $u->current_class_id = 19;
        $u->first_name .= '.';
        $u->save();
        dd($u);
        dd("done");  */
        $segments = request()->segments();
        $status = 0;

        if (in_array('students', $segments)) {
            $status = 1;
        } else 
        if (in_array('pending-students', $segments)) {
            $status = 2;
        } else if (in_array('not-active-students', $segments)) {
            $status = 0;
        }

        $grid = new Grid(new User());

        $grid->perPages([10, 50, 100, 300, 500]);
        $grid->batchActions(function ($batch) {
            $batch->disableDelete();
            $batch->add(new ChangeStudentsStatus());
            $batch->add(new PromoteStudentsClass());
            $batch->add(new UpdateStudentsSecularStream());
            $segments = request()->segments();
            if (in_array('students', $segments)) {
                $batch->add(new UpdateStudentsTheologyStream());
            }
            $batch->add(new StudentsChangeGender());
        });
        $grid->actions(function ($actions) {
            if (!Auth::user()->isRole('admin')) {
                $actions->disableDelete();
            }
        });




        Utils::display_checklist(Utils::students_checklist(Admin::user()));
        Utils::display_checklist(Utils::students_optional_subjects_checklist(Admin::user()));

        $teacher_subjects = Subject::where([
            'subject_teacher' => Admin::user()->id
        ])
            ->orWhere([
                'teacher_1' => Admin::user()->id
            ])
            ->orWhere([
                'teacher_2' => Admin::user()->id
            ])
            ->orWhere([
                'teacher_3' => Admin::user()->id
            ])
            ->get();



        $teacher_theology_subjects = TheologySubject::where([
            'subject_teacher' => Admin::user()->id
        ])

            ->orWhere([
                'teacher_1' => Admin::user()->id
            ])
            ->orWhere([
                'teacher_2' => Admin::user()->id
            ])
            ->orWhere([
                'teacher_3' => Admin::user()->id
            ])
            ->get();



        $grid->filter(function ($filter) {

            $u = Admin::user();
            $ajax_url = url(
                '/api/ajax?'
                    . 'enterprise_id=' . $u->enterprise_id
                    . "&search_by_1=name"
                    . "&search_by_2=id"
                    . "&model=User"
            );

            $filter->equal('parent_id', 'Filte by Parent')
                ->select(function ($id) {
                    $a = User::find($id);
                    if ($a) {
                        return [$a->id => "#" . $a->id . " - " . $a->name];
                    }
                })

                ->ajax($ajax_url);



            $filter->between('created_at', 'Admitted')->date();
            $filter->like('school_pay_payment_code', 'By school-pay code');
            $u = Admin::user();

            if (!Admin::user()->isRole('dos')) {


                $teacher_subjects = Subject::where([
                    'subject_teacher' => Admin::user()->id
                ])
                    ->orWhere([
                        'teacher_1' => Admin::user()->id
                    ])
                    ->orWhere([
                        'teacher_2' => Admin::user()->id
                    ])
                    ->orWhere([
                        'teacher_3' => Admin::user()->id
                    ])
                    ->get();

                $teacher_theology_subjects = TheologySubject::where([
                    'subject_teacher' => Admin::user()->id
                ])
                    ->orWhere([
                        'teacher_1' => Admin::user()->id
                    ])
                    ->orWhere([
                        'teacher_2' => Admin::user()->id
                    ])
                    ->orWhere([
                        'teacher_3' => Admin::user()->id
                    ])
                    ->get();

                /* if ($teacher_subjects->count() > 0) {
                    $filter->equal('current_class_id', 'Filter by class')->select(AcademicClass::where([
                        'enterprise_id' => $u->enterprise_id
                    ])->where('id', $teacher_subjects->pluck('academic_class_id'))->orderBy('id', 'Desc')->get()->pluck('name_text', 'id'));
                } */


                if ($teacher_theology_subjects->count() > 0) {

                    $classes = TheologyClass::where([
                        'enterprise_id' => $u->enterprise_id
                    ])->where('id', $teacher_theology_subjects->pluck('theology_class_id'))->orderBy('id', 'Desc')->get()->pluck('name_text', 'id');
                    $filter->equal('current_theology_class_id', 'Filter by theology class')->select($classes);
                }
            } else {

                $classes = TheologyClass::where([
                    'enterprise_id' => $u->enterprise_id
                ])->orderBy('id', 'Desc')->get()->pluck('name_text', 'id');

                $filter->equal('current_class_id', 'Filter by class')->select(AcademicClass::where([
                    'enterprise_id' => $u->enterprise_id
                ])->orderBy('id', 'Desc')->get()->pluck('name_text', 'id'));
                $classes[0] = 'No theology class';
                $filter->equal('current_theology_class_id', 'Filter by theology class')->select($classes);
            }

            $streams = [];
            $term = $u->ent->active_term();
            if ($term != null) {
                foreach (
                    AcademicClassSctream::where(
                        [
                            'enterprise_id' => $u->enterprise_id,
                        ]
                    )
                        ->orderBy('id', 'desc')
                        ->get() as $ex
                ) {
                    if ($ex->academic_class->academic_year_id != $term->academic_year_id) {
                        continue;
                    }
                    $streams[$ex->id] = $ex->name_text;
                }
            }
            $filter->equal('stream_id', 'Filter by Stream')->select($streams);



            // Remove the default id filter
            $filter->disableIdFilter();
        });



        //user_number column


        $grid->quickSearch('name', 'lin', 'school_pay_payment_code', 'user_number')
            ->placeholder("Search by name or LIN or school pay code or ID number");
        //on export, emergency_person_name as it is
        $grid->export(function ($export) {
            $export->originalValue([
                'emergency_person_name',
                'emergency_person_phone',
                'lin'
            ]);
            $export->except([
                'parent_id',
                'avatar',
                'documents',
            ]);
        });





        if (!Admin::user()->isRole('dos')) {
            $grid->disableExport();
            $grid->disableCreateButton();

            /*  $grid->model()->where(
                'current_class_id',
                $teacher_subjects->pluck('academic_class_id'),
            )->orWhereIn(
                'current_theology_class_id',
                $teacher_theology_subjects->pluck('theology_class_id'),
            ); */
        } else {
        }


        $grid->column('id', __('ID'))
            ->sortable();

        $grid->model()->where([
            'enterprise_id' => Admin::user()->enterprise_id,
            'user_type' => 'student',
            'status' => $status
        ]);


        /*  if (Admin::user()->isRole('dos')) {
            $grid->column('status', 'Status')
                ->filter([
                    0 => 'Not active',
                    2 => 'Pending',
                    1 => 'Verified',
                ])
                ->editable('select', [1 => 'Active', 2 => 'Pending', 0 => 'Not active']);
        } else {
            $grid->column('status', __('Status'))
                ->filter([0 => 'Pending', 1 => 'Verified'])
                ->using([0 => 'Pending', 1 => 'Verified'])
                ->width(100)
                ->label([
                    0 => 'danger',
                    1 => 'success',
                ])
                ->sortable();
        } */




        $grid->column('avatar', __('Photo'))
            ->lightbox(['width' => 60, 'height' => 60])
            ->sortable();


        $grid->column('name', __('Name'))->sortable();

        $grid->column('current_class_id', __('Current class'))
            ->display(function () {
                if ($this->current_class == null) {
                    return '<span class="badge bg-danger">No class</span>';
                }
                return $this->current_class->name_text;
            })->sortable();
        $grid->column('stream_id', __('Stream'))
            ->display(function () {
                if ($this->stream == null) {
                    return 'No Stream';
                }
                return $this->stream->name;
            })->sortable();



        $grid->column('current_theology_class_id', __('Theology class'))
            ->display(function () {
                if ($this->current_theology_class == null) {
                    return '<span class="badge bg-danger">No class</span> ';
                }
                return $this->current_theology_class->name_text;
            })
            ->hide()
            ->sortable();

        $grid->column('theology_stream_id', __('Theology Stream'))
            ->display(function () {
                if ($this->theology_stream == null) {
                    return 'No Stream';
                }
                return $this->theology_stream->name;
            })->sortable();



        $grid->column('sex', __('Gender'))
            ->sortable()
            ->filter(['Male' => 'Male', 'Female' => 'Female'])
            ->editable('select', ['Male' => 'Male', 'Female' => 'Female']);
            
        $grid->column('emergency_person_name', __('Guardian'))
            ->hide()
            ->sortable()
            ->editable();
        $grid->column('emergency_person_phone', __('Guardian Phone'))->sortable()
            ->editable()->filter('like');


        $grid->column('phone_number_1', __('Phone number'))->hide();
        $grid->column('phone_number_2', __('Phone number 2'))->hide();
        $grid->column('email', __('Email'))->hide();
        $grid->column('date_of_birth', __('D.O.B'))->sortable()->hide();
        $grid->column('nationality', __('Nationality'))->sortable()->hide();

        $grid->column('place_of_birth', __('Address'))->sortable()->hide();
        $grid->column('home_address', __('Home address'))->hide();

        $grid->column('lin', __('LIN'))->sortable()->editable()
            ->filter('like');
        $grid->column('school_pay_payment_code', __('School pay payment code'))->sortable()
            ->filter('like');

        $grid->column('parent_id', __('Parent'))
            ->display(function ($x) {
                $parent = $this->parent;
                if ($parent == null) {
                    $parent = $this->getParent();
                }
                if ($parent == null) {
                    if ($x != null) {
                        return $x;
                    }
                    try {
                        $p = User::createParent($this);
                    } catch (\Throwable $th) {
                        //throw $th;
                    }
                    return 'No parent';
                }

                if ($this->parent == null) {
                    return 'No parent';
                }

                $txt = '<a href="' . admin_url('parents/?id=' . $this->parent->id) . '" title="View parent" ><b>' . $this->parent->name . "</b></a>";

                return $txt;
            })
            ->sortable();
        $grid->column('documents', __('Print Documents'))
            ->display(function () {
                $admission_letter = url('print-admission-letter?id=' . $this->id);
                return '<a title="Print admission letter" href="' . $admission_letter . '" target="_blank">Admission letter</a>';
            });




        $grid->column('created_at', __('Admitted'))
            ->display(function ($date) {
                return Carbon::parse($date)->format('d-M-Y');
            })->hide()->sortable();

        $grid->column('user_number', __('ID Number'))->sortable();

        //residence
        $grid->column('residence', __('Residence'))->sortable()
            ->label([
                'DAY_SCHOLAR' => 'success',
                'BOARDER' => 'info',
            ])->filter([
                'DAY_SCHOLAR' => 'Day Scholar',
                'BOARDER' => 'Boarder',
            ])->sortable()->hide();

        //theology_stream_id

        //current_theology_class_id
        /*    $grid->column('current_theology_class_id', __('Theology Class'))
            ->display(function () {
                if ($this->current_theology_class == null) {
                    return 'No class';
                }
                return $this->current_theology_class->name_text;
            })->hide()->sortable();
 */
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

        $u = Administrator::findOrFail($id);
        $tab = new Tab();
        $term = $u->ent->active_term();
        $active_term_transactions = $u->account->transactions()->where([
            'term_id' => $term->id
        ])->get();
        $services_for_this_term  = $u->services()->where([
            'due_term_id' => $term->id
        ])->get();

        //revers $services_for_this_term
        $services_for_this_term = $services_for_this_term->reverse();
        $student_data = null;

        if ($u->user_type == 'student') {
            $student_data = $u->get_finances();
        }

        /* 
        "id" => 123
        "created_at" => "2024-02-02 01:17:58"
        "updated_at" => "2024-02-02 01:17:58"
        "enterprise_id" => 7
        "academic_class_id" => 127
        "name" => "Tuition - Primary Two 2024"
        "amount" => 885000
        "type" => "Secular"
        "theology_class_id" => null
        "cycle" => "Termly"
        "due_term_id" => 40
*/


        //reverse $active_term_transactions
        $active_term_transactions = $active_term_transactions->reverse();

        $tab->add('Bio', view('admin.dashboard.show-user-profile-bio', [
            'u' => $u,
            'active_term_transactions' => $active_term_transactions,
            'student_data' => $student_data
        ]));
        $tab->add('Classes', view('admin.dashboard.show-user-profile-classes', [
            'u' => $u,
            'student_data' => $student_data
        ]));
        $tab->add('Services', view('admin.dashboard.show-user-profile-bills', [
            'u' => $u,
            'services_for_this_term' => $services_for_this_term,
            'student_data' => $student_data
        ]));

        $all_transactions = $u->account->transactions;
        //reverse $all_transactions
        $all_transactions = $all_transactions->reverse();
        $tab->add('Transactions', view('admin.dashboard.show-user-profile-transactions', [
            'u' => $u,
            'all_transactions' => $all_transactions,
            'student_data' => $student_data
        ]));
        return $tab;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {

        /*  $u  = Administrator::find(10615);
        Administrator::my_update($u);
        die("done romina"); */
        $u = Admin::user();
        $form = new Form(new Administrator());



        $form->tab('BIO DATA', function (Form $form) {

            if (!$form->isEditing()) {
                if (Admin::user()->isRole('dos')) {
                    $form->multipleSelect('roles', 'Role')
                        ->attribute([
                            'autocomplete' => 'off'
                        ])
                        ->default([4])
                        ->value([4])
                        ->options(
                            AdminRole::where('slug', '!=', 'super-admin')
                                ->where('slug', '!=', 'admin')
                                ->get()
                                ->pluck('name', 'id')
                        )
                        ->readOnly()
                        ->rules('required');
                }
            }

            $u = Admin::user();
            $form->hidden('enterprise_id')->rules('required')->default($u->enterprise_id)
                ->value($u->enterprise_id);

            $form->disableCreatingCheck();
            $form->disableReset();
            $form->disableViewCheck();

            $form->hidden('user_type')->default('student')->value('student')->updateRules('required|max:223');

            $form->text('first_name')->rules('required');
            $form->text('given_name');
            $form->text('last_name');
            $form->text('lin', 'Student\'s LIN');

            $form->text('school_pay_payment_code');
            $form->text('school_pay_account_id');
            $form->radio('sex', 'Gender')->options(['Male' => 'Male', 'Female' => 'Female'])->rules('required');
            $form->radio('residence', 'Residence')
                ->options([
                    'DAY_SCHOLAR' => 'Day Scholar',
                    'BOARDER' => 'Boarder',
                ])
                ->default('DAY_SCHOLAR')
                ->rules('required');
            $form->date('date_of_birth', 'Date of birth');



            $active_academic_year = $u->ent->active_academic_year();
            if ($active_academic_year == null) {
                die("No active academic year");
            }

            $classes = [];
            foreach (
                AcademicClass::where([
                    'enterprise_id' => $u->enterprise_id,
                    'academic_year_id' => $active_academic_year->id,
                ])->get() as $class
            ) {
                if (((int)($class->academic_year->is_active)) != 1) {
                    continue;
                }
                $classes[$class->id] = $class->name_text;
            }



            if ($form->isCreating()) {
                $form->select('current_class_id', 'Class')->options($classes)->rules('required')
                    ->load(
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



                //change students class
                $form->radio('change_class', 'Do you want to change this student\'s class?')->options([
                    "Yes" => 'Yes',
                    "No" => 'No',
                ])->when('Yes', function (Form $form) {
                    $u = Admin::user();
                    $active_academic_year = $u->ent->active_academic_year();
                    if ($active_academic_year == null) {
                        die("No active academic year");
                    }
                    $classes = [];
                    foreach (
                        AcademicClass::where([
                            'enterprise_id' => $u->enterprise_id,
                            'academic_year_id' => $active_academic_year->id,
                        ])->get() as $class
                    ) {
                        if (((int)($class->academic_year->is_active)) != 1) {
                            continue;
                        }
                        $classes[$class->id] = $class->name_text;
                    }


                    $form->select('current_class_id', 'Class')->options($classes)->rules('required')
                        ->load(
                            'stream_id',
                            url('/api/streams?enterprise_id=' . $u->enterprise_id)
                        );
                    $form->select('stream_id', __('Stream'))->options(function ($id) {
                        $streams = [];
                        $u = Admin::user();
                        $active_academic_year = $u->ent->active_academic_year();
                        if ($active_academic_year == null) {
                            die("No active academic year");
                        }
                        $classes_ids = [];
                        foreach (
                            AcademicClass::where([
                                'enterprise_id' => $u->enterprise_id,
                                'academic_year_id' => $active_academic_year->id,
                            ])->get() as $class
                        ) {
                            if (((int)($class->academic_year->is_active)) != 1) {
                                continue;
                            }
                            $classes_ids[] = $class->id;
                        }


                        foreach (
                            AcademicClassSctream::where(
                                [
                                    'enterprise_id' => $u->enterprise_id,
                                ]
                            )
                                ->whereIn('academic_class_id', $classes_ids)
                                ->orderBy('id', 'desc')
                                ->get() as $ex
                        ) {
                            $streams[$ex->id] = $ex->name_text;
                        }
                        return $streams;
                    });
                });
            }
            $form->image('avatar', 'Student\'s photo')->uniqueName();
            $form->ignore('change_class');

            $form->divider();
            $form->radio('status')->options([
                2 => 'Pending',
                1 => 'Active',
                0 => 'Not active',
            ])
                ->rules('required')->default(2);

            /* $states = [
                'on' => ['value' => 1, 'text' => 'Verified', 'color' => 'success'],
                'off' => ['value' => 0, 'text' => 'Pending', 'color' => 'danger'],
            ];
            $form->switch('verification')->states($states)
                ->rules('required')->default(0); */
        });



        $form->tab('PERSONAL INFORMATION', function (Form $form) {

            $form->text('home_address');
            $form->text('current_address');
            $form->text('emergency_person_name', "Guardian name");
            $form->text('emergency_person_phone', "Guardian phone number");
            $form->text('phone_number_2', "Guardian phone number 2");

            $form->text('religion');
            $form->text('father_name', "Father's name");
            $form->text('father_phone', "Father's phone number");
            $form->text('mother_name', "Mother's name");
            $form->text('mother_phone', "Mother's phone number");
            $form->text('occupation', 'Guardian occupation');

            $form->text('nationality');
        });


        if (Admin::user()->isRole('dos')) {
            $form->tab('CLASSES', function (Form $form) {
                $form->morphMany('classes', 'CLASS HISTORY', function (Form\NestedForm $form) {
                    $form->html('Click on new to add this student to a class');
                    $u = Admin::user();
                    $form->hidden('enterprise_id')->default($u->enterprise_id);

                    $form->select('academic_class_id', 'Class')->options(function () {
                        return AcademicClass::where([
                            'enterprise_id' => Admin::user()->enterprise_id,
                        ])->get()->pluck('name_text', 'id');
                    })->load(
                        'stream_id',
                        url('/api/streams?enterprise_id=' . $u->enterprise_id)
                    )->readOnly();
                })
                    ->disableCreate()
                    ->disableDelete();
                $form->divider();
            });
        }

        if (Admin::user()->isRole('dos')) {
            $form->html('Click on new to add this student to a theology class');
            $form->tab('THEOLOGY CLASSES', function (Form $form) {
                $form->morphMany('theology_classes', null, function (Form\NestedForm $form) {

                    $u = Admin::user();
                    $form->hidden('enterprise_id')->default($u->enterprise_id);

                    $form->select('theology_class_id', 'Class')->options(function () {
                        return TheologyClass::where([
                            'enterprise_id' => Admin::user()->enterprise_id,
                        ])->get()->pluck('name', 'id');
                    });
                });
                $form->divider();
            });
        }


        if (Admin::user()->isRole('dos')) {
            $form->tab('SYSTEM ACCOUNT', function (Form $form) {

                $form->text('email', 'Email address');
                $form->text('username', 'Username');

                $form->password('password', trans('admin.password'));

                $form->saving(function (Form $form) {
                    if ($form->password && $form->model()->password != $form->password) {
                        $form->password = Hash::make($form->password);
                    }
                });
            });
        }



        return $form;
    }
}
