<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecondaryReportCardItem extends Model
{
    use HasFactory;

    public static function boot()
    {
        parent::boot();

        self::creating(function ($m) {
            $reportItem = SecondaryReportCardItem::where([
                'secondary_report_card_id' => $m->secondary_report_card_id,
                'secondary_subject_id' => $m->secondary_subject_id,
            ])->first();
            if ($reportItem != null) {
                return false;
            }
        });

        self::deleting(function ($m) {
            die("You cannot delete this item.");
        });
    }

    public function subject()
    {
        return $this->belongsTo(SecondarySubject::class, 'secondary_subject_id');
    }

    public function report_card()
    {
        return $this->belongsTo(SecondaryReportCard::class, 'secondary_report_card_id');
    }

    public function getItemsAttribute(){
        return $this->hasMany();
    }
    public function items()
    {


        $acts =  Activity::where([
            'subject_id' => $this->secondary_subject_id,
            'term_id' => $this->report_card->term_id,
        ])->get();
        $activies = [];
        foreach ($acts as $key => $act) {
            $comp = SecondaryCompetence::where([
                'activity_id' => $act->id,
                'administrator_id' => $this->report_card->administrator_id,
            ])->first();
            if ($comp == null) {
                dd("not found");
                $comp = new SecondaryCompetence();
            }
            $act->competance = $comp;
            $activies[] = $act;
        }
        return $activies;
    }

    protected $appends = ['items'];
}
