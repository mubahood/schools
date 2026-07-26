<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimetableRoom extends Model
{
    protected $table = 'timetable_rooms';

    protected $fillable = [
        'enterprise_id', 'building_id', 'name', 'capacity',
        'room_type', 'description', 'is_active',
    ];

    public function building()
    {
        return $this->belongsTo(Building::class, 'building_id');
    }

    public function entries()
    {
        return $this->hasMany(TimetableEntry::class);
    }

    public function getDisplayNameAttribute(): string
    {
        $cap = $this->capacity > 0 ? " (cap. {$this->capacity})" : '';
        return $this->name . $cap;
    }
}
