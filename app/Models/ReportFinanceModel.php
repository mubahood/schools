<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportFinanceModel extends Model
{
    protected $table = 'report_finances';
    use HasFactory;

    public function ent()
    {
        return $this->belongsTo(Enterprise::class);
    }
    
    public function academic_year()
    {
        return $this->belongsTo(AcademicYear::class);
    } 
    public function term ()
    {
        return $this->belongsTo(Term::class);
    } 
    public function academic_term () 
    {
        return $this->belongsTo(Term::class,'term_id');
    }

}
