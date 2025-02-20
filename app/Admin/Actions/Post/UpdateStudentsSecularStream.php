<?php

namespace App\Admin\Actions\Post;

use App\Models\AcademicClass;
use App\Models\AcademicClassSctream;
use Encore\Admin\Actions\BatchAction;
use Encore\Admin\Facades\Admin;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;


class UpdateStudentsSecularStream extends BatchAction
{
    public $name = 'Promote Students  - By Secular Stream';

    public function handle(Collection $collection, Request $r)
    {
        $i = 0;
        $stream_id = ((int)($r->get('stream_id')));
        $stream = AcademicClassSctream::find($stream_id);
        if ($stream == null) {
            return $this->response()->error("Stream not found.")->refresh();
        }
        $class = $stream->academic_class;
        if ($class == null) {
            return $this->response()->error("Class not found.")->refresh();
        }

        foreach ($collection as $model) {
            $model->status = ((int)($r->get('status')));
            $model->current_class_id = $class->id;
            $model->stream_id = $stream_id;
            $i++;
            $model->save();
        }
        return $this->response()->success("Updated $i Successfully.")->refresh();
    }


    public function form()
    {

        $u = Admin::user();
        if ($u == null) {
            throw new \Exception('User not found');
        }

        $active_academic_year = $u->ent->active_academic_year();
        if ($active_academic_year == null) {
            die("No active academic year");
        }

        $classes = [];
        $ids = [];
        foreach (
            AcademicClass::where([
                'enterprise_id' => $u->enterprise_id,
                'academic_year_id' => $active_academic_year->id,
            ])->get() as $class
        ) {
            if (((int)($class->academic_year->is_active)) != 1) {
                continue;
            }
            $classes[$class->id] = $class->name_text;
            $ids[] = $class->id;
        }

        $_streams = AcademicClassSctream::whereIn('academic_class_id', $ids)->get();
        $streams = [];
        foreach ($_streams as $stream) {
            $streams[$stream->id] = $stream->name_text;
        }

        $this->select('stream_id', __('Select Stream'))
            ->options($streams)
            ->required()
            ->rules('required');



        $this->select('status', __('Status'))
            ->options([1 => 'Active', 2 => 'Pending', 0 => 'Not active'])
            ->required()
            ->rules('required');
    }
}
