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
    protected $title = 'Books catalogue';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Book());
        $grid->model()->where('enterprise_id', Admin::user()->enterprise_id);

        $u = Admin::user();
        if (!$u->isRole('librarian')) {
            $grid->disableActions();
            $grid->disableCreateButton();
            $grid->disableExport();
            $grid->disableBatchActions();
        }

        $grid->column('id', __('BOOK ID'))->sortable();
        
        /* $grid->column('thumbnail', __('Book cover'))->lightbox(['zooming' => true]);
        
                $grid->picture('thumbnail', __('Book cover'))->display(function ($thumb){
            return '<img width="30" src="'.url("uploads/".$thumb).'">';
        })->lightbox();

        
        */

        $grid->column('thumbnail', __('Book cover'))->display(function ($thumb){
            return '<img width="30" src="'.url("storage/".$thumb).'">';
        });

        $grid->column('title', __('Title'))->sortable();
        $grid->column('book_author_id', __('Author'))->display(function () {
            if ($this->author == null) {
                return "-";
            }
            return $this->author->name;
        })->sortable();
        $grid->column('books_category_id', __('Category'))->display(function () {
            if ($this->category == null) {
                return "-";
            }
            return $this->category->title;
        })->sortable();

        $grid->column('isbn', __('Isbn'));
        $grid->column('quantity', __('Quantity Available'));
        $grid->column('language', __('Language'))->hide();
        $grid->column('pdf', __('Pdf'))->hide();
        $grid->column('price', __('Price'))->hide();
        $grid->column('page_count', __('Pages'))->hide();
        $grid->column('description', __('Description'))->hide();
        $grid->column('published_date', __('Published'))->hide();
        $grid->column('subtitle', __('Subtitle'))->hide();

        //$grid->column('enterprise_id', __('Enterprise id'));

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
