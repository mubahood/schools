<?php

namespace App\Admin\Controllers;

use App\Models\Gen;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\DB;

class GenController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Gen';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Gen());

        $grid->model()->orderBy('id', 'desc');
        $grid->column('id', __('Id'))->sortable();
        $grid->column('class_name', __('Class'));
        $grid->column('table_name', __('Table'));
        $grid->column('gen', __('Gen-Model'))->display(function () {
            return '<a target="_blank" href="' . url('gen?id=' . $this->id) . '">Make Model</a>';
        });
        $grid->column('gen-form', __('Gen-form'))->display(function () {
            return '<a target="_blank" href="' . url('gen-form?id=' . $this->id) . '">Make Forms</a>';
        });
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
        $show = new Show(Gen::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('class_name', __('Class name'));
        $show->field('use_db_table', __('Use db table'));
        $show->field('table_name', __('Table name'));
        $show->field('fields', __('Fields'));
        $show->field('file_id', __('File id'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Gen());

        $form->text('class_name', __('Class Name'));
        $tables = DB::select("SHOW TABLES");
        $data = [];
        foreach ($tables as $key => $table) {
            //$tables[] = $table->Tables_in_ussd;
            $db_name = 'Tables_in_' . env("DB_DATABASE");
            $data[$table->$db_name] = $table->$db_name;
        }
        $form->select('table_name', __('Table name'))->options($data)->rules('required');
        $form->text('end_point', __('end_point'))->rules('required');
        $form->hidden('fields', __('Fields'))->default('');
        $form->hidden('use_db_table', __('Fields'))->default('Yes');
        $form->hidden('file_id', __('File ID'))->default('');

        return $form;
    }
}
