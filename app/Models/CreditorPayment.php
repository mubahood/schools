<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditorPayment extends Model
{
    use HasFactory;


    /**
     * Mass-assignable columns. See the note on FinancialRecord: without this
     * every CreditorPayment::create() throws MassAssignmentException.
     */
    protected $fillable = [
        'enterprise_id',
        'creditor_record_id',
        'created_by_id',
        'amount_paid',
        'payment_date',
        'payment_method',
        'reference',
        'notes',
    ];
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
