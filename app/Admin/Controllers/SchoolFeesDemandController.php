<?php

namespace App\Admin\Controllers;

use App\Models\AcademicClass;
use App\Models\SchoolFeesDemand;
use App\Models\Utils;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class SchoolFeesDemandController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'School Fees Demands';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new SchoolFeesDemand());
        $u = Admin::user();
        $grid->model()->where('enterprise_id', $u->enterprise_id)->orderBy('id', 'desc');

        $grid->column('id', __('Id'))->sortable();
        $grid->column('created_at', __('Created'))
            ->display(function () {
                return Utils::my_date($this->created_at);
            })
            ->sortable();
        $grid->column('description', __('Title'))->sortable();
        $grid->column('amount', __('Amount'));
        $grid->column('demand_notice', __('Demand Notice'))
            ->display(function ($f) {
                $url = url('generate-demand-notice?id=' . $this->id);
                return '<a href="' . $url . '" target="_blank">Generate Demand Notices</a>';
            });
        $grid->column('meal-cards', __('Meal Card'))
            ->display(function ($f) {
                $url = url('meal-cards?id=' . $this->id);
                return '<a href="' . $url . '" target="_blank">Generate Meal Cards</a>';
            });

        /*         $grid->column('message_1', __('Message 1'));
        $grid->column('message_2', __('Message 2'));
        $grid->column('message_3', __('Message 3'));
        $grid->column('message_4', __('Message 4'));
        $grid->column('message_5', __('Message 5')); */

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
        $show = new Show(SchoolFeesDemand::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('description', __('Description'));
        $show->field('amount', __('Amount'));
        $show->field('message_1', __('Message 1'));
        $show->field('message_2', __('Message 2'));
        $show->field('message_3', __('Message 3'));
        $show->field('message_4', __('Message 4'));
        $show->field('message_5', __('Message 5'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new SchoolFeesDemand());
        $u = Admin::user();
        $form->hidden('enterprise_id')->value($u->enterprise_id);
        $form->text('description', __('Title'))->rules('required');
        $form->decimal('amount', __('Balance'))->default(0)->rules('required');
        $form->quill('message_1', __('Demand Notice Template'))
            ->default('Dear Parent,<br>we write to inform you that your child <b>[STUDENT_NAME]</b> has an outstanding balance of UGX <b>[BALANCE_AMOUNT] - [STUDENT_CLASS]</b>.<br><b>[STUDENT_NAME]</b> will not be permitted in school unless this balance is cleared.')
            ->required();
        $form->textarea('message_2', __('SMS Template'))
            ->default('Dear Parent, you are reminded to clear the outstanding balance of UGX [BALANCE_AMOUNT] for your child [STUDENT_NAME]. Thank you.')
            ->required();
        $form->html('<code>[BALANCE_AMOUNT]</code> <code>[STUDENT_NAME]</code> <code>[STUDENT_CLASS]</code>', 'Keywords');


        $u = Admin::user();
        $year = $u->ent->active_academic_year();
        $academicClasses = AcademicClass::where([
            'enterprise_id' => $u->enterprise_id,
            'academic_year_id' => $year->id,
        ])
            ->orderBy('id', 'DESC')
            ->get();
        $classes = [];
        foreach ($academicClasses as  $v) {
            $classes[$v->id] = $v->name_text;
        }

        //message_4
        $form->date('message_4', __('Due Date'))->default(date('Y-m-d'))->rules('required');

        $form->multipleSelect('classes', 'Target Classes')
            ->options($classes);

        /*         $form->textarea('', __('Message 3'));
        $form->textarea('', __('Message 4'));
        $form->textarea('message_5', __('Message 5')); */

        return $form;
    }
}
