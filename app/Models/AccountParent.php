<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountParent extends Model
{
    use HasFactory;

    public static function boot()
    {
        parent::boot();

        self::booting(function ($m) {
            die("You cannot delete this account.");
            if ($m->name == 'Other') {
            }
        });
    }

    public function accounts()
    {
        return $this->hasMany(Account::class);
    }

    public function getSum($year)
    {

        $tot = 0;
        // $accs = "SELECT id FROM accounts WHERE account_parent_id = $this->id";
        // $sums = "SELECT id FROM accounts WHERE account_parent_id = $this->id";

        foreach (Account::where([
            'account_parent_id' => $this->id
        ])->get() as $key => $acc) {
            $tot += Transaction::where([
                'account_id' => $acc->id,
                'is_contra_entry' => 0,
                'academic_year_id' => $year->id
            ])
            ->where('amount','>',0)
            ->sum('amount');
        }

        return $tot;
    }
    /* 
"balance" => 0
"status" => 0
"category" => null
"description" => null
"account_parent_id" => null
"transfer_keyword" => null
"want_to_transfer" => null
"academic_class_id" => null
"prossessed" => "No"
*/
}
