<?php

namespace App\Admin\Actions\Post;

use App\Models\AcademicClass;
use App\Models\AcademicClassSctream;
use App\Models\TheologyClass;
use App\Models\TheologyStream;
use Encore\Admin\Actions\BatchAction;
use Encore\Admin\Facades\Admin;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;


class UpdateStudentsTheologyStream extends BatchAction
{
public $name = 'Change Students  - Theology Stream';

    public function handle(Collection $collection, Request $r)
    {
        $i = 0;
        $stream_id = ((int)($r->get('stream_id')));
        $stream = TheologyStream::find($stream_id);
        if ($stream == null) {
            return $this->response()->error("Stream #$stream_id not found.")->refresh();
        }
        $class = $stream->theology_class;
        if ($class == null) {
            return $this->response()->error("Class not found.")->refresh();
        }

        foreach ($collection as $model) {
            $model->current_theology_class_id = $class->id;
            $model->theology_stream_id = $stream_id;
            $i++;
            $model->save();
        }
        return $this->response()->success("Changed $i students to " . $class->name_text)->refresh();
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
            TheologyClass::where([
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

        $_streams = TheologyStream::whereIn('theology_class_id', $ids)->get();
        $streams = [];
        foreach ($_streams as $stream) {
            $streams[$stream->id] = $stream->name_text;
        }

        $this->select('stream_id', __('Select Stream'))
            ->options($streams)
            ->required()
            ->rules('required');
    }
}
