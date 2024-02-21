<?php

namespace App\Admin\Controllers;

use App\Models\Post;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class PostController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected function title()
    {
        $segment = request()->segment(1);
        if ($segment == 'posts') {
            return "News";
        } else if ($segment == 'notice-board') {
            return "Notice Board";
        } else if ($segment == 'events') {
            return "Events";
        }
        return "Posts";
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Post());
        $u = Admin::user();
        $conds = [
            'enterprise_id' => $u->enterprise_id
        ];
        $segment = request()->segment(1);
        if ($segment == 'posts') {
            $conds['type'] = 'News';
        } else if ($segment == 'notice-board') {
            $conds['type'] = 'Notice';
        } else if ($segment == 'events') {
            $conds['type'] = 'Event';
        }


        $grid->model()->where(
            $conds
        )->orderBy('id', 'desc');

        $grid->column('photo', __('Photo'))
            ->lightbox(['width' => 60, 'height' => 60])
            ->sortable();

        $grid->disableBatchActions();
        $grid->column('id', __('Id'))->hide();

        $grid->quickSearch('title')->placeholder('Search by title...');

        $grid->column('title', __('Title'))
            ->sortable();
        $grid->column('academic_year_id', __('Academic Year'))
            ->display(function ($year) {
                return \App\Models\AcademicYear::find($year)->name;
            })->sortable()
            ->hide();
        $grid->column('term_id', __('Term'))
            ->display(function ($term) {
                return "Term " . \App\Models\Term::find($term)->name;
            })->sortable()
            ->hide();
        $grid->column('posted_by_id', __('Posted'))
            ->display(function ($posted) {
                return \App\Models\User::find($posted)->name;
            })->sortable();

        $grid->column('description', __('Description'))->hide();
        $grid->column('view_count', __('Views'))
            ->display(function ($views) {
                return $this->views()->count();
            });

        $grid->column('file', __('File'))->hide();
        /* $grid->column('type', __('Type'))
            ->filter(['News' => 'News', 'Notice' => 'Notice', 'Event' => 'Event'])
            ->sortable(); */
        $grid->column('target', __('Target'))->hide();
        $grid->column('status', __('Status'))
            ->filter(['Draft' => 'Draft', 'Published' => 'Published'])
            ->sortable()
            ->label([
                'Draft' => 'default',
                'Published' => 'success',
            ]);
        if ($segment == 'Event') {
            $grid->column('event_date', __('Event Date'))
                ->display(function ($date) {
                    return date('d M, Y', strtotime($date));
                })->sortable();
        }

        $grid->column('created_at', __('Posted On'))
            ->display(function ($created) {
                return date('d M, Y', strtotime($created));
            })->sortable();
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
        $show = new Show(Post::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('academic_year_id', __('Academic year id'));
        $show->field('term_id', __('Term id'));
        $show->field('posted_by_id', __('Posted by id'));
        $show->field('title', __('Title'));
        $show->field('description', __('Description'));
        $show->field('photo', __('Photo'));
        $show->field('file', __('File'));
        $show->field('type', __('Type'));
        $show->field('target', __('Target'));
        $show->field('status', __('Status'));
        $show->field('event_date', __('Event date'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Post());
        $u = Admin::user();
        $segment = request()->segment(1);
        $type = 'News';
        if ($segment == 'notice-board') {
            $type = 'Notice';
        } else if ($segment == 'events') {
            $type = 'Event';
        }
        $ent = \App\Models\Enterprise::find($u->enterprise_id);
        $term = $ent->active_term();

        $form->hidden('enterprise_id', __('Enterprise'))
            ->default($u->enterprise_id);
        $form->hidden('academic_year_id', __('Academic year id'))->default($term->academic_year_id);
        $form->hidden('term_id', __('Term id'))->default($term->id);
        $form->hidden('posted_by_id', __('Posted by id'))->default($u->id);
        $form->text('title', __('Title'))->rules('required');
        $form->quill('description', __('Description'))->rules('required');
        $form->image('photo', __('Photo'));
        $form->file('file', __('File'));
        $form->hidden('type', __('Type'))->default($type);

        $form->radio('target', __('Target'))
            ->options(['All' => 'All', 'Students' => 'Students', 'Parents' => 'Parents', 'Teachers' => 'Teachers', 'Non-Teaching' => 'Non-Teaching Staff',])
            ->default('All');
        $form->radio('status', __('Status'))->options(['Draft' => 'Draft', 'Published' => 'Published'])->rules('required');
        if ($type == 'Event') {
            $form->datetime('event_date', __('Event date'))->rules('required');
        }

        $form->footer(function ($footer) {
            $footer->disableReset();
        });

        return $form;
    }
}
