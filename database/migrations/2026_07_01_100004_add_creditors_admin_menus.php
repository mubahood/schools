<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddCreditorsAdminMenus extends Migration
{
    // Finance menu parent_id = 73

    public function up()
    {
        $financeId = 73;

        $items = [
            ['title' => 'Creditors',         'uri' => 'creditor-records',  'icon' => 'fa-credit-card'],
            ['title' => 'Creditor Payments', 'uri' => 'creditor-payments', 'icon' => 'fa-money'],
        ];

        foreach ($items as $item) {
            if (!DB::table('admin_menu')->where('uri', $item['uri'])->exists()) {
                $order = (int)(DB::table('admin_menu')->max('order') ?? 0) + 1;
                DB::table('admin_menu')->insert([
                    'parent_id'  => $financeId,
                    'order'      => $order,
                    'title'      => $item['title'],
                    'icon'       => $item['icon'],
                    'uri'        => $item['uri'],
                    'permission' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down()
    {
        DB::table('admin_menu')->whereIn('uri', ['creditor-records', 'creditor-payments'])->delete();
    }
}
