<?php

namespace App\Admin\Actions\Post;

use Encore\Admin\Actions\BatchAction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;


class BatchStudentFinancialAccountChangeBalance extends BatchAction
{
    public $name = 'Change account balance';

    public function handle(Collection $collection, Request $r)
    {
        $i = 0;
        foreach ($collection as $model) {
            $model->new_balance = 1;
            $model->new_balance_amount = $r->get('new_balance_amount');
            $i++;
            $model->save();
        }

        return $this->response()->success("Updated $i Successfully.")->refresh();
    }


    public function form()
    {
        $this->text('new_balance_amount', __('New Account Balance'))
            ->required()
            ->rules('int')->attribute('type', 'number') 
            ->rules('required');
    }
}
