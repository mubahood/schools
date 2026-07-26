<?php

namespace App\Admin\Controllers;

use App\Models\Building;
use App\Models\TimetableRoom;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class TimetableRoomController extends AdminController
{
    protected $title = 'Classrooms & Rooms';

    protected function grid()
    {
        $u    = Admin::user();
        $grid = new Grid(new TimetableRoom());

        $grid->model()
            ->where('enterprise_id', $u->enterprise_id)
            ->orderBy('name');

        $grid->disableExport();
        $grid->disableColumnSelector();

        $grid->column('id', '#')->sortable();
        $grid->column('name', 'Room')->sortable()->display(function ($v) {
            return "<strong>{$v}</strong>";
        });
        $grid->column('room_type', 'Type')->label([
            'Classroom' => 'success',
            'Lab'       => 'info',
            'Hall'      => 'warning',
            'Library'   => 'default',
            'Other'     => 'default',
        ]);
        $grid->column('capacity', 'Capacity')->display(fn($v) => $v > 0 ? $v : '—')->sortable();
        $grid->column('building_name', 'Building')->display(function () {
            return optional($this->building)->name ?? '—';
        });
        $grid->column('is_active', 'Status')->display(function ($v) {
            return $v
                ? "<span class='label label-success'>Active</span>"
                : "<span class='label label-default'>Inactive</span>";
        });

        $grid->filter(function ($filter) use ($u) {
            $filter->disableIdFilter();
            $filter->like('name', 'Room name');
            $filter->equal('room_type', 'Type')->select([
                'Classroom' => 'Classroom', 'Lab' => 'Lab',
                'Hall' => 'Hall', 'Library' => 'Library', 'Other' => 'Other',
            ]);
        });

        return $grid;
    }

    protected function detail($id)
    {
        $show = new Show(TimetableRoom::findOrFail($id));
        $show->field('name');
        $show->field('room_type', 'Type');
        $show->field('capacity');
        $show->field('description');
        return $show;
    }

    protected function form()
    {
        $u    = Admin::user();
        $form = new Form(new TimetableRoom());

        $form->hidden('enterprise_id')->default($u->enterprise_id);

        $form->text('name', 'Room Name')
            ->rules('required|string|max:255')
            ->placeholder('e.g. P.6 Classroom, Science Lab, Assembly Hall');

        $form->select('room_type', 'Room Type')
            ->options([
                'Classroom' => 'Classroom',
                'Lab'       => 'Lab / Laboratory',
                'Hall'      => 'Hall / Assembly',
                'Library'   => 'Library',
                'Other'     => 'Other',
            ])
            ->default('Classroom')
            ->rules('required');

        $form->number('capacity', 'Seating Capacity')
            ->default(0)
            ->help('Leave 0 if capacity is not relevant');

        $buildings = Building::where('enterprise_id', $u->enterprise_id)
            ->orderBy('name')->pluck('name', 'id')->toArray();
        if (!empty($buildings)) {
            $form->select('building_id', 'Building (optional)')
                ->options(['' => '— none —'] + $buildings);
        } else {
            $form->hidden('building_id');
        }

        $form->textarea('description', 'Description / Notes')->rows(2);

        $form->radio('is_active', 'Status')
            ->options([1 => 'Active', 0 => 'Inactive'])
            ->default(1);

        $form->disableReset();
        $form->disableViewCheck();
        $form->disableCreatingCheck();

        return $form;
    }
}
