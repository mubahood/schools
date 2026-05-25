<?php

namespace App\Models;

use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

class StudentProgressiveReport extends Model
{
    use HasFactory;

    protected $table = 'student_progressive_reports';

    protected $appends = ['student_text', 'academic_class_text'];

    public static function boot()
    {
        parent::boot();

        // Prevent duplicate: one report per student × progressive assessment
        self::creating(function ($m) {
            $exists = self::where([
                'student_id'                => $m->student_id,
                'progressive_assessment_id' => $m->progressive_assessment_id,
            ])->first();
            if ($exists) return false;
        });

        // Cascade delete items when report is deleted
        self::deleting(function ($m) {
            $m->items()->delete();
        });
    }

    // ── relationships ────────────────────────────────────────────────────────
    public function progressive_assessment()
    {
        return $this->belongsTo(ProgressiveAssessment::class);
    }

    public function term()
    {
        return $this->belongsTo(Term::class);
    }

    public function academic_year()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function owner()
    {
        return $this->belongsTo(Administrator::class, 'student_id');
    }

    public function academic_class()
    {
        return $this->belongsTo(AcademicClass::class);
    }

    public function stream()
    {
        return $this->belongsTo(AcademicClassSctream::class, 'stream_id');
    }

    public function ent()
    {
        return $this->belongsTo(Enterprise::class, 'enterprise_id');
    }

    public function items()
    {
        return $this->hasMany(StudentProgressiveReportItem::class);
    }

    // ── comment generation (mirrors StudentReportCard::generate_comment) ─────
    public function generate_comment()
    {
        if (!$this->owner || $this->total_aggregates < 3) return;

        $comments = ReportComment::where('enterprise_id', $this->enterprise_id)
            ->where('min_score', '<=', $this->total_aggregates)
            ->where('max_score', '>=', $this->total_aggregates)
            ->get();

        if ($comments->isEmpty()) return;

        $comment = $comments->shuffle()->first();
        $owner   = $this->owner;
        $tokens  = ['[NAME]' => $owner->name];

        if (strtolower(trim($owner->sex ?? '')) === 'male') {
            $tokens += ['[HE_OR_SHE]' => 'He', '[HIS_OR_HER]' => 'His', '[HIM_OR_HER]' => 'Him'];
        } elseif (strtolower(trim($owner->sex ?? '')) === 'female') {
            $tokens += ['[HE_OR_SHE]' => 'She', '[HIS_OR_HER]' => 'Her', '[HIM_OR_HER]' => 'Her'];
        } else {
            $tokens += ['[HE_OR_SHE]' => 'He/She', '[HIS_OR_HER]' => 'His/Her', '[HIM_OR_HER]' => 'Him/Her'];
        }

        $this->class_teacher_comment = str_replace(array_keys($tokens), array_values($tokens), $comment->comment);
        $this->head_teacher_comment  = str_replace(array_keys($tokens), array_values($tokens), $comment->hm_comment ?? '');
        $this->save();
    }

    // ── PDF generation ───────────────────────────────────────────────────────
    public function download_self()
    {
        if (!$this->owner) throw new Exception('Student not found.');

        $pa = $this->progressive_assessment;
        if (!$pa) throw new Exception('Progressive Assessment record not found.');

        // Collect this student's report (may span multiple—but here it is one per PA)
        $reports = self::where([
            'progressive_assessment_id' => $pa->id,
            'student_id'                => $this->student_id,
        ])->orderBy('id')->get();

        if ($reports->isEmpty()) throw new Exception('No report found for this student.');

        $name = $this->id . '-' . $this->owner->name . '-' . ($pa->title ?? 'test-report');
        $name = preg_replace('/[^A-Za-z0-9\-_]/', '-', $name) . '.pdf';
        $storePath = public_path('storage/files/' . $name);

        if (file_exists($storePath)) unlink($storePath);

        $viewData = [
            'items'       => $reports,
            'ent'         => $pa->enterprise,
            'assessment'  => $pa,
        ];

        if (isset($_GET['html'])) {
            echo view('progressive-assessment.print', $viewData);
            die();
        }

        $pdf = App::make('dompdf.wrapper');
        $pdf->setPaper('A4', 'landscape');
        $pdf->loadHTML(view('progressive-assessment.print', $viewData));

        try {
            file_put_contents($storePath, $pdf->output());
        } catch (Exception $e) {
            throw new Exception('Error saving PDF: ' . $e->getMessage());
        }

        $this->pdf_url        = $name;
        $this->date_generated = Carbon::now();
        $this->is_ready       = $pa->display_to_parents === 'Yes';
        $this->save();

        return $name;
    }

    // ── appended attributes ───────────────────────────────────────────────────
    public function getStudentTextAttribute(): string
    {
        return $this->owner?->name ?? 'N/A';
    }

    public function getAcademicClassTextAttribute(): string
    {
        return $this->academic_class?->name ?? '';
    }
}
