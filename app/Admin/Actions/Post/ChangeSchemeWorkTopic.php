<?php

namespace App\Admin\Actions\Post;

use App\Models\AcademicClass;
use Encore\Admin\Actions\BatchAction;
use Encore\Admin\Facades\Admin;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;


class ChangeSchemeWorkTopic extends BatchAction
{
    public $name = 'Change topics of selected';

    public function handle(Collection $collection, Request $r)
    {
        $i = 0;
        foreach ($collection as $model) {
            $topic = $r->get('topic');
            $model->topic = $topic;
            $i++;
            $model->save();
        }
        return $this->response()->success("Updated $i topics Successfully.")->refresh();
    }


    public function form()
    {

        $this->text('topic', __('Topic'));
    }
}
