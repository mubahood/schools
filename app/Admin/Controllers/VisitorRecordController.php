<?php

namespace App\Admin\Controllers;

use App\Models\User;
use App\Models\VisitorRecord;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class VisitorRecordController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Visitors\'s Records';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new VisitorRecord());
        $grid->disableBatchActions();
        $u = Admin::user();
        $grid->quickSearch('name', 'phone_number')->placeholder('Search by name or phone number');
        $grid->model()->where('enterprise_id', $u->enterprise_id)
            ->orderBy('created_at', 'desc');
        $grid->column('created_at', __('Date'))
            ->display(function ($created_at) {
                return date('d-m-Y', strtotime($created_at));
            })->sortable();
        $grid->column('name', __('Name'))->sortable();
        $grid->column('phone_number', __('Phone number'))->sortable();
        $grid->column('address', __('Address'))->sortable();
        $grid->column('purpose', __('Purpose'))->sortable()
            ->label([
                'Official' => 'success',
                'Staff' => 'info',
                'Student' => 'warning',
                'Other' => 'danger',
            ])
            ->filter([
                'Official' => 'Official',
                'Staff' => 'Staff',
                'Student' => 'Student',
                'Other' => 'Other',
            ])
            ->sortable();
        $grid->column('purpose_staff_id', __('Host'))
            ->display(function ($purpose_staff_id) {
                $staff = User::find($purpose_staff_id);
                if ($staff != null) {
                    return $staff->name;
                }
                $student = User::find($this->purpose_student_id);
                if ($student != null) {
                    return $student->name_text;
                }

                if ($this->purpose == 'Official') {
                    return $this->purpose_office;
                }
                return $this->purpose_other;
            });
        $grid->column('purpose_student_id', __('Visted Student'))
            ->display(function ($purpose_student_id) {
                $student = User::find($purpose_student_id);
                if ($student != null) {
                    return $student->name_text;
                }
                return '';
            })->sortable()->hide();
        $grid->column('organization', __('Organization'))->sortable()->hide();
        $grid->column('email', __('Email'))->hide();
        $grid->column('nin', __('Nin'))->hide();
        $grid->column('status', __('Check-out Status'))
            ->dot([
                'In' => 'danger',
                'Out' => 'success',
            ])->sortable();
        $grid->column('check_in_time', __('Check-in Time'))->sortable();
        $grid->column('check_out_time', __('Check-out Time'))->sortable();
        $grid->column('purpose_description', __('Purpose description'));
        $grid->column('purpose_office', __('Purpose office'))->sortable()->hide();

        $grid->column('signature_src', __('Sign'))
            ->lightbox(['width' => 50, 'height' => 50])->width(50)->sortable();
        $grid->column('car_reg', __('Vevicle Reg'))->sortable()
            ->filter('like')
            ->display(function ($car_reg) {
                if ($car_reg != null && strlen($car_reg) > 3) {
                    return $car_reg;
                }
                return '-';
            })->hide();


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
        $show = new Show(VisitorRecord::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('visitor_id', __('Visitor id'));
        $show->field('purpose_staff_id', __('Purpose staff id'));
        $show->field('purpose_student_id', __('Purpose student id'));
        $show->field('name', __('Name'));
        $show->field('phone_number', __('Phone number'));
        $show->field('organization', __('Organization'));
        $show->field('email', __('Email'));
        $show->field('address', __('Address'));
        $show->field('nin', __('Nin'));
        $show->field('check_in_time', __('Check in time'));
        $show->field('check_out_time', __('Check out time'));
        $show->field('purpose', __('Purpose'));
        $show->field('purpose_description', __('Purpose description'));
        $show->field('purpose_office', __('Purpose office'));
        $show->field('purpose_other', __('Purpose other'));
        $show->field('signature_src', __('Signature src'));
        $show->field('signature_path', __('Signature path'));
        $show->field('lacal_id', __('Lacal id'));
        $show->field('has_car', __('Has car'));
        $show->field('car_reg', __('Car reg'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new VisitorRecord());

        $form->number('visitor_id', __('Visitor id'));
        $form->number('purpose_staff_id', __('Purpose staff id'));
        $form->number('purpose_student_id', __('Purpose student id'));
        $form->text('name', __('Name'));
        $form->text('phone_number', __('Phone number'));
        $form->text('organization', __('Organization'));
        $form->email('email', __('Email'));
        $form->text('address', __('Address'));
        $form->text('nin', __('Nin'));
        $form->date('check_in_time', __('Check in time'))->default(date('Y-m-d'));
        $form->date('check_out_time', __('Check out time'))->default(date('Y-m-d'));
        $form->text('purpose', __('Purpose'));
        $form->textarea('purpose_description', __('Purpose description'));
        $form->textarea('purpose_office', __('Purpose office'));
        $form->textarea('purpose_other', __('Purpose other'));
        $form->textarea('signature_src', __('Signature src'));
        $form->textarea('signature_path', __('Signature path'));
        $form->text('lacal_id', __('Lacal id'));
        $form->text('has_car', __('Has car'));
        $form->text('car_reg', __('Car reg'));

        return $form;
    }
}
