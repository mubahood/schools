<?php

namespace App\Admin\Controllers;

use App\Models\AcademicClass;
use App\Models\SchoolFeesDemand;
use App\Models\User;
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
        $grid->column('amount', __('Amount (UGX)'))
            ->display(function ($f) {
                return $this->direction . " " . number_format($f);
            })
            ->sortable();

        // classes
        $grid->column('classes', __('Target Classes'))
            ->display(function ($f) {
                $classes = [];
                foreach ($f as $v) {
                    $classes[] = AcademicClass::find($v)->name_text;
                }
                return implode(', ', $classes);
            });
        $grid->column('target_type', __('Residence'))->sortable()
            ->label([
                'DAY_SCHOLAR' => 'success',
                'BOARDER' => 'info',
                'ALL' => 'warning',
            ])->filter([
                'DAY_SCHOLAR' => 'Day Scholar',
                'BOARDER' => 'Boarder',
                'ALL' => 'All',
            ])->sortable();

        $grid->column('demand_notice', __('Demand Notice'))
            ->display(function ($f) {
                $salt = '&salt=' . rand(1000, 9999) . time();
                $url = url('generate-demand-notice?id=' . $this->id) . $salt;
                return '<a href="' . $url . '" target="_blank">Generate Demand Notices</a>';
            });
        $grid->column('meal-cards', __('Meal Card'))
            ->display(function ($f) {
                $url = url('meal-cards?id=' . $this->id . '&type=MEAL_CARD') . '&salt=' . rand(1000, 9999) . time();
                return '<a href="' . $url . '" target="_blank">Generate Meal Cards for (' . $this->target_type . ')</a>';
            });


        $grid->column('gate-pass', __('Gate Pass'))
            ->display(function ($f) {
                $url = url('meal-cards?id=' . $this->id . '&type=GATE_PASS') . '&salt=' . rand(1000, 9999) . time();
                return '<a href="' . $url . '" target="_blank">Generate GATE-PASS for (' . $this->target_type . ')</a>';
            });


        $grid->column('meal-cards-3', __('List of Students'))
            ->display(function ($f) {
                $url = url('meal-cards?id=' . $this->id . '&type=LIST') . '&salt=' . rand(1000, 9999) . time();
                return '<a href="' . $url . '" target="_blank">Generate List</a>';
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
        $form->decimal('amount', __('Balance'))
            ->rules([
                'required',
                'numeric',
            ])
            ->required();
        $form->radio('direction', 'Direction')->options([
            '<=' => 'Less than or equal to (<=)',
            '>=' => 'Greater than or equal to (>=)',
            '=' => 'Equal to (=)',
            '>' => 'Greater than (>)',
            '<' => 'Less than (<)',
        ])->rules('required')->required();

        //ADD [SCHOOL_PAY_CODE]
        $form->quill('message_1', __('Demand Notice Template'))
            ->default('Dear Parent,<br>We write to inform you that your child <b>[STUDENT_NAME] - [STUDENT_CLASS]</b> has an outstanding balance of UGX <b>[BALANCE_AMOUNT]</b>. 
            We request you to clear the balance to avoid inconvenience.<br>You can pay using Mobile Money through <b>School Pay Gateway.</b> Use the code <b>[SCHOOL_PAY_CODE]</b><br>Thank you.')
            ->required();
        $form->textarea('message_2', __('SMS Template'))
            ->default('Dear Parent, you are reminded to clear the outstanding balance of UGX [BALANCE_AMOUNT] for your child [STUDENT_NAME]. Thank you.')
            ->required();
        //ADD SCHOOL_PAY_CODE
        $form->html('<code>[BALANCE_AMOUNT]</code> <code>[STUDENT_NAME]</code> <code>[STUDENT_CLASS]</code> <code>[SCHOOL_PAY_CODE]</code>')->help('Use the following placeholders in your message template.')->required();

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
            ->options($classes)
            ->rules('required')
            ->required();

        //target_type borders or day scholars
        $form->radio('target_type', 'Target Residence Type')->options([
            'BOARDER' => 'Boarders',
            'DAY_SCHOLAR' => 'Day Scholars',
            'ALL' => 'All'
        ])->rules('required')->required();


        $form->radio('has_specific_students', __('Specific Students'))->options([
            'Yes' => 'Yes',
            'No' => 'No',
        ])->default('No')->required()
            ->when('Yes', function (Form $form) {
                $u = Admin::user();
                $ajax_url = url('/api/ajax-users?enterprise_id=' . $u->enterprise_id . "");
                $form->multipleSelect('target_students', "Select Students")
                    ->options(function ($ids) {
                        if (!is_array($ids)) {
                            return [];
                        }
                        $data = User::whereIn('id', $ids)->pluck('name', 'id');
                        return $data;
                    })
                    ->ajax($ajax_url)->rules('required');
            })
            ->rules('required');

        /*         $form->textarea('', __('Message 3'));
        $form->textarea('', __('Message 4'));
        $form->textarea('message_5', __('Message 5')); */

        /*             $table->string('has_range')->default('No')->nullable();
            $table->integer('min_range')->nullable();
            $table->integer('max_range')->nullable(); */
        $form->radio('has_range', 'Has Range')->options([
            'Yes' => 'Yes',
            'No' => 'No',
        ])->default('No')->required()
            ->when('Yes', function (Form $form) {
                $form->decimal('min_range', 'Min Range')->rules('required');
                $form->decimal('max_range', 'Max Range')->rules('required');
            })
            ->rules('required');

        return $form;
    }
}
