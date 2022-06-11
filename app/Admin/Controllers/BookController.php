<?php

namespace App\Admin\Controllers;

use App\Models\Book;
use App\Models\BookAuthor;
use App\Models\BooksCategory;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class BookController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Book';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Book());

        $grid->column('id', __('Id'));
        $grid->column('enterprise_id', __('Enterprise id'));
        $grid->column('books_category_id', __('Books category id'));
        $grid->column('api_id', __('Api id'));
        $grid->column('title', __('Title'));
        $grid->column('subtitle', __('Subtitle'));
        $grid->column('book_author_id', __('Book author id'));
        $grid->column('published_date', __('Published date'));
        $grid->column('description', __('Description'));
        $grid->column('isbn', __('Isbn'));
        $grid->column('page_count', __('Page count'));
        $grid->column('thumbnail', __('Thumbnail'));
        $grid->column('language', __('Language'));
        $grid->column('price', __('Price'));
        $grid->column('quantity', __('Quantity'));
        $grid->column('pdf', __('Pdf'));

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
        $show = new Show(Book::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('books_category_id', __('Books category id'));
        $show->field('api_id', __('Api id'));
        $show->field('title', __('Title'));
        $show->field('subtitle', __('Subtitle'));
        $show->field('book_author_id', __('Book author id'));
        $show->field('published_date', __('Published date'));
        $show->field('description', __('Description'));
        $show->field('isbn', __('Isbn'));
        $show->field('page_count', __('Page count'));
        $show->field('thumbnail', __('Thumbnail'));
        $show->field('language', __('Language'));
        $show->field('price', __('Price'));
        $show->field('quantity', __('Quantity'));
        $show->field('pdf', __('Pdf'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Book());

        $form->hidden('enterprise_id')->rules('required')->default(Admin::user()->enterprise_id)
            ->value(Admin::user()->enterprise_id);
        $form->select('books_category_id', 'Category')->options(
            BooksCategory::sub_categories()->pluck('title', 'id')
        )
            ->rules('required');
        $form->text('title', __('Book Title'));
        $form->text('subtitle', __('Short description'));
        $form->text('quantity', __('Quantity Available'))->attribute('type', 'number')->required();

        $form->select('book_author_id', 'Author')->options(
            BookAuthor::sub_categories()->pluck('title', 'id')
        )
            ->rules('required');
        $form->date('published_date', __('Published date'));
        $form->text('isbn', __('USBN'));
        $form->textarea('description', __('Description'));
        $form->text('page_count', __('Page count'))->attribute('type', 'number');
        $form->select('language', 'Language')->options([
            'English' => 'English',
            'Swahili' => 'Swahili',
            'French' => 'French',
            'Arabic' => 'Arabic',
            'Other' => 'Other',
        ])
            ->rules('required');
        $form->image('thumbnail', __('Cover photo'));
        $form->text('price', __('Price'))->attribute('type', 'number');

        $form->file('pdf', __('PDF'));

        $form->hidden('api_id', __('Api id'));
        return $form;
    }
}
