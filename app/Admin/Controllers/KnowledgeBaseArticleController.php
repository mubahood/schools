<?php

namespace App\Admin\Controllers;

use App\Models\KnowledgeBaseArticle;
use App\Models\KnowledgeBaseCategory;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class KnowledgeBaseArticleController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Knowledge Base Articles';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new KnowledgeBaseArticle());

        $grid->column('id', __('ID'))->sortable();
        $grid->column('category.name', __('Category'))->sortable();
        $grid->column('title', __('Title'))->sortable();
        $grid->column('slug', __('Slug'));
        $grid->column('excerpt', __('Excerpt'))->limit(50);
        $grid->column('order_number', __('Order'))->sortable();
        $grid->column('has_youtube_video', __('Video'))->display(function ($value) {
            return $value ? '<i class="fa fa-video-camera text-success"></i>' : '<i class="fa fa-minus text-muted"></i>';
        });
        $grid->column('is_published', __('Status'))->display(function ($value) {
            return $value ? '<span class="label label-success">Published</span>' : '<span class="label label-warning">Draft</span>';
        });
        $grid->column('created_at', __('Created'))->display(function ($value) {
            return date('M j, Y', strtotime($value));
        })->sortable();

        $grid->filter(function($filter) {
            $filter->like('title', 'Title');
            $filter->equal('category_id', 'Category')->select(function () {
                return KnowledgeBaseCategory::active()->pluck('name', 'id');
            });
            $filter->equal('is_published', 'Status')->select([1 => 'Published', 0 => 'Draft']);
            $filter->equal('has_youtube_video', 'Has Video')->select([1 => 'Yes', 0 => 'No']);
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
        $show = new Show(KnowledgeBaseArticle::findOrFail($id));

        $show->field('id', __('ID'));
        $show->field('category.name', __('Category'));
        $show->field('title', __('Title'));
        $show->field('slug', __('Slug'));
        $show->field('content', __('Content'))->unescape();
        $show->field('excerpt', __('Excerpt'));
        $show->field('order_number', __('Order Number'));
        $show->field('has_youtube_video', __('Has YouTube Video'))->as(function ($value) {
            return $value ? 'Yes' : 'No';
        });
        $show->field('youtube_video_link', __('YouTube Video Link'));
        $show->field('is_published', __('Published'))->as(function ($value) {
            return $value ? 'Yes' : 'No';
        });
        $show->field('meta_title', __('Meta Title'));
        $show->field('meta_description', __('Meta Description'));
        $show->field('reading_time', __('Reading Time'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new KnowledgeBaseArticle());

        $form->select('category_id', __('Category'))
            ->options(function () {
                return KnowledgeBaseCategory::active()->orderBy('order_number')->pluck('name', 'id');
            })
            ->rules('required');

        $form->text('title', __('Title'))->rules('required|max:255');
        $form->text('slug', __('Slug'))->help('Leave empty to auto-generate from title');
        
        $form->ckeditor('content', __('Content'))->rules('required');
        $form->textarea('excerpt', __('Excerpt'))->help('Brief summary (leave empty to auto-generate from content)');

        $form->divider('Organization');
        $form->number('order_number', __('Order Number'))->default(0)->help('Lower numbers appear first within category');

        $form->divider('YouTube Video (Optional)');
        $form->switch('has_youtube_video', __('Include YouTube Video'))->default(0);
        $form->url('youtube_video_link', __('YouTube Video URL'))->help('Full YouTube video URL');

        $form->divider('Publishing');
        $form->switch('is_published', __('Published'))->default(1);

        $form->divider('SEO (Optional)');
        $form->text('meta_title', __('Meta Title'))->help('Leave empty to use article title');
        $form->textarea('meta_description', __('Meta Description'))->help('Leave empty to use excerpt');

        $form->saving(function (Form $form) {
            // Auto-generate slug if not provided
            if (empty($form->slug)) {
                $form->slug = \Illuminate\Support\Str::slug($form->title);
            }

            // Validate YouTube video if enabled
            if ($form->has_youtube_video && empty($form->youtube_video_link)) {
                throw new \Exception('YouTube video URL is required when "Include YouTube Video" is enabled.');
            }

            // Auto-generate excerpt if not provided
            if (empty($form->excerpt) && !empty($form->content)) {
                $form->excerpt = \Illuminate\Support\Str::limit(strip_tags($form->content), 150);
            }
        });

        return $form;
    }
}