<?php

namespace App\Admin\Controllers;

use App\Models\Book;
use App\Models\BookBorrow;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class BookBorrowController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'BookBorrow';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new BookBorrow());

        $grid->column('id', __('Id'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        $grid->column('enterprise_id', __('Enterprise id'));
        $grid->column('borrowed_by', __('Borrowed by'));
        $grid->column('return_date', __('Return date'));
        $grid->column('status', __('Status'));

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
        $show = new Show(BookBorrow::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('borrowed_by', __('Borrowed by'));
        $show->field('return_date', __('Return date'));
        $show->field('status', __('Status'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new BookBorrow());
        $u = Admin::user();

        $form->display('Books borrowed by')->default($u->name)->value($u->name);
        $form->hidden('enterprise_id', __('Enterprise id'))
            ->default($u->enterprise_id);
        $form->hidden('borrowed_by', __('Borrowed by'))->default($u->id);

        if ($form->isCreating()) {
            $form->date('return_date', __('Return before date'))
                ->help("Select the date you promise to return these books")
                ->rules('required');
        } else {
            if (!$u->isRole('librarian')) {
                $form->date('return_date', __('Return before date'))
                    ->rules('required');
            } else {
                $form->display('return_date');
            }
        }


        $form->morphMany('book_borrow_books', 'Click on new to add a book', function (Form\NestedForm $form) {
            $u = Admin::user();

            $form->hidden('enterprise_id')->default($u->enterprise_id);
            //$form->enterprise_id('borrowed_by')->default($u->id);
            $form->enterprise_id('returned')->default(0);
            $form->enterprise_id('is_lost')->default(0);

            $form->select('book_id', "Book title")
                ->options(function ($id) {
                    $b = Book::find($id);

                    if ($b) {
                        return [$b->id => $b->title];
                    }
                })
                ->ajax(url('/api/books?enterprise_id=' . $u->enterprise_id))->rules('required');
        });


        $form->divider();

        if ($u->isRole('librarian')) {

            $options = [];
            if (!$form->isCreating()) {
                $options = [
                    'Borrowed' => 'Approve request',
                    'Rejected' => 'Reject request',
                ];
            } else {
                $options = [
                    'Borrowed' => 'Approve',
                    'Pending' => 'Pending',
                ];
            }

            $form->select('status', __('Status'))
                ->options($options)
                ->help("Carefully make status decision since it cannot be reversed.")
                ->rules('required');
        } else {
            $form->hidden('status', __('Status'))->default('Pending');
        }

        return $form;
    }
}
