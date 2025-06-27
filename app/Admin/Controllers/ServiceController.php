<?php

namespace App\Admin\Controllers;

use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceSubscription;
use App\Models\Transaction;
use App\Models\UniversityProgramme;
use App\Models\User;
use App\Models\Utils;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ServiceController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Services';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Service());

        // Scope to current enterprise & newest first
        $grid->model()
            ->where('enterprise_id', Admin::user()->enterprise_id)
            ->orderBy('id', 'desc');

        // Disable actions we don’t need
        $grid->disableBatchActions();
        $grid->disableExport();
        $grid->actions(function ($actions) {
            $actions->disableView();
            $actions->disableDelete();
        });

        // Filters
        $grid->filter(function ($filter) {
            $filter->disableIdFilter();

            // By name
            $filter->like('name', 'Name');

            // By category
            $filter->equal('service_category_id', 'Category')
                ->select(
                    ServiceCategory::where('enterprise_id', Admin::user()->enterprise_id)
                        ->pluck('name', 'id')
                );

            // Compulsory?
            $filter->equal('is_compulsory', 'Compulsory')
                ->select(['Yes' => 'Yes', 'No' => 'No']);
        });

        // Quick search
        $grid->quickSearch('name')->placeholder('Search services...');

        // Columns
        $grid->column('id',        'ID')->sortable();
        $grid->column('name',      'Name')->sortable();
        $grid->column('service_category.name', 'Category')
            ->sortable()
            ->label('info');
        $grid->column('fee',       'Fee (UGX)')
            ->display(fn($fee) => number_format($fee))
            ->sortable();

        // Subscribers in current term
        $grid->column('subscribers', 'Subscribers')
            ->display(function () {
                $term = Admin::user()->ent->active_term();
                if (! $term) {
                    return 'N/A';
                }
                $count = ServiceSubscription::where([
                    'service_id'    => $this->id,
                    'due_term_id'   => $term->id,
                ])->count();

                return number_format($count);
            });

        // Total collected this term
        $grid->column('total_amount', 'Total Collected (UGX)')
            ->display(function () {
                $term = Admin::user()->ent->active_term();
                if (! $term) {
                    return 'N/A';
                }
                $sum = Transaction::where([
                    'service_id' => $this->id,
                    'term_id'    => $term->id,
                ])->sum('amount');

                return number_format(abs($sum));
            });

        // Compulsory flag
        $grid->column('is_compulsory', 'Compulsory')
            ->using(['Yes' => 'Yes', 'No' => 'No'])
            ->label([
                'Yes' => 'success',
                'No'  => 'default',
            ])
            ->sortable();

        // Creation time
        $grid->column('created_at', 'Created')
            ->display(fn($d) => Utils::my_date_3($d))
            ->sortable();

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
        $show = new Show(Service::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('name', __('Name'));
        $show->field('description', __('Description'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {

        $form = new Form(new Service());
        $u = Admin::user();
        $form->hidden('enterprise_id', __('Enterprise id'))->default($u->enterprise_id)->rules('required');
        $form->text('name', __('Name'))->rules('required');

        $form->select('service_category_id', 'Service category')
            ->options(ServiceCategory::where([
                'enterprise_id' => $u->enterprise_id
            ])->get()->pluck('name', 'id'))->rules('required');
        $form->text('fee', __('Fee'))->attribute('type', 'number')->rules('required');

        $form->textarea('description', __('Description'));

        $ent = $u->ent;
        if ($ent->type == 'University') {
            $form->radio('is_compulsory', __('Is Compulsory'))
                ->options([
                    'Yes' => 'Yes',
                    'No' => 'No',
                ])
                ->default('No')
                ->required()
                ->rules('required')
                ->when(
                    'Yes',
                    function ($form) {

                        /* 
                                    $table->string('is_compulsory_to_all_courses')->default('no');
            $table->string('is_compulsory_to_all_semesters')->default('no');
                        */

                        //is_compulsory_to_all_courses
                        $form->radio('is_compulsory_to_all_courses', __('Is Compulsory to All Courses'))
                            ->options([
                                'Yes' => 'Yes',
                                'No' => 'No',
                            ])
                            ->when(
                                'No',
                                function ($form) {
                                    $dropdown = Utils::get_dropdown(
                                        UniversityProgramme::class,
                                        null,
                                        'code',
                                        null,
                                    );
                                    $form->checkbox('applicable_to_courses', 'Select Programmes Applicable')
                                        ->stacked()
                                        ->options($dropdown ?? []);
                                }
                            );
                        //is_compulsory_to_all_semesters 
                        $form->radio('is_compulsory_to_all_semesters', __('Is Compulsory to All Semesters'))
                            ->options([
                                'Yes' => 'Yes',
                                'No' => 'No',
                            ])
                            ->when(
                                'No',
                                function ($form) {

                                    $form->checkbox('applicable_to_semesters', __('Applicable to semesters'))
                                        ->stacked()
                                        ->options([
                                            1 => '1st Semester',
                                            2 => '2nd Semester',
                                            3 => '3rd Semester',
                                            4 => '4th Semester',
                                            5 => '5th Semester',
                                            6 => '6th Semester',
                                            7 => '7th Semester',
                                            8 => '8th Semester',
                                        ]);
                                }
                            );
                    }
                );
        }

        //bill_existing_students
        $form->radio('bill_existing_students', __('Bill Existing Students'))
            ->options([
                'Yes' => 'Yes',
                'No' => 'No',
            ])
            ->default('No')
            ->required()
            ->rules('required');

        /* 
            $table->string('is_compulsory')->default('No');
            $table->text('applicable_to_courses')->nullable();
            $table->text('applicable_to_semesters')->nullable();
        */



        // $form->disableCreatingCheck(); 
        // $form->disableEditingCheck();
        $form->disableViewCheck();
        $form->disableReset();

        return $form;
    }
}
