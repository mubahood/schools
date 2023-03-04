<?php

namespace App\Admin\Controllers;

use App\Models\AcademicYear;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Auth;

class AcademicYearController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Academic year';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        /*  $ac = Academicyear::find(3);
        Academicyear::generate_classes($ac);
        dd($ac); */
        /*
        $ac = Academicyear::find(3);
        $ac->details .= '1';
        $ac->is_active = 1;  
        $ac->save(); 
        */


        $grid = new Grid(new AcademicYear());
        $grid->disableBatchActions();
        $grid->model()->where([
            'enterprise_id' => Admin::user()->enterprise_id,

        ])
            ->orderBY('id', 'desc');

        $grid->column('name', __('Name'));
        $grid->column('starts', __('Starts'));
        $grid->column('ends', __('Ends'));
        $grid->column('is_active', __('Status'))->display(function ($is_active) {
            if ($is_active) {
                return "Active";
            } else {
                return "Not Active";
            }
        })->label();

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
        $show = new Show(AcademicYear::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('name', __('Name'));
        $show->field('starts', __('Starts'));
        $show->field('ends', __('Ends'));
        $show->field('details', __('Details'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        /*  $u = Admin::user(); 
        $m = AcademicYear::where('id',2)->first();
        $m->name .= rand(1,100).""; 
      
        $m->is_active = 1;
        $m->save();
        die("Romina");   */
        $u = Admin::user();
        $form = new Form(new AcademicYear());

        $form->disableCreatingCheck();
        $form->disableEditingCheck();
        $form->disableReset();
        $form->disableViewCheck();


        $form->hidden('enterprise_id', __('Enterprise id'))->default($u->enterprise_id)->rules('required');
        $form->text('name', __('Name'))->rules('required');
        $form->date('starts', __('Starts'))->default(date('Y-m-d'))->rules('required');
        $form->date('ends', __('Ends'))->default(date('Y-m-d'))->rules('required');
        $form->textarea('details', __('Details'));
        $form->radio('is_active', __('is_active'))->options([
            1 => 'Set as current year',
            0 => 'Set as Not current year',
        ])->when(1, function ($f) {

            $f->html('<div class="alert alert-danger"> <b>Warning!</b> Make sure you select the right purpose to why you need to switch academic years.</div>', 'Warning');
            $f->radio('process_data', __('Why do you want to switch academic years?'))
                ->options([
                    'No' => 'To just view data',
                    'Yes' => 'To process everything, promote students, create new classes, deactivate studnets in candidate classes.',
                ])
                ->default('No')
                ->rules('required');
        });
        $form->password(
            'password',
            'Enter your password to confirm this process.'
        )
            ->help('You cannot reverse this proess.')
            ->rules('required');


        $form->saving(function (Form $f) {


            $u = Auth::user();
            $errors = [];
            if (
                (!$u->isRole('admin')) ||
                (!$u->isRole('dos'))
            ) {
                $errors['password'] = 'Only system admin can perform this action.';
            }

            if (
                !password_verify($_POST['password'], $u->password)
            ) {
                $errors['password'] = 'You entered wrong password. Enter correct pasword and try again.';
            }

            if (!empty($errors)) {
                return back()->withInput()->withErrors($errors);
            }
        });

        $form->ignore(['password']);
        return $form;
    }
}
