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
use App\Models\TheologyStream;
use App\Models\TheologySubject;
use App\Models\Participant;
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
        $u = Admin::user();
        $ent = $u->ent;
        if ($ent == null) {
            return $this->error('No enterprise found. Please contact your system administrator.');
        }

        $isUniversity = $ent->type == 'University';


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


            $ent = $u->ent;

            $active_term = $ent->active_term();
            if ($active_term == null) {
                return $this->error('Active term not found.');
            }

            //theology streams
            $theology_streams = [];
            $academic_class_ids = [];
            $academic_class = TheologyClass::where([
                'enterprise_id' => $u->enterprise_id,
                'academic_year_id' => $active_term->academic_year_id,
            ])->limit(10000)->orderBy('id', 'desc')->get();
            foreach ($academic_class as $key => $value) {
                $academic_class_ids[] = $value->id;
            }
            foreach (
                TheologyStream::wherein('theology_class_id', $academic_class_ids)
                    ->where([
                        'enterprise_id' => $u->enterprise_id,
                    ])->limit(10000)->orderBy('id', 'desc')->get() as $key => $value
            ) {
                $theology_streams[$value->id] = $value->name_text;
            }
            $filter->equal('theology_stream_id', 'Filter by Theology Stream')->select($theology_streams);


            $filter->equal('extension.student_sourced_by_agent', 'Sourced by Agent?')
                ->select(['Yes' => 'Yes', 'No' => 'No']);

            $agents = \App\Models\Utils::getUsersByRoleSlug('marketing-agent', $u->enterprise_id);
            $filter->equal('extension.student_sourced_by_agent_id', 'Agent')->select($agents);

            $filter->between('extension.student_sourced_by_agent_commission', 'Agent Commission');

            $filter->equal('extension.student_sourced_by_agent_commission_paid', 'Commission Paid?')
                ->select(['Yes' => 'Yes', 'No' => 'No']);

            // Remove the default id filter
            $filter->disableIdFilter();
        });



        //user_number column


        $grid->quickSearch('name', 'lin', 'school_pay_payment_code', 'user_number', 'phone_number_1', 'phone_number_2')
            ->placeholder("Search by name or LIN or school pay code or ID number");
        //on export, emergency_person_name as it is
        $grid->export(function ($export) {
            $export->originalValue([
                'emergency_person_name',
                'emergency_person_phone',
                'lin',
                'sex',
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
            ->sortable()
            ->hide();

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
        if ($isUniversity) {
            $grid->column('stream_id', __('Stream'))
                ->display(function () {
                    if ($this->stream == null) {
                        return 'No Stream';
                    }
                    return $this->stream->name;
                })->sortable()
                ->hide();
        } else {
            $grid->column('stream_id', __('Stream'))
                ->display(function () {
                    if ($this->stream == null) {
                        return 'No Stream';
                    }
                    return $this->stream->name;
                })->sortable();
        }




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
            })->sortable()
            ->hide();



        $grid->column('sex', __('Gender'))
            ->sortable()
            ->filter(['Male' => 'Male', 'Female' => 'Female'])
            ->editable('select', ['Male' => 'Male', 'Female' => 'Female']);

        if ($isUniversity) {
            $grid->column('emergency_person_name', __('Guardian'))
                ->hide()
                ->sortable()
                ->editable();
            $grid->column('emergency_person_phone', __('Guardian Phone'))->sortable()
                ->editable()->filter('like')
                ->hide();
            //is_enrolled
            $grid->column('is_enrolled', __('Enrolled'))
                ->filter([
                    'Yes' => 'Enrolled',
                    'No' => 'Not enrolled',
                ])
                ->sortable()
                ->display(function ($value) {
                    $url = null;
                    if ($value != 'Yes') {
                        $url = '<a target="_blank" href="' . admin_url('student-has-semeters/create?student_id=' . $this->getKey() . '') . '" title="Enroll Now"><i class="fa fa-edit"></i>  Enroll Now</a>';
                    }
                    if ($value == 'Yes') {
                        return '<span class="badge bg-success">Enrolled</span> <br> ';
                    } else {
                        return '<span class="badge bg-danger">Not enrolled</span> <br> ' . $url . '';
                    }
                });
        } else {
            $grid->column('emergency_person_name', __('Guardian'))
                ->sortable()
                ->editable()
                ->filter('like');
            $grid->column('emergency_person_phone', __('Guardian Phone'))->sortable()
                ->editable()
                ->filter('like');
        }






        $grid->column('phone_number_1', __('Phone number'))->hide();
        $grid->column('phone_number_2', __('Phone number 2'))->hide();
        $grid->column('email', __('Email'))->hide();
        $grid->column('date_of_birth', __('D.O.B'))->sortable()->hide();
        $grid->column('nationality', __('Nationality'))->sortable()->hide();

        $grid->column('place_of_birth', __('Address'))->sortable()->hide();
        $grid->column('home_address', __('Home address'))->hide();

        if ($ent->type != 'University') {
            $grid->column('lin', __('LIN'))->sortable()->editable()
                ->filter('like');
        } else {
            $grid->column('lin', __('LIN'))->sortable()->editable()
                ->filter('like')->hide();
            //has_account_info
            $grid->column('has_account_info', ('Has ShoolPay'))
                ->filter([
                    'Yes' => 'Yes',
                    'No' => 'No',
                ])
                ->sortable();
        }
        $grid->column('school_pay_payment_code', ('SchoolPay code'))->sortable()
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
            ->sortable()
            ->hide();
        $grid->column('documents', __('Print Documents'))
            ->display(function () {
                $admission_letter = url('print-admission-letter?id=' . $this->id);
                return '<a title="Print admission letter" href="' . $admission_letter . '" target="_blank">Admission letter</a>';
            });




        $grid->column('created_at', __('Admitted'))
            ->display(function ($date) {
                return Carbon::parse($date)->format('d-M-Y');
            })->hide()->sortable();

        $grid->column('user_number', __('Reg No.'))->sortable();

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
        $grid->column('extension.student_sourced_by_agent', 'Sourced by Agent?')
            ->display(function ($value) {
                return $value === 'Yes' ? 'Yes' : 'No';
            })
            ->hide();

        $grid->column('extension.student_sourced_by_agent_id', 'Agent')
            ->display(function ($value) {
                if (!$value) {
                    return '';
                }
                $agent = \App\Models\User::find($value);
                return $agent ? $agent->name : '';
            })
            ->hide()
            ->sortable();

        $grid->column('extension.student_sourced_by_agent_commission', 'Agent Commission')
            ->display(function ($value) {
                return $value ? number_format($value) : '';
            })
            ->hide();

        $grid->column('extension.student_sourced_by_agent_commission_paid', 'Commission Paid?')
            ->display(function ($value) {
                return $value === 'Yes' ? 'Yes' : 'No';
            })
            ->hide();
        return $grid;
    }

    /**
     * Get student attendance summary
     */
    private function getStudentAttendanceSummary($student_id, $enterprise_id, $term_id = null)
    {
        $start_date = request('att_start_date', now()->subMonths(3)->format('Y-m-d'));
        $end_date = request('att_end_date', now()->format('Y-m-d'));

        $query = Participant::where('participants.enterprise_id', $enterprise_id)
            ->where('participants.administrator_id', $student_id)
            ->whereBetween(DB::raw('DATE(participants.created_at)'), [$start_date, $end_date]);

        if ($term_id) {
            $query->where('participants.term_id', $term_id);
        }

        // Overall statistics
        $overall_stats = $query->selectRaw('
            SUM(CASE WHEN is_present = 1 THEN 1 ELSE 0 END) as total_present,
            SUM(CASE WHEN is_present = 0 THEN 1 ELSE 0 END) as total_absent,
            COUNT(*) as total_sessions
        ')->first();

        $overall_rate = $overall_stats->total_sessions > 0 
            ? ($overall_stats->total_present / $overall_stats->total_sessions) * 100 
            : 0;

        // Statistics by type
        $type_stats = Participant::where('participants.enterprise_id', $enterprise_id)
            ->where('participants.administrator_id', $student_id)
            ->whereBetween(DB::raw('DATE(participants.created_at)'), [$start_date, $end_date])
            ->when($term_id, function($q) use ($term_id) {
                return $q->where('participants.term_id', $term_id);
            })
            ->selectRaw('
                participants.type,
                SUM(CASE WHEN is_present = 1 THEN 1 ELSE 0 END) as present_count,
                SUM(CASE WHEN is_present = 0 THEN 1 ELSE 0 END) as absent_count,
                COUNT(*) as total_records
            ')
            ->groupBy('participants.type')
            ->get();

        $type_names = [
            'STUDENT_REPORT' => 'Student Report',
            'STUDENT_LEAVE' => 'Student Leave',
            'STUDENT_MEAL' => 'Meal Session',
            'CLASS_ATTENDANCE' => 'Class Attendance',
            'THEOLOGY_ATTENDANCE' => 'Theology Classes',
            'ACTIVITY_ATTENDANCE' => 'Activities',
        ];

        $by_type = $type_stats->map(function($item) use ($type_names) {
            $rate = $item->total_records > 0 
                ? ($item->present_count / $item->total_records) * 100 
                : 0;
            
            return [
                'type' => $item->type,
                'type_name' => $type_names[$item->type] ?? $item->type,
                'present' => $item->present_count,
                'absent' => $item->absent_count,
                'total' => $item->total_records,
                'rate' => round($rate, 1)
            ];
        })->toArray();

        return [
            'total_sessions' => $overall_stats->total_sessions ?? 0,
            'total_present' => $overall_stats->total_present ?? 0,
            'total_absent' => $overall_stats->total_absent ?? 0,
            'overall_rate' => round($overall_rate, 1),
            'by_type' => $by_type
        ];
    }

    /**
     * Get student attendance records
     */
    private function getStudentAttendanceRecords($student_id, $enterprise_id, $term_id = null)
    {
        $start_date = request('att_start_date', now()->subMonths(3)->format('Y-m-d'));
        $end_date = request('att_end_date', now()->format('Y-m-d'));
        $att_type = request('att_type');
        $att_status = request('att_status');

        $query = Participant::where('participants.enterprise_id', $enterprise_id)
            ->where('participants.administrator_id', $student_id)
            ->whereBetween(DB::raw('DATE(participants.created_at)'), [$start_date, $end_date])
            ->leftJoin('academic_classes as ac', 'participants.academic_class_id', '=', 'ac.id')
            ->leftJoin('theology_classes as tc', 'participants.academic_class_id', '=', 'tc.id')
            ->select([
                'participants.*',
                DB::raw('COALESCE(ac.name, tc.name) as academic_class_name')
            ]);

        if ($term_id) {
            $query->where('participants.term_id', $term_id);
        }

        if ($att_type) {
            $query->where('participants.type', $att_type);
        }

        if ($att_status !== null && $att_status !== '') {
            $query->where('participants.is_present', (int)$att_status);
        }

        return $query->orderByDesc('participants.created_at')->get();
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {

        $u = User::findOrFail($id);
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

        // Get attendance data for students
        $attendance_summary = null;
        $attendance_records = null;
        if ($u->user_type == 'student') {
            $attendance_summary = $this->getStudentAttendanceSummary($u->id, $u->enterprise_id, $term->id);
            $attendance_records = $this->getStudentAttendanceRecords($u->id, $u->enterprise_id, $term->id);
        }

        $tab->add('Bio', view('admin.dashboard.show-user-profile-bio', [
            'u' => $u,
            'active_term_transactions' => $active_term_transactions,
            'student_data' => $student_data,
            'attendance_summary' => $attendance_summary
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

        // Add Attendance tab for students
        if ($u->user_type == 'student') {
            $tab->add('Attendance', view('admin.dashboard.show-user-profile-attendance', [
                'u' => $u,
                'attendance_summary' => $attendance_summary,
                'attendance_records' => $attendance_records,
                'student_data' => $student_data
            ]));
        }

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
            $form->radio('extension.student_sourced_by_agent', 'Student sourced by marketing agent?')->options([
                'Yes' => 'Yes',
                'No' => 'No',
            ])->default('No')
                ->rules('required')
                ->when('Yes', function (Form $form) {
                    $u = Admin::user();
                    $agents = Utils::getUsersByRoleSlug('marketing-agent', $u->enterprise_id);
                    $form->select('extension.student_sourced_by_agent_id', 'Agent')
                        ->options($agents)->rules('required');
                    $form->decimal('extension.student_sourced_by_agent_commission', 'Commission')->default(0)
                        ->rules('required');
                    $form->radio('extension.student_sourced_by_agent_commission_paid', 'Commission paid?')
                        ->options(['Yes' => 'Yes', 'No' => 'No'])->default('No');
                });;

            /* 
                        $table->string('')->default('No')->nullable();
            $table->unsignedBigInteger('student_sourced_by_agent_id')->nullable();
            $table->integer('student_sourced_by_agent_commission')->nullable();
            $table->string('student_sourced_by_agent_commission_paid')->default('No')->nullable();
            */
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
