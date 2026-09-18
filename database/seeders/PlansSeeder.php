<?php

namespace Database\Seeders;

use App\Models\Billing\Plan;
use Illuminate\Database\Seeder;

/**
 * The three public packages. Prices are per 6 months; a year is double.
 * Idempotent: keyed on code, so re-running updates rather than duplicates.
 */
class PlansSeeder extends Seeder
{
    public function run()
    {
        $common = ['Students, parents & staff', 'Fees, receipts & SchoolPay', 'Report cards & assessments', 'Parent mobile app'];

        $plans = [
            ['code' => 'STARTER', 'name' => 'Starter', 'min_students' => 0,   'max_students' => 100,  'price_6m' => 100000, 'price_12m' => 200000, 'sort' => 1,
             'features' => array_merge($common, ['Email support'])],
            ['code' => 'GROWTH',  'name' => 'Growth',  'min_students' => 101, 'max_students' => 500,  'price_6m' => 300000, 'price_12m' => 600000, 'sort' => 2,
             'features' => array_merge($common, ['Bulk SMS & messaging', 'Priority support'])],
            ['code' => 'SCALE',   'name' => 'Scale',   'min_students' => 501, 'max_students' => 1000, 'price_6m' => 700000, 'price_12m' => 1400000, 'sort' => 3,
             'features' => array_merge($common, ['Bulk SMS & messaging', 'Priority support', 'Dedicated onboarding'])],
        ];

        foreach ($plans as $p) {
            Plan::updateOrCreate(['code' => $p['code']], $p + ['is_public' => true]);
        }
    }
}
