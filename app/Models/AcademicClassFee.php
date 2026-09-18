<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicClassFee extends Model
{
    use HasFactory;

    protected $fillable = ['enterprise_id', 'academic_class_id', 'name', 'amount'];


    public static function process_bill($m)
    {

        if ($m->academic_class == null) {
            throw new \Exception("Academic class is required", 1);
        }
        $ent = Enterprise::find($m->enterprise_id);
        if ($ent == null) {
            throw new \Exception("Enterprise not found", 1);
        } 
        $active_term = $ent->active_term();
        if($active_term == null){
            return; 
        } 
        if($active_term->id != $m->due_term_id){
            return; 
        }
        if ($m->academic_class != null) {
            $students = User::where([
                'current_class_id' => $m->academic_class_id,
                'enterprise_id' => $m->enterprise_id,
                'status' => 1
            ])->get();
            foreach ($students as $key => $student) {
                $student->update_fees();
            }
        }
    }

    function due_term()
    {
        return $this->belongsTo(Term::class, 'due_term_id');
    }
    function academic_class()
    {
        if ($this->type == 'Theology') {
            return $this->belongsTo(TheologyClass::class, 'theology_class_id');
        }
        return $this->belongsTo(AcademicClass::class);
    }

    /**
     * Refuses a fee definition that duplicates one already on the same class and
     * term.
     *
     * Creating a fee bills every student in the class immediately (see
     * process_bill), and the "already billed" guard in User::update_fees keys on
     * academic_class_fee_id — so a second definition is a second id and bills the
     * whole class all over again. That is exactly how 810 BFSS students were
     * charged BASIC FEES twice for Term 3 2026: the fee was set up on 7 May and
     * again on 18/19 August.
     *
     * A class may legitimately carry several *different* fees in one term, so the
     * name is part of the key; only a same-named repeat is refused.
     */
    public static function assertNotDuplicate($m)
    {
        $termId = (int) $m->due_term_id;
        if ($termId < 1) {
            return; // No term to collide on.
        }

        $name = trim((string) $m->name);
        if ($name === '') {
            return;
        }

        $query = self::where('enterprise_id', $m->enterprise_id)
            ->where('due_term_id', $termId)
            ->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower($name)]);

        // Secular fees hang off a class, theology fees off a theology class.
        if (!empty($m->academic_class_id)) {
            $query->where('academic_class_id', $m->academic_class_id);
        } else {
            $query->whereNull('academic_class_id');
        }
        if (!empty($m->theology_class_id)) {
            $query->where('theology_class_id', $m->theology_class_id);
        }

        if (!empty($m->id)) {
            $query->where('id', '!=', $m->id);
        }

        $existing = $query->first();
        if ($existing === null) {
            return;
        }

        $term      = Term::find($termId);
        $termLabel = $term ? ('Term ' . $term->name . ($term->academic_year ? ' ' . $term->academic_year->name : '')) : "term #{$termId}";
        $className = $m->academic_class ? $m->academic_class->name : "class #{$m->academic_class_id}";

        throw new \Exception(
            "\"{$name}\" already exists for {$className} in {$termLabel} (fee #{$existing->id}, "
                . "UGX " . number_format($existing->amount) . "). Edit that fee instead — adding a second one "
                . "would bill every student in the class a second time.",
            1
        );
    }

    public static function boot()
    {
        parent::boot();

        self::creating(function ($m) {
            self::assertNotDuplicate($m);
        });

        // An edit can move a fee onto a class or term that already has one.
        self::updating(function ($m) {
            self::assertNotDuplicate($m);
        });

        self::created(function ($m) {
            AcademicClassFee::process_bill($m);
        });
        self::updated(function ($m) {
            AcademicClassFee::process_bill($m);
        });
    }

    protected  $appends = ['amount_text'];
    function getAmountTextAttribute()
    {
        return "UGX " . number_format($this->amount);
    }
}
