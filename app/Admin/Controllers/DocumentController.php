<?php

namespace App\Admin\Controllers;

use App\Models\Document;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Auth;

class DocumentController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Document';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Document());
        $grid->disableCreateButton();
        $grid->disableBatchActions();

        $grid->model()
            ->where([
                'enterprise_id' => Auth::user()->enterprise_id
            ]);
        $grid->column('name', __('Document'))->sortable();
        $grid->column('print_hearder', __('Print hearder'));
        $grid->column('print_water_mark', __('Print water mark'));

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
        $show = new Show(Document::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('name', __('Name'));
        $show->field('print_hearder', __('Print hearder'));
        $show->field('print_water_mark', __('Print water mark'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Document());

        $form->text('name', __('Document'))->readonly();

        $form->html("
        <code>[STUDENT_NAME]</code>
        <code>[STUDENT_CLASS]</code>
        <code>[STUDENT_SCHOOL_PAY_CODE]</code>
        <code>[SCHOOL_NAME]</code>
        <code>[REQUIREMENTS_TABLE]</code>
        ", 'Key words');
        $form->quill('body', __('Document'));
        $form->switch('print_hearder', __('Print hearder'));
        $form->switch('print_water_mark', __('Print water mark'));

        return $form;
    }
}
