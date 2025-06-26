<?php

namespace App\Admin\Controllers;

use App\Models\Course;
use App\Models\UniversityProgramme;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Facades\Admin;
use App\Models\Enterprise;
use App\Models\Utils;

class UniversityProgrammeController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Programmes';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new UniversityProgramme());
        /*   $course = UniversityProgramme::find(1);
        $course->description .= '.';
        $course->save();

        dd($course); */

        // Scope to current enterprise & newest first
        $grid->model()
            ->where('enterprise_id', Admin::user()->enterprise_id)
            ->orderBy('created_at', 'desc');

        // Filters
        $grid->filter(function ($filter) {
            $filter->disableIdFilter();

            $filter->equal('status', 'Status')->select([
                'Active'   => 'Active',
                'Inactive' => 'Inactive',
            ]);
            $filter->between('created_at', 'Created At')->datetime();
        });

        $grid->quickSearch('name', 'code')
            ->placeholder('Search by name or code');
        $grid->column('id',      'ID')->sortable();
        $grid->column('name',    'Name')->limit(30);
        $grid->column('code',    'Code')->limit(10);
        $grid->column('description', 'Description')->limit(50);

        //total_semester_bills
        $grid->column('total_semester_bills', 'Total Bills')
            ->display(function ($val) {
                return number_format($val, 2);
            })->sortable();

        $grid->column('status',  'Status')->using([
            'Active'   => 'Active',
            'Inactive' => 'Inactive',
        ])->label([
            'Active'   => 'success',
            'Inactive' => 'danger',
        ]);
        $grid->column('created_at', 'Created')
            ->display(fn($v) => Utils::my_date_3($v))
            ->sortable();
        $grid->column('updated_at', 'Updated')
            ->display(fn($v) => Utils::my_date_3($v));

        //generate course fees structure
        $grid->column('fees_structure', 'Fees Structure PDF')->display(function () {
            $url = 'university-programmes-fees-structure?id=' . $this->id;
            return '<a href="' . $url . '" target="_blank" class="btn btn-xs btn-primary">Generate PDF</a>';
        });

        // disable batch delete
        $grid->disableBatchActions();


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
        $show = new Show(UniversityProgramme::findOrFail($id));

        $show->field('id',          'ID');
        $show->field('enterprise.name', 'Enterprise');
        $show->field('name',        'Name');
        $show->field('code',        'Code');
        $show->field('description', 'Description');
        $show->field('status',      'Status')
            ->as(fn($status) => ucfirst($status))
            ->label([
                'Active'   => 'success',
                'Inactive' => 'danger',
            ]);
        $show->field('created_at',  'Created At');
        $show->field('updated_at',  'Updated At');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new UniversityProgramme());

        // Auto-assign enterprise
        $form->hidden('enterprise_id')->value(Admin::user()->enterprise_id);

        $form->text('name',        'Name')
            ->rules('required|string|max:255');
        $form->text('code',        'Code')
            ->rules('required|string|max:50')
            ->help('Unique programme code.');
        $form->textarea('description', 'Description')
            ->rows(3)
            ->rules('nullable|string|max:1000');

        $form->radio('status',     'Status')
            ->options([
                'Active'   => 'Active',
                'Inactive' => 'Inactive',
            ])
            ->default('Active')
            ->rules('required');

        $form->radio('has_semester_1',     'Has Semester 1')
            ->options([
                'Yes'   => 'Yes',
                'No' => 'No',
            ])
            ->required()
            ->rules('required')
            ->when('Yes', function (Form $form) {
                $form->decimal('semester_1_bill', 'Semester 1 Tuition')
                    ->rules('required|numeric|min:0');
            });
        $form->radio('has_semester_2',     'Has Semester 2')
            ->options([
                'Yes'   => 'Yes',
                'No' => 'No',
            ])
            ->required()
            ->rules('required')
            ->when('Yes', function (Form $form) {
                $form->decimal('semester_2_bill', 'Semester 2 Tuition')
                    ->rules('required|numeric|min:0');
            });
        $form->radio('has_semester_3',     'Has Semester 3')
            ->options([
                'Yes'   => 'Yes',
                'No' => 'No',
            ])
            ->required()
            ->rules('required')
            ->when('Yes', function (Form $form) {
                $form->decimal('semester_3_bill', 'Semester 3 Tuition')
                    ->rules('required|numeric|min:0');
            });

        $form->radio('has_semester_4',     'Has Semester 4')
            ->options([
                'Yes'   => 'Yes',
                'No' => 'No',
            ])
            ->required()
            ->rules('required')
            ->when('Yes', function (Form $form) {
                $form->decimal('semester_4_bill', 'Semester 4 Tuition')
                    ->rules('required|numeric|min:0');
            });
        $form->radio('has_semester_5',     'Has Semester 5')
            ->options([
                'Yes'   => 'Yes',
                'No' => 'No',
            ])
            ->required()
            ->rules('required')
            ->when('Yes', function (Form $form) {
                $form->decimal('semester_5_bill', 'Semester 5 Tuition')
                    ->rules('required|numeric|min:0');
            });
        $form->radio('has_semester_6',     'Has Semester 6')
            ->options([
                'Yes'   => 'Yes',
                'No' => 'No',
            ])
            ->required()
            ->rules('required')
            ->when('Yes', function (Form $form) {
                $form->decimal('semester_6_bill', 'Semester 6 Tuition')
                    ->rules('required|numeric|min:0');
            });
        $form->radio('has_semester_7',     'Has Semester 7')
            ->options([
                'Yes'   => 'Yes',
                'No' => 'No',
            ])
            ->required()
            ->rules('required')
            ->when('Yes', function (Form $form) {
                $form->decimal('semester_7_bill', 'Semester 7 Tuition')
                    ->rules('required|numeric|min:0');
            });
        $form->radio('has_semester_8',     'Has Semester 8')
            ->options([
                'Yes'   => 'Yes',
                'No' => 'No',
            ])
            ->required()
            ->rules('required')
            ->when('Yes', function (Form $form) {
                $form->decimal('semester_8_bill', 'Semester 8 Tuition')
                    ->rules('required|numeric|min:0');
            });





        // prevent enterprise re-assignment
        $form->saving(function (Form $form) {
            $form->model()->enterprise_id = Admin::user()->enterprise_id;
        });

        // polish UI
        $form->disableReset();
        $form->footer(function ($footer) {
            // disable view/edit checkboxes
            $footer->disableViewCheck();
            // $footer->disableEditingCheck();
            // $footer->disableCreatingCheck(); 
        });

        return $form;
    }
}
