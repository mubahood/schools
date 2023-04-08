<?php

namespace App\Admin\Controllers;

use App\Models\AcademicClass;
use App\Models\AcademicYear;
use App\Models\AdminRole;
use App\Models\AdminRoleUser;
use App\Models\StudentHasClass;
use App\Models\Subject;
use App\Models\TheologyClass;
use App\Models\TheologySubject;
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

        $grid = new Grid(new Administrator());


        $grid->disableBatchActions();
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

            


            // Remove the default id filter
            $filter->disableIdFilter();
        });




        $grid->quickSearch('name')->placeholder("Search by name...");



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


        if (Admin::user()->isRole('dos')) {
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
        }






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
                    return '<span class="badge bg-danger">No Stream</span>';
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



        $grid->column('sex', __('Gender'))
            ->sortable()
            ->filter(['Male' => 'Male', 'Female' => 'Female']);
        $grid->column('emergency_person_name', __('Guardian'))
            ->hide()
            ->sortable();
        $grid->column('emergency_person_phone', __('Guardian Phone'))->hide()->sortable();


        $grid->column('phone_number_1', __('Phone number'))->hide();
        $grid->column('phone_number_2', __('Phone number 2'))->hide();
        $grid->column('email', __('Email'))->hide();
        $grid->column('date_of_birth', __('D.O.B'))->sortable()->hide();
        $grid->column('nationality', __('Nationality'))->sortable()->hide();

        $grid->column('place_of_birth', __('Address'))->sortable()->hide();
        $grid->column('home_address', __('Home address'))->hide();

        $grid->column('school_pay_payment_code', __('School pay payment code'))->sortable();

        $grid->column('parent_id', __('Parent'))
        ->display(function($x){
            if($this->parent == null){
                return $x;
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
        $tab->add('Bio', view('admin.dashboard.show-user-profile-bio', [
            'u' => $u
        ]));
        $tab->add('Classes', view('admin.dashboard.show-user-profile-classes', [
            'u' => $u
        ]));
        $tab->add('Services', view('admin.dashboard.show-user-profile-bills', [
            'u' => $u
        ]));
        $tab->add('Transactions', view('admin.dashboard.show-user-profile-transactions', [
            'u' => $u
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

            $form->text('school_pay_payment_code');
            $form->text('school_pay_account_id');
            $form->radio('sex', 'Gender')->options(['Male' => 'Male', 'Female' => 'Female'])->rules('required');



            $classes = [];
            foreach (AcademicClass::all() as $class) {
                if (((int)($class->academic_year->is_active)) != 1) {
                    continue;
                }
                $classes[$class->id] = $class->name_text;
            }

            $form->select('current_class_id', 'Class')->options($classes)->rules('required');

            $form->image('avatar', 'Student\'s photo');

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

            $form->text('nationality');
        });


        if (Admin::user()->isRole('dos')) {
            $form->tab('CLASSES', function (Form $form) {
                $form->morphMany('classes', 'CLASS ALLOCATION', function (Form\NestedForm $form) {
                    $form->html('Click on new to add this student to a class');
                    $u = Admin::user();
                    $form->hidden('enterprise_id')->default($u->enterprise_id);

                    $form->select('academic_class_id', 'Class')->options(function () {
                        return AcademicClass::where([
                            'enterprise_id' => Admin::user()->enterprise_id,
                        ])->get()->pluck('name_text', 'id');
                    })
                        ->rules('required')->load(
                            'stream_id',
                            url('/api/streams?enterprise_id=' . $u->enterprise_id)
                        );
                });
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
                $form->text('username', 'Username')
                    ->creationRules(["unique:admin_users"])
                    ->updateRules(["unique:admin_users,username,{{id}}"]);

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
