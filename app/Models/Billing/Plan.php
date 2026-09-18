<?php

namespace App\Models\Billing;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = ['code', 'name', 'min_students', 'max_students', 'price_6m', 'price_12m', 'features', 'is_public', 'sort'];

    protected $casts = ['is_public' => 'boolean'];

    public const PERIOD_6M = '6m';
    public const PERIOD_12M = '12m';

    public function getFeaturesAttribute($v)
    {
        $d = json_decode($v ?? '[]', true);

        return is_array($d) ? $d : [];
    }

    public function setFeaturesAttribute($v)
    {
        $this->attributes['features'] = json_encode(is_array($v) ? array_values($v) : []);
    }

    public function priceFor(string $period): int
    {
        return (int) ($period === self::PERIOD_12M ? $this->price_12m : $this->price_6m);
    }

    public static function months(string $period): int
    {
        return $period === self::PERIOD_12M ? 12 : 6;
    }

    /** Human range, e.g. "0 – 100 students". */
    public function rangeText(): string
    {
        return $this->max_students === null
            ? number_format($this->min_students) . '+ students'
            : number_format($this->min_students) . ' – ' . number_format($this->max_students) . ' students';
    }

    /** True when this plan covers a school of $count active students. */
    public function fits(int $count): bool
    {
        return $count >= (int) $this->min_students
            && ($this->max_students === null || $count <= (int) $this->max_students);
    }
}
