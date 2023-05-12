<?php

namespace App\Admin\Actions\Post;

use App\Models\Transaction;
use Encore\Admin\Actions\BatchAction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;


class BatchSetProcessedAccountController extends BatchAction
{
    public $name = 'Process balance';

    public function handle(Collection $collection, Request $r)
    {
        $i = 0;
        foreach ($collection as $acc) {
            $acc->processBalance(); 
            $i++;
        }

        return $this->response()->success("Updated $i Successfully.")->refresh();
    }
}
