<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditorPayment extends Model
{
    use HasFactory;

    public function creditor_record()
    {
        return $this->belongsTo(CreditorRecord::class);
    }

    public function created_by()
    {
        return $this->belongsTo(Administrator::class, 'created_by_id');
    }

    public static function boot()
    {
        parent::boot();

        $recalc = function ($m) {
            $record = CreditorRecord::find($m->creditor_record_id);
            if ($record) {
                $record->updateStatus();
            }
        };

        self::created($recalc);
        self::updated($recalc);
        self::deleted($recalc);
    }
}
