<?php

namespace App\Admin\Actions\Post;

use Encore\Admin\Actions\BatchAction;
use Illuminate\Database\Eloquent\Collection;

class BatchPrint extends BatchAction
{
    public $name = 'batch print';

    public function handle(Collection $collection)
    {
        $x = 0;
        foreach ($collection as $model) {
            $x++;
        }

        return $this->response()->success('Success message... '.$x)->refresh();
    }

}