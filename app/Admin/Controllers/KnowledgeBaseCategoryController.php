<?php

namespace App\Admin\Controllers;

use App\Models\KnowledgeBaseCategory;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class KnowledgeBaseCategoryController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Knowledge Base Categories';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new KnowledgeBaseCategory());

        $grid->column('id', __('ID'))->sortable();
        $grid->column('name', __('Name'))->sortable();
        $grid->column('slug', __('Slug'));
        $grid->column('description', __('Description'))->limit(50);
        $grid->column('icon', __('Icon'));
        $grid->column('order_number', __('Order'))->sortable();
        $grid->column('articles_count', __('Articles Count'))->display(function () {
            return $this->articles()->count();
        })->sortable();
        $grid->column('is_active', __('Status'))->display(function ($value) {
            return $value ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>';
        });
        $grid->column('created_at', __('Created'))->display(function ($value) {
            return date('M j, Y', strtotime($value));
        })->sortable();

        $grid->filter(function($filter) {
            $filter->like('name', 'Name');
            $filter->equal('is_active', 'Status')->select([1 => 'Active', 0 => 'Inactive']);
        });

        $grid->actions(function ($actions) {
            $actions->add(new \Encore\Admin\Grid\Actions\Show);
            $actions->add(new \Encore\Admin\Grid\Actions\Edit);
            $actions->add(new \Encore\Admin\Grid\Actions\Delete);
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
        $show = new Show(KnowledgeBaseCategory::findOrFail($id));

        $show->field('id', __('ID'));
        $show->field('name', __('Name'));
        $show->field('slug', __('Slug'));
        $show->field('description', __('Description'));
        $show->field('icon', __('Icon'));
        $show->field('order_number', __('Order Number'));
        $show->field('is_active', __('Active'))->as(function ($value) {
            return $value ? 'Yes' : 'No';
        });
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        $show->articles('Articles', function ($articles) {
            $articles->resource('/admin/knowledge-base/articles');
            $articles->column('id', 'ID');
            $articles->column('title', 'Title');
            $articles->column('is_published', 'Published')->display(function ($value) {
                return $value ? 'Yes' : 'No';
            });
            $articles->column('created_at', 'Created')->display(function ($value) {
                return date('M j, Y', strtotime($value));
            });
        });

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new KnowledgeBaseCategory());

        $form->text('name', __('Name'))->rules('required|max:255');
        $form->text('slug', __('Slug'))->help('Leave empty to auto-generate from name');
        $form->textarea('description', __('Description'))->help('Brief description of what this category covers');
        $form->text('icon', __('Icon'))->help('FontAwesome icon class (e.g., fa-book, fa-question-circle)')->default('fa-book');
        $form->number('order_number', __('Order Number'))->default(0)->help('Lower numbers appear first');
        $form->switch('is_active', __('Active'))->default(1);

        $form->saving(function (Form $form) {
            // Auto-generate slug if not provided
            if (empty($form->slug)) {
                $form->slug = \Illuminate\Support\Str::slug($form->name);
            }
        });

        return $form;
    }
}