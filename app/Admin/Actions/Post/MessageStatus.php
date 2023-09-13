<?php

namespace App\Admin\Actions\Post;

use App\Models\AcademicClass;
use App\Models\DirectMessage;
use Encore\Admin\Actions\BatchAction;
use Encore\Admin\Facades\Admin;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;


class MessageStatus extends BatchAction
{
    public $name = 'Change Message\'s Status';

    public function handle(Collection $collection, Request $r)
    {
        $i = 0;
        foreach ($collection as $model) {
            $model->status = (($r->get('status')));
            $i++;
            $model->save();
            DirectMessage::send_message($model);
        }
        return $this->response()->success("Updated $i Messages Successfully.")->refresh();
    }


    public function form()
    {

        $this->select('status', __('Status'))
            ->options([
                'Pending' => 'Pending',
                'Draft' => 'Draft',
            ])
            ->required()
            ->rules('required');
    }
}
