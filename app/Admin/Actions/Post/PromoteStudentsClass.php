<?php

namespace App\Admin\Actions\Post;

use App\Models\AcademicClass;
use Encore\Admin\Actions\BatchAction;
use Encore\Admin\Facades\Admin;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;


class PromoteStudentsClass extends BatchAction
{
    public $name = 'Promote Students - By Class'; 

    public function handle(Collection $collection, Request $r)
    {
        $i = 0;
        foreach ($collection as $model) {
            $model->status = ((int)($r->get('status')));
            $class_id = ((int)($r->get('academic_class_id')));
            $class = AcademicClass::find($class_id);
            if ($class == null) {
                return $this->response()->error("Class not found.")->refresh();
            }
            $model->current_class_id = $class_id;
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
        }

        $this->select('academic_class_id', __('Academic Class'))->options($classes);



        $this->select('status', __('Status'))
            ->options([1 => 'Active', 2 => 'Pending', 0 => 'Not active'])
            ->required()
            ->rules('required');
    }
}
