<?php

namespace App\Admin\Controllers;

use App\Models\AcademicClass;
use App\Models\DataExport;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class DataExportController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'DataExport';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new DataExport());
        $grid->disableBatchActions();
        $u = Admin::user();
        $grid->model()->where('enterprise_id', $u->enterprise_id)
            ->orderBy('id', 'desc');

        $grid->column('created_at', __('DATE'))
            ->display(function ($created_at) {
                return date('d-M-Y', strtotime($created_at));
            })->sortable();
        $grid->column('target_type', __('Target'))
            ->display(function ($target_type) {
                if ($target_type == 'employees') {
                    return 'All Employees';
                } elseif ($target_type == 'classes') {
                    return 'By Student\'s Classes';
                } elseif ($target_type == 'users') {
                    return 'By Specific Users';
                }
            });
        $grid->column('name', __('Populations'))
            ->display(function () {
                if ($this->target_type == 'classes') {
                    $classes = [];
                    foreach ($this->classes as $key => $v) {
                        $classes[] = AcademicClass::find($v)->name_text;
                    }
                    return implode(", ", $classes);
                } elseif ($this->target_type == 'users') {
                    /*  $users = [];
                    foreach ($this->users as $key => $v) {
                        $users[] = Administrator::find($v)->name;
                    }
                    return implode(", ", $users); */
                    return 'Specific Users';
                } else {
                    return 'All Employees';
                }
            });
        $grid->column('do_generate_pdf', __('Generate ZIP'))
            ->display(function ($do_generate_pdf) {
                $url = url('photos-zip-generation?id=' . $this->id);
                if ($this->pdf_generated == 'Yes') {
                    return "<a target='_blank' href='$url'>Re-Generate ZIP</a>";
                }
                return "<a target='_blank' href='$url'>Generate ZIP</a>";
            });
        //identification-cards-print
        $grid->column('file_link', __('DOWNLOAD ZIP'))
            ->display(function ($file_link) {
                //it zip not generated
                if ($file_link == null) {
                    return 'N/A';
                }
                $zip_url = url('storage/' . $file_link);
                return "<a 
                style='background-color: #000; color: #fff; padding: 10px; margin: 10px; text-decoration: none; border-radius: 5px;' 
                target='_blank' href='$zip_url'>DOWNLOAD ZIP</a>";
            });
        $grid->column('pdf_generated', __('ZIP Generated'));

        //template
        $grid->column('template', __('File Size'))
            ->display(function ($template) {
                return $template . ' MB';
            })->sortable();
        $grid->disableBatchActions();
        $grid->disableExport();
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
        $show = new Show(DataExport::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('target_type', __('Target type'));
        $show->field('classes', __('Classes'));
        $show->field('users', __('Users'));
        $show->field('file_link', __('File link'));
        $show->field('do_generate_pdf', __('Do generate pdf'));
        $show->field('pdf_generated', __('Pdf generated'));

        return $show;
    }

    protected function form()
    {
        $form = new Form(new DataExport());
        $u = Admin::user();
        $enterprise_id = $u->enterprise_id;
        $form->hidden('enterprise_id')->value($enterprise_id);

        $form->radio('target_type', __('Target Type'))
            ->options([
                'classes' => 'By Student\'s Classes',
                'users' => 'By Specific Users',
            ])->default('students')
            ->when('classes', function (Form $form) {
                $u = Admin::user();
                $ent = $u->enterprise;
                $year = $ent->active_academic_year();
                $classes = [];
                foreach (AcademicClass::where('academic_year_id', $year->id)->get() as $key => $class) {
                    $classes[$class->id] = $class->name_text;
                }
                $form->multipleSelect('classes', __('Classes'))->options($classes);
            })->when('users', function (Form $form) {
                $u = Admin::user();
                $ajax_url = url('/api/ajax-users?enterprise_id=' . $u->enterprise_id . "");
                $form->multipleSelect('users', "Select Users")
                    ->options(function ($ids) {
                        if (!is_array($ids)) {
                            return [];
                        }
                        $data = Administrator::whereIn('id', $ids)->pluck('name', 'id');
                        return $data;
                    })
                    ->ajax($ajax_url)->rules('required');
            })
            ->rules('required');

        if ($form->isCreating()) {
            $form->hidden('do_generate_pdf')->value('Yes')->default('Yes');
            $form->hidden('pdf_generated')->value('No')->default('No');
        } else {
            $form->radio('do_generate_pdf', __('Re-Generate pdf'))->default('No')
                ->options([
                    'Yes' => 'Yes',
                    'No' => 'No',
                ]);
        }


        return $form;
    }
}
