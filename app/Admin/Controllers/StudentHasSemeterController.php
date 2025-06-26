<?php

namespace App\Admin\Controllers;

use App\Models\StudentHasSemeter;
use App\Models\Term;
use App\Models\AcademicYear;
use App\Models\Service;
use App\Models\User;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Facades\Admin;

class StudentHasSemeterController extends AdminController
{
    /**
     * Title for current resource.
     */
    protected $title = 'Student Semester Enrollments';

    protected function grid()
    {
        $grid = new Grid(new StudentHasSemeter());
        $grid->quickSearch('registration_number', 'schoolpay_code', 'pegpay_code')
            ->placeholder('Search by Reg #, SchoolPay Code or PegPay Code'); 

        // Scope to this user's enterprise
        $grid->model()
            ->where('enterprise_id', Admin::user()->enterprise_id)
            ->orderBy('created_at', 'desc');

        // "Process Enrollment" button
        $grid->tools(function ($tools) {
            $tools->append(
                '<a class="btn btn-sm btn-primary" target="_blank" href="'
                    . admin_url('process-students-enrollment')
                    . '">Process Enrollment</a>'
            );
        });

        // Columns
        $grid->column('id', 'ID')->sortable();
        $grid->column('student.name', 'Student')->sortable()
            ->display(function () {
                $url = admin_url('students/' . $this->student_id );
                return "<b><a href='{$url}' target='_blank'>{$this->student->name_text}</a></b>";
            });

        $grid->column('academic_year.name', 'Academic Year')->sortable();
        $grid->column('term.name_text', 'Semester')->sortable();
        $grid->column('year_name', 'Year of Study')->sortable();
        $grid->column('semester_name', 'Semester of Study')->sortable();

        $grid->column('set_fees_balance_amount', 'Balance')->sortable()
            ->display(function ($amount) {
                return 'UGX ' . number_format($amount);
            });
        $grid->column('services', 'Services')->display(function ($ids) {
            if (empty($ids)) {
                return '-';
            }
            $names = Service::whereIn('id', $ids)->pluck('name')->all();
            return implode(', ', $names);
        });
        $grid->column('registration_number', 'Reg #')->sortable();
        $grid->column('schoolpay_code', 'SchoolPay Code')->sortable();
        $grid->column('pegpay_code', 'PegPay Code')->sortable();
        $grid->column('enrolled_by.username', 'Enrolled By');
        $grid->column('is_processed', 'Processed')
            ->using(['Yes' => 'Yes', 'No' => 'No'])
            ->label([
                'Yes' => 'success',
                'No'  => 'danger',
            ])
            ->sortable()->hide();
        $grid->column('created_at', 'Created')->sortable()->hide();

        // Filters
        $grid->filter(function ($filter) {
            // remove default id filter
            $filter->disableIdFilter();

            // Student (ajax)
            $u = Admin::user();
            $ajaxUrl = url("/api/ajax-users?enterprise_id={$u->enterprise_id}&search_by_1=name&search_by_2=id&user_type=student&model=User");
            $filter->equal('student_id', 'Student')
                ->select(function ($id) {
                    $u = User::find($id);
                    return $u ? [$u->id => $u->name_text] : [];
                })
                ->ajax($ajaxUrl);

            // Term & Academic Year
            $filter->equal('term_id', 'Semester')
                ->select(Term::getItemsToArray(['enterprise_id' => $u->enterprise_id]));
            $filter->equal('academic_year_id', 'Academic Year')
                ->select(
                    AcademicYear::where('enterprise_id', $u->enterprise_id)
                        ->pluck('name', 'id')
                );

            // Year & Semester of study
            $filter->equal('year_name', 'Year of Study')
                ->select([
                    1 => 'Year 1',
                    2 => 'Year 2',
                    3 => 'Year 3',
                    4 => 'Year 4',
                ]);
            $filter->equal('semester_name', 'Semester of Study')
                ->select([
                    1 => '1st Sem',
                    2 => '2nd Sem',
                    3 => '3rd Sem',
                    4 => '4th Sem',
                    5 => '5th Sem',
                    6 => '6th Sem',
                    7 => '7th Sem',
                    8 => '8th Sem',
                ]);

            // Registration & payment codes
            $filter->like('registration_number', 'Reg #');
            $filter->like('schoolpay_code', 'SchoolPay Code');
            $filter->like('pegpay_code', 'PegPay Code');;
            // Created date
            $filter->between('created_at', 'Created At')->datetime();
        });

        return $grid;
    }

    /**
     * Detail view.
     */
    protected function detail($id)
    {
        $show = new Show(StudentHasSemeter::findOrFail($id));

        $show->field('id', 'ID');
        $show->field('enterprise.name', 'Enterprise');
        $show->field('student.name', 'Student');
        $show->field('term.name_text', 'Term');
        $show->field('academic_year.name', 'Academic Year');
        $show->field('year_name', 'Year');
        $show->field('semester_name', 'Semester');
        $show->field('update_fees_balance', 'Update Fees')->as(fn($v) => ucfirst($v));
        $show->field('set_fees_balance_amount', 'Balance Amount');
        $show->field('services', 'Services');
        $show->field('registration_number', 'Reg #');
        $show->field('schoolpay_code', 'SchoolPay Code');
        $show->field('pegpay_code', 'PegPay Code');
        $show->field('enrolled_by.username', 'Enrolled By');
        $show->field('is_processed', 'Processed')->as(fn($v) => $v ? 'Yes' : 'No');
        $show->field('remarks', 'Remarks');
        $show->field('created_at', 'Created at');
        $show->field('updated_at', 'Updated at');

        return $show;
    }


    /**
     * Create / edit form.
     */
    protected function form()
    {
        /* $id = 1;
        $rec = StudentHasSemeter::find($id);
        $rec = StudentHasSemeter::do_process($rec);
        dd($rec); */
        $form = new Form(new StudentHasSemeter());

        // Auto-assign enterprise & enrolling user
        $form->hidden('enterprise_id')->default(Admin::user()->enterprise_id);
        $form->hidden('enrolled_by_id')->default(Admin::user()->id);
        $u = Admin::user();
        $ajax_url = url(
            '/api/ajax-users?'
                . 'enterprise_id=' . $u->enterprise_id
                . "&search_by_1=name"
                . "&search_by_2=id"
                . "&user_type=student"
                . "&model=User"
        );
        $ent = $u->ent;
        $user_number = null;
        $school_pay_payment_code = null;
        $pegpay_code = null;
        $active_term = $ent->active_term();
        if (!$active_term) {
            throw new \Exception("No active term found for this enterprise.");
        }

        $form->divider('Student Information');
        if ($form->isCreating()) {
            $user = null;
            $student_id = request()->get('student_id');
            if ($student_id != null && is_numeric($student_id)) {
                $user = User::find($student_id);
            }

            //display
            if ($user) {
                $form->display('student_name', 'Student')
                    ->with(function () use ($user) {
                        return $user->name_text;
                    });
                //hidden field for student_id
                $user_number = $user->user_number;
                $school_pay_payment_code = $user->school_pay_payment_code;
                $pegpay_code = $user->pegpay_code;

                $form->hidden('student_id')->default($user->id);
            } else {
                $form->select('administrator_id', __("Student"))
                    ->options(function ($id) {
                        $a = Administrator::find($id);
                        if ($a) {
                            return [$a->id => "#" . $a->id . " - " . $a->name_text];
                        }
                    })
                    ->ajax($ajax_url)->rules('required');
            }

            $form->hidden('term_id')->default($active_term->id);
            $form->hidden('academic_year_id')
                ->default($active_term->academic_year_id);

            //display the academic year name
            $form->display('academic_year.name', 'Academic Year')
                ->with(function () use ($active_term) {
                    return $active_term->academic_year->name;
                });
            //display the term name
            $form->display('term.name_text', 'Semester')
                ->with(function () use ($active_term) {
                    return 'Semester ' . $active_term->name_text;
                });
        } else {
            $record_id = request()->route('student_has_semeter');
            $record = StudentHasSemeter::find($record_id);
            if ($record != null) {
                $user = $record->student;

                if ($user != null) {
                    // For editing, just show the student name
                    $form->display('student_name_text', 'Student')
                        ->with(function () use ($user) {
                            return $user->name_text;
                        });

                    $form->display('academic_year.name', 'Academic Year')
                        ->with(function () use ($record) {
                            return $record->academic_year->name;
                        });
                    //display the term name
                    $form->display('term.name_text', 'Semester')
                        ->with(function () use ($record) {
                            return 'Semester ' . $record->term->name_text;
                        });
                }
            }
        }

        $form->text('registration_number', 'Registration Number')->default($user_number);
        $form->text('schoolpay_code', 'SchoolPay Code')
            ->rules('required|nullable|max:50')
            ->required()
            ->default($school_pay_payment_code);
        $form->text('pegpay_code', 'PegPay Code')
            ->default($pegpay_code);

        $form->divider('Enrollment Details');

        // Free-text labels
        $form->radio('year_name', 'Year of Study')
            ->options([
                1 => 'Year 1',
                2 => 'Year 2',
                3 => 'Year 3',
                4 => 'Year 4',
            ])
            ->required()
            ->rules('required');
        $form->radio('semester_name', 'Semester of Study')
            ->options([
                1 => '1st Semester',
                2 => '2nd Semester',
                3 => '3rd Semester',
                4 => '4th Semester',
                5 => '5th Semester',
                6 => '6th Semester',
                7 => '7th Semester',
                8 => '8th Semester',
            ])
            ->required()
            ->rules('required');



        // Yes/No on fees update
        $form->hidden('update_fees_balance', 'Update Fees Balance?')
            ->options(['Yes' => 'Yes', 'No' => 'No'])
            ->default('Yes');

        $form->divider('Fees Billing');



        $form->decimal('set_fees_balance_amount', 'Last semester balance')
            ->help('Set the last semester balance amount. This will be used to update the fees balance for the student.')
            ->rules('required|numeric|min:0')
            ->required();

        $services = [];
        foreach (
            Service::where(
                'enterprise_id',
                Admin::user()->enterprise_id
            )->get() as $service
        ) {
            $services[$service->id] = $service->name_text;
        }

        $form->checkbox('services', 'Services')
            ->stacked()
            ->options($services);


        if ($form->isCreating()) {
            $form->hidden('is_processed', 'Processed')
                ->default('No');
        } else {
            $form->radio('is_processed', 'Do you want to process this enrollment?')
                ->options([
                    'Yes' => 'No',
                    'No'  => 'Yes',
                ]);
        }


        $form->disableCreatingCheck();
        $form->disableViewCheck();
        $form->disableReset();

        return $form;
    }
}
