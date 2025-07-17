<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminUserExtension extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'student_sourced_by_agent',
        'student_sourced_by_agent_id',
        'student_sourced_by_agent_commission',
        'student_sourced_by_agent_commission_paid',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
