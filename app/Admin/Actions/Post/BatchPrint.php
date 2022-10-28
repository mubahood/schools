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
        $jds = [];
        foreach ($collection as $model) {
            $jds[] = $model->id;
        }

        
        return redirect(url('print?ids='.json_encode($jds)));
        return $this->response()->success('Success message... '.$x)->refresh();
    }

    public function html()
    {
        return "<a class='report-posts btn btn-sm btn-danger'><i class='fa fa-info-circle'></i>Report</a>";
    }

}