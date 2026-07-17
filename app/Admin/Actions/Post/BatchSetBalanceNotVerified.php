<?php

namespace App\Admin\Actions\Post;

use Encore\Admin\Actions\BatchAction;
use Illuminate\Database\Eloquent\Collection;

class BatchSetBalanceNotVerified extends BatchAction
{
    public $name = 'Hide Balance from Parents';

    public function handle(Collection $collection)
    {
        $count = 0;
        foreach ($collection as $account) {
            $account->is_balance_verified = 0;
            $account->save();
            $count++;
        }
        return $this->response()->success("Balance hidden for $count account(s).")->refresh();
    }

    public function dialog()
    {
        $this->confirm('Hide the fees balance from parents in the mobile app for selected accounts?');
    }
}
