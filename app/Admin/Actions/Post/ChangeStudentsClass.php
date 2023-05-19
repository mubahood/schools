<?php

namespace App\Admin\Actions\Post;

use App\Models\AcademicClass;
use Encore\Admin\Actions\BatchAction;
use Encore\Admin\Facades\Admin;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;


class ChangeStudentsClass extends BatchAction
{
    public $name = 'Change Student\'s Class';

    public function handle(Collection $collection, Request $r)
    {
        $i = 0;
        foreach ($collection as $model) { 
            $model->academic_class_id = $r->get('academic_class_id');
            $model->done_selecting_option_courses = $r->get('done_selecting_option_courses');
            $model->stream_id = null;
            $i++;
            $model->save();
        } 
        return $this->response()->success("Updated $i Successfully.")->refresh();
    }


    public function form()
    { 

        $ops = [];
        foreach (AcademicClass::where([
            'enterprise_id' => Admin::user()->enterprise_id,
        ])
        ->orderby('id','desc')
        ->get() as $key => $val) {
            $ops[$val->id] = $val->name_text;
        }
        $this->select('academic_class_id', __('Academic class'))
            ->options($ops)
            ->required()
            ->rules('required');

        $this->radio('done_selecting_option_courses', __('FROM P-7 to P-6'))
            ->options([
                1 => 'Yes',
                0 => 'No',
            ])
            ->required()
            ->rules('required');
    }
}
