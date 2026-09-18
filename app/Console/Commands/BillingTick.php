<?php

namespace App\Console\Commands;

use App\Services\BillingService;
use Illuminate\Console\Command;

/** Daily: move schools through trialing -> past_due -> suspended and email owners. */
class BillingTick extends Command
{
    protected $signature = 'billing:tick';
    protected $description = 'Apply subscription/trial expiries and send owner notices';

    public function handle(): int
    {
        $r = BillingService::tick();
        $this->info(sprintf('checked=%d changed=%d notified=%d', $r['checked'], $r['changed'], $r['notified']));

        return self::SUCCESS;
    }
}
