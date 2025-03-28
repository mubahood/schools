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


class StudentsChangeGender extends BatchAction
{
    public $name = 'Change Students\ Gender';

    public function handle(Collection $collection, Request $r)
    {
        $i = 0;

        foreach ($collection as $model) {
            $model->sex = $r->sex;
            $i++;
            $model->save();
        }
        return $this->response()->success("Changed $i students gender.")->refresh();
    }


    public function form()
    {
        $this->select('sex', __('Select Gender'))
            ->options([
                'Male' => 'Male',
                'Female' => 'Female',
            ]);
    }
}
