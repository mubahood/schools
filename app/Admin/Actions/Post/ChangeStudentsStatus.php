<?php

namespace App\Admin\Actions\Post;

use App\Models\AcademicClass;
use Encore\Admin\Actions\BatchAction;
use Encore\Admin\Facades\Admin;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;


class ChangeStudentsStatus extends BatchAction
{
    public $name = 'Change Student\'s Status';

    public function handle(Collection $collection, Request $r)
    {
        $i = 0;
        foreach ($collection as $model) {
            $model->status = ((int)($r->get('status')));
            $i++;
            $model->save();
        }
        return $this->response()->success("Updated $i Successfully.")->refresh();
    }


    public function form()
    {

        $this->select('status', __('Status'))
            ->options([1 => 'Active', 2 => 'Pending', 0 => 'Not active'])
            ->required()
            ->rules('required');
    }
}
