<?php

namespace App\Admin\Actions\Post;

use App\Models\Transaction;
use Encore\Admin\Actions\BatchAction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;


class BatchSetNotProcessedAccountController extends BatchAction
{
    public $name = 'Set Not Process balance';

    public function handle(Collection $collection, Request $r)
    {
        $i = 0;
        foreach ($collection as $acc) {  
        
            $acc->prossessed = 'No';
            $acc->save(); 
            $i++;
        }

        return $this->response()->success("Updated $i Successfully.")->refresh();
    }
}
