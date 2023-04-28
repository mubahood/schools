<?php

namespace App\Admin\Actions\Post;

use Encore\Admin\Actions\BatchAction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;


class BatchStudentFinancialAccountSetVerified extends BatchAction
{
    public $name = 'Set as VERIFIED ACCOUNTS';

    public function handle(Collection $collection, Request $r)
    {
        $i = 0;
        foreach ($collection as $model) {
            $model->status = 1;
            $i++;
            $model->save();
        }
        return $this->response()->success("Updated $i Successfully.")->refresh();
    }
}
