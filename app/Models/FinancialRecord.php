<?php

namespace App\Models;

use App\Services\Finance\FinanceService;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A single line of the school's ledger: either a budget line or money spent.
 *
 * The rules live in FinanceService. The hooks below only make sure that a row
 * written by any route — this module, a legacy screen, a console command —
 * goes through the same normalisation.
 */
class FinancialRecord extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * amount, academic_year_id and parent_account_id are deliberately absent:
     * they are derived from the term, the account and quantity x unit price,
     * so a crafted request cannot dictate what a record is worth or which
     * vote it lands under.
     */
    protected $fillable = [
        'enterprise_id',
        'account_id',
        'term_id',
        'supplier_id',
        'created_by_id',
        'type',
        'description',
        'payment_date',
        'payment_method',
        'quantity',
        'unit_price',
        'is_credit',
        'credit_amount',
    ];

    protected $casts = [
        'amount' => 'integer',
        'is_credit' => 'boolean',
        'quantity' => 'integer',
        'unit_price' => 'integer',
        'credit_amount' => 'integer',
        'payment_date' => 'date',
        'deleted_at' => 'datetime',
    ];

    /**
     * Set by FinanceService::update() when the caller genuinely supplied
     * quantity and unit price. False means "do not touch the money".
     */
    public bool $moneyGiven = true;

    public function created_by()
    {
        return $this->belongsTo(Administrator::class, 'created_by_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Administrator::class, 'supplier_id');
    }

    public function par()
    {
        return $this->belongsTo(AccountParent::class, 'parent_account_id');
    }

    public function term()
    {
        return $this->belongsTo(Term::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function creditor_record()
    {
        return $this->hasOne(CreditorRecord::class, 'financial_record_id');
    }

    public function audits()
    {
        return $this->hasMany(FinanceAudit::class, 'subject_id')
            ->where('subject_type', 'financial_record')->orderByDesc('id');
    }

    public function isExpenditure(): bool
    {
        return $this->type === FinanceService::TYPE_EXPENDITURE;
    }

    protected static function boot()
    {
        parent::boot();

        self::creating(function (self $m) {
            if (!$m->created_by_id) {
                $m->created_by_id = \Encore\Admin\Facades\Admin::user()->id
                    ?? optional(Enterprise::find($m->enterprise_id))->administrator_id;
            }
            FinanceService::normalise($m, true);
        });

        self::updating(function (self $m) {
            FinanceService::normalise($m, $m->moneyGiven);
        });
    }
}
