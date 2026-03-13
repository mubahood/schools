<?php

namespace App\Admin\Actions\Post;

use Encore\Admin\Actions\BatchAction;
use Illuminate\Database\Eloquent\Collection;

class BatchPrintReceipts extends BatchAction
{
    public $name = 'Print Receipts';

    public function handle(Collection $collection)
    {
        $ids = $collection->pluck('id')->implode(',');
        $url = url('print-receipt-batch?ids=' . $ids);

        return $this->response()->success('Opening receipts...')->open($url);
    }
}
