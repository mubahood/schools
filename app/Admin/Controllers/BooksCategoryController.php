<?php

namespace App\Admin\Controllers;

use App\Models\BooksCategory;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Tree;
use Encore\Admin\Widgets\Box;
use Illuminate\Routing\Controller;

class BooksCategoryController extends Controller
{
    use HasResourceActions;

    /**
     * Index interface.
     *
     * @param Content $content
     *
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->title(trans('admin.menu'))
            ->description(trans('admin.list'))
            ->row(function (Row $row) {
                $row->column(6, $this->treeView()->render());

                $row->column(6, function (Column $column) {
                    $form = new \Encore\Admin\Widgets\Form();
                    $form->action(admin_url('books-categories/'));
 
                    $form->select('parent_id', trans('admin.parent_id'))->options(BooksCategory::selectOptions());
                    $form->text('title', trans('admin.title'))->rules('required')->default("Default title");
                    $form->textarea('details');
                    
                    $form->hidden('enterprise_id')->rules('required')->default(Admin::user()->enterprise_id)
                    ->value(Admin::user()->enterprise_id);
                    
                    $form->hidden('_token')->default(csrf_token());
                    $column->append((new Box(trans('admin.new'), $form))->style('success'));
                });
            });
    }

    /**
     * Redirect to edit page.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function show($id)
    {
        return redirect()->route('admin.auth.menu.edit', ['menu' => $id]);
    }

    /**
     * @return \Encore\Admin\Tree
     */
    protected function treeView()
    {
        
        $tree = new Tree(new BooksCategory());

        $tree->disableCreate();

        $tree->branch(function ($branch) {
            $payload = "<strong>{$branch['title']}</strong>";

            return $payload;
        });

        return $tree;
    }

    /**
     * Edit interface.
     *
     * @param string  $id
     * @param Content $content
     *
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return $content
            ->title(trans('admin.menu'))
            ->description(trans('admin.edit'))
            ->row($this->form()->edit($id));
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    public function form()
    {

        $form = new Form(new BooksCategory());

        $form->display('id', 'ID');

        $form->select('parent_id', trans('admin.parent_id'))->options(BooksCategory::selectOptions());
        $form->text('title', trans('admin.title'))->rules('required');


        return $form;
    }

    /**
     * Help message for icon field.
     *
     * @return string
     */
    protected function iconHelp()
    {
        return 'For more icons please see <a href="http://fontawesome.io/icons/" target="_blank">http://fontawesome.io/icons/</a>';
    }
}
