<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IncomeSheet extends Model
{
    protected $fillable = [
        'enterprise_id',
        'term_id',
        'title',
        'date_from',
        'date_to',
        'type',
        'status',
    ];

    public function enterprise()
    {
        return $this->belongsTo(Enterprise::class);
    }

    public function term()
    {
        return $this->belongsTo(Term::class);
    }
}
