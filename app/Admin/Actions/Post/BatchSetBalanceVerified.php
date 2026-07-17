<?php

namespace App\Admin\Actions\Post;

use Encore\Admin\Actions\BatchAction;
use Illuminate\Database\Eloquent\Collection;

class BatchSetBalanceVerified extends BatchAction
{
    public $name = 'Show Balance to Parents';

    public function handle(Collection $collection)
    {
        $count = 0;
        foreach ($collection as $account) {
            $account->is_balance_verified = 1;
            $account->save();
            $count++;
        }
        return $this->response()->success("Balance made visible for $count account(s).")->refresh();
    }

    public function dialog()
    {
        $this->confirm('Mark selected accounts as balance-visible to parents in the mobile app?');
    }
}
