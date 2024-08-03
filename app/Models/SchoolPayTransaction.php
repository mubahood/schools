<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolPayTransaction extends Model
{
    use HasFactory;

    public static function boot()
    {
        parent::boot();
        self::deleting(function ($m) {
            throw new Exception("Transaction cannot be deleted.", 1);
            return false;
        });


        self::creating(function ($m) {

            $exist = SchoolPayTransaction::where([
                'school_pay_transporter_id' => $m->school_pay_transporter_id,
            ])->first();
            if ($exist != null) {
                return false;
            }

            $dup = Transaction::where([
                'school_pay_transporter_id' => $m->school_pay_transporter_id,
            ])->first();
            if ($dup != null) {
                $m->status = 'Imported';
            } else {
                $m->status = 'Not Imported';
            }
            return $m;
        });

        //updating
        self::updating(function ($m) {
            $exist = SchoolPayTransaction::where([
                'school_pay_transporter_id' => $m->school_pay_transporter_id,
            ])->first();
            if ($exist != null) {
                if ($exist->id != $m->id) {
                    return false;
                }
            }

            $dup = Transaction::where([
                'school_pay_transporter_id' => $m->school_pay_transporter_id,
            ])->first();
            if ($dup != null) {
                $m->status = 'Imported';
            } else {
                $m->status = 'Not Imported';
            }
            return $m;
        });
    }

    //account 
    public function account()
    {
        return $this->belongsTo(Account::class);
    } 
}
