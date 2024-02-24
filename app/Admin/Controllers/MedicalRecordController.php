<?php

namespace App\Admin\Controllers;

use App\Models\Disease;
use App\Models\MedicalRecord;
use App\Models\Term;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class MedicalRecordController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Medical Records';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new MedicalRecord());
        $grid->disableBatchActions();
        $u = Admin::user();
        $grid->model()->where('enterprise_id', $u->enterprise_id)
            ->orderBy('id', 'desc');


        //filter
        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $u = Admin::user();
            $ajax_url = url(
                '/api/ajax-users?'
                    . 'enterprise_id=' . $u->enterprise_id
                    . "&search_by_1=name"
                    . "&search_by_2=id"
                    . "&user_type=student"
                    . "&model=User"
            );
            $filter->equal('patient_id', 'Patient')
                ->select(function ($id) {
                    $a = Administrator::find($id);
                    if ($a) {
                        return [$a->id => $a->name];
                    }
                })
                ->ajax($ajax_url);

            $terms = [];
            $active_term = 0;
            foreach (Term::where(
                'enterprise_id',
                Admin::user()->enterprise_id
            )->orderBy('id', 'desc')->get() as $key => $term) {
                $terms[$term->id] = "Term " . $term->name . " - " . $term->academic_year->name;
                if ($term->is_active) {
                    $active_term = $term->id;
                }
            }
            $filter->equal('term_id', 'Fliter by term')->select($terms);

            $filter->equal('has_disease', 'Results')
                ->radio([
                    'Positive' => 'Positive',
                    'Negative' => 'Negative',
                ]);
        });

        $grid->column('created_at', __('Date'))
            ->display(function ($d) {
                return date('d M, Y', strtotime($d));
            })->sortable();

        $grid->column('patient_id', __('Patient'))
            ->display(function ($id) {
                $a = Administrator::find($id);
                if ($a) {
                    return "#" . $a->id . " - " . $a->name;
                }
            })->sortable();

        $grid->column('age', __('Age'))->sortable();
        $grid->column('weight', __('Weight'))->sortable();
        $grid->column('height', __('Height'))->sortable()->hide();
        $grid->column('blood_group', __('Blood Group'))->sortable();
        $grid->column('blood_pressure', __('Blood pressure'))->sortable()->hide();

        $grid->column('symptoms', __('Symptoms'))->limit(20)->sortable();
        $grid->column('disease_id', __('Disease'))
            ->display(function ($id) {
                $a = Disease::find($id);
                if ($a) {
                    return $a->name;
                }
                return "N/A";
            })->sortable();

        $grid->column('other_diseases', __('Other Diseases'))
            ->display(function ($ids) {
                $a = Disease::whereIn('id', $ids)->get();
                if ($a) {
                    return $a->pluck('name')->implode(', ');
                }
                return "N/A";
            })->sortable()
            ->hide();

        $grid->column('administered_drugs', __('Administered Drugs'))->hide();
        $grid->column('recommended_drugs', __('Recommended drugs'))->hide();
        $grid->column('specialist_instructions', __('Specialist instructions'))->hide();
        $grid->column('specialist_remarks', __('Specialist remarks'));
        $grid->column('has_disease', __('Results'))
            ->dot([
                'Positive' => 'danger',
                'Negative' => 'success',
            ]);

        $grid->column('posted_by_id', __('Created by'))
            ->display(function ($id) {
                $a = Administrator::find($id);
                if ($a) {
                    return $a->name;
                }
            })->sortable();
        $grid->column('term_id', __('Term'))
            ->display(function ($t) {
                return "Term " . $this->term->name_text;
            })->sortable()
            ->hide();

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
        $show = new Show(MedicalRecord::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('academic_year_id', __('Academic year id'));
        $show->field('term_id', __('Term id'));
        $show->field('posted_by_id', __('Posted by id'));
        $show->field('patient_id', __('Patient id'));
        $show->field('disease_id', __('Disease id'));
        $show->field('age', __('Age'));
        $show->field('weight', __('Weight'));
        $show->field('height', __('Height'));
        $show->field('blood_group', __('Blood group'));
        $show->field('blood_pressure', __('Blood pressure'));
        $show->field('other_diseases', __('Other diseases'));
        $show->field('administered_drugs', __('Administered drugs'));
        $show->field('symptoms', __('Symptoms'));
        $show->field('recommended_drugs', __('Recommended drugs'));
        $show->field('specialist_instructions', __('Specialist instructions'));
        $show->field('specialist_remarks', __('Specialist remarks'));
        $show->field('has_disease', __('Has disease'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new MedicalRecord());
        $u = Admin::user();
        $form->hidden('enterprise_id', __('Enterprise id'))
            ->default($u->enterprise_id);
        $form->hidden('posted_by_id', __('Posted by id'))->default($u->id);

        $form->divider('Patient Details');

        $ajax_url = url(
            '/api/ajax-users?'
                . 'enterprise_id=' . $u->enterprise_id
                . "&search_by_1=name"
                . "&search_by_2=id"
                . "&user_type=student"
                . "&model=User"
        );

        $form->select('patient_id', "Select Patient")
            ->options(function ($id) {
                $a = Administrator::find($id);
                if ($a) {
                    return [$a->id => "#" . $a->id . " - " . $a->name];
                }
            })
            ->ajax($ajax_url)->rules('required');

        $form->decimal('age', __('Age'))->rules('required');
        $form->decimal('weight', __('Weight (Kg)'));
        $form->decimal('height', __('Height'));

        $form->radio('blood_group', __('Blood Group'))
            ->options([
                'A+' => 'A+',
                'A-' => 'A-',
                'B+' => 'B+',
                'B-' => 'B-',
                'AB+' => 'AB+',
                'AB-' => 'AB-',
                'O+' => 'O+',
                'O-' => 'O-',
            ])
            ->default('O+');
        $form->text('blood_pressure', __('Blood Pressure (mmHg)'));

        $form->divider('Disease Diagnosis');

        $form->textarea('symptoms', __('Symptoms'))->rules('required');

        $diseases = Disease::where('enterprise_id', $u->enterprise_id)
            ->orderBy('name', 'asc')
            ->get();

        $form->radio('has_disease', __('Does the patient have any disease?'))
            ->options([
                'Positive' => 'Positive',
                'Negative' => 'Negative',
            ])
            ->default('No')
            ->rules('required')
            ->when('Positive', function ($form) use ($diseases) {
                $form->select('disease_id', __('Select Disease Diagnosis'))
                    ->options($diseases->pluck('name', 'id'))
                    ->rules('required');

                $form->multipleSelect('other_diseases', __('Other Diseases'))
                    ->options($diseases->pluck('name', 'id'));
            });

        $form->divider('Treatment');

        $form->textarea('administered_drugs', __('Administered drugs'));
        $form->textarea('recommended_drugs', __('Recommended drugs'));
        $form->textarea('specialist_instructions', __('Specialist instructions'));
        $form->textarea('specialist_remarks', __('Specialist remarks'));
        $form->decimal('cost', __('Estimated cost of treatment'))->rules('required');


        $form->disableCreatingCheck();
        $form->disableViewCheck();
        $form->disableReset();


        return $form;
    }
}
