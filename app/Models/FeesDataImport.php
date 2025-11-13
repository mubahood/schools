<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FeesDataImport extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'enterprise_id',
        'created_by_id',
        'term_id',
        'title',
        'identify_by',
        'school_pay_column',
        'reg_number_column',
        'services_columns',
        'current_balance_column',
        'previous_fees_term_balance_column',
        'cater_for_balance',
        'status',
        'summary',
        'file_path',
        'file_hash',
        'batch_identifier',
        'is_locked',
        'locked_at',
        'locked_by_id',
        'started_at',
        'completed_at',
        'total_rows',
        'processed_rows',
        'success_count',
        'failed_count',
        'skipped_count',
        'validation_errors',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'is_locked' => 'boolean',
        'locked_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'total_rows' => 'integer',
        'processed_rows' => 'integer',
        'success_count' => 'integer',
        'failed_count' => 'integer',
        'skipped_count' => 'integer',
    ];

    /**
     * Status constants
     */
    const STATUS_PENDING = 'Pending';
    const STATUS_PROCESSING = 'Processing';
    const STATUS_COMPLETED = 'Completed';
    const STATUS_FAILED = 'Failed';
    const STATUS_CANCELLED = 'Cancelled';

    /**
     * Creator belongs to User
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    /**
     * Enterprise belongs to Enterprise
     */
    public function enterprise(): BelongsTo
    {
        return $this->belongsTo(Enterprise::class);
    }

    /**
     * Term belongs to Term
     */
    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    /**
     * Locked by user
     */
    public function lockedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by_id');
    }

    /**
     * Import has many records
     */
    public function records(): HasMany
    {
        return $this->hasMany(FeesDataImportRecord::class, 'fees_data_import_id');
    }

    /**
     * Get failed records
     */
    public function failedRecords(): HasMany
    {
        return $this->records()->where('status', FeesDataImportRecord::STATUS_FAILED);
    }

    /**
     * Get successful records
     */
    public function successfulRecords(): HasMany
    {
        return $this->records()->where('status', FeesDataImportRecord::STATUS_COMPLETED);
    }

    /**
     * Getter for services_columns attribute with try-catch
     */
    public function getServicesColumnsAttribute($value)
    {
        try {
            return $value ? json_decode($value, true) : [];
        } catch (\Exception $e) {
            Log::warning("Failed to decode services_columns for import {$this->id}", [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Setter for services_columns attribute with try-catch
     */
    public function setServicesColumnsAttribute($value)
    {
        try {
            $this->attributes['services_columns'] = is_array($value) ? json_encode($value) : $value;
        } catch (\Exception $e) {
            Log::warning("Failed to encode services_columns for import", [
                'error' => $e->getMessage()
            ]);
            $this->attributes['services_columns'] = null;
        }
    }

    /**
     * Getter for validation_errors attribute
     */
    public function getValidationErrorsAttribute($value)
    {
        try {
            return $value ? json_decode($value, true) : [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Setter for validation_errors attribute
     */
    public function setValidationErrorsAttribute($value)
    {
        try {
            $this->attributes['validation_errors'] = is_array($value) ? json_encode($value) : $value;
        } catch (\Exception $e) {
            $this->attributes['validation_errors'] = null;
        }
    }

    /**
     * Scope for pending imports
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope for processing imports
     */
    public function scopeProcessing($query)
    {
        return $query->where('status', self::STATUS_PROCESSING);
    }

    /**
     * Scope for completed imports
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope for failed imports
     */
    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * Check if import is locked
     */
    public function isLocked(): bool
    {
        return $this->is_locked && $this->locked_at && $this->locked_at->diffInMinutes(now()) < 30;
    }

    /**
     * Lock the import
     * @param User|\Encore\Admin\Auth\Database\Administrator $user
     */
    public function lock($user): bool
    {
        if ($this->isLocked() && $this->locked_by_id !== $user->id) {
            return false;
        }

        return $this->update([
            'is_locked' => true,
            'locked_at' => now(),
            'locked_by_id' => $user->id,
        ]);
    }

    /**
     * Unlock the import
     */
    public function unlock(): bool
    {
        return $this->update([
            'is_locked' => false,
            'locked_at' => null,
            'locked_by_id' => null,
        ]);
    }

    /**
     * Check if import can be processed
     * Made less strict - allows reprocessing of completed imports
     */
    public function canBeProcessed(): bool
    {
        // Only block if currently processing or locked by another user
        return $this->status !== self::STATUS_PROCESSING && !$this->isLocked();
    }

    /**
     * Check if import is complete
     */
    public function isComplete(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if import has failed
     */
    public function hasFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Get progress percentage
     */
    public function getProgressPercentage(): float
    {
        if ($this->total_rows == 0) {
            return 0;
        }
        return round(($this->processed_rows / $this->total_rows) * 100, 2);
    }

    /**
     * Generate unique batch identifier
     */
    public static function generateBatchIdentifier(int $enterpriseId): string
    {
        return 'BATCH_' . $enterpriseId . '_' . now()->format('YmdHis') . '_' . substr(md5(uniqid()), 0, 8);
    }

    /**
     * Check for duplicate file hash
     */
    public static function isDuplicateFile(string $fileHash, int $enterpriseId, ?int $excludeId = null): bool
    {
        $query = self::where('file_hash', $fileHash)
            ->where('enterprise_id', $enterpriseId)
            ->where('status', self::STATUS_COMPLETED);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        // Generate batch identifier on creation
        static::creating(function ($import) {
            if (empty($import->batch_identifier)) {
                $import->batch_identifier = self::generateBatchIdentifier($import->enterprise_id);
            }
        });

        // Clean up locks on old processing imports
        static::updating(function ($import) {
            if ($import->isDirty('status') && $import->status === self::STATUS_PROCESSING) {
                // Check for stale locks (older than 30 minutes)
                if ($import->locked_at && $import->locked_at->diffInMinutes(now()) > 30) {
                    $import->unlock();
                }
            }
        });
    }
}
