<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

class FeesDataImportRecord extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'fees_data_import_id',
        'enterprise_id',
        'user_id',
        'account_id',
        'index',
        'identify_by',
        'reg_number',
        'school_pay',
        'current_balance',
        'previous_fees_term_balance',
        'updated_balance',
        'total_amount',
        'status',
        'summary',
        'error_message',
        'data',
        'services_data',
        'transaction_hash',
        'row_hash',
        'processed_at',
        'retry_count',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'current_balance' => 'decimal:2',
        'previous_fees_term_balance' => 'decimal:2',
        'updated_balance' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'processed_at' => 'datetime',
        'retry_count' => 'integer',
        'index' => 'integer',
    ];

    /**
     * Status constants
     */
    const STATUS_PENDING = 'Pending';
    const STATUS_PROCESSING = 'Processing';
    const STATUS_COMPLETED = 'Completed';
    const STATUS_FAILED = 'Failed';
    const STATUS_SKIPPED = 'Skipped';

    /**
     * Import belongs to FeesDataImport
     */
    public function import(): BelongsTo
    {
        return $this->belongsTo(FeesDataImport::class, 'fees_data_import_id');
    }

    /**
     * Enterprise belongs to Enterprise
     */
    public function enterprise(): BelongsTo
    {
        return $this->belongsTo(Enterprise::class);
    }

    /**
     * User belongs to User (the student)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Account belongs to Account
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Getter for data attribute
     */
    public function getDataAttribute($value)
    {
        try {
            return $value ? json_decode($value, true) : [];
        } catch (\Exception $e) {
            Log::warning("Failed to decode data for record {$this->id}", [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Setter for data attribute
     */
    public function setDataAttribute($value)
    {
        try {
            $this->attributes['data'] = is_array($value) ? json_encode($value) : $value;
        } catch (\Exception $e) {
            Log::warning("Failed to encode data for record", [
                'error' => $e->getMessage()
            ]);
            $this->attributes['data'] = null;
        }
    }

    /**
     * Getter for services_data attribute
     */
    public function getServicesDataAttribute($value)
    {
        try {
            return $value ? json_decode($value, true) : [];
        } catch (\Exception $e) {
            Log::warning("Failed to decode services_data for record {$this->id}", [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Setter for services_data attribute
     */
    public function setServicesDataAttribute($value)
    {
        try {
            $this->attributes['services_data'] = is_array($value) ? json_encode($value) : $value;
        } catch (\Exception $e) {
            Log::warning("Failed to encode services_data for record", [
                'error' => $e->getMessage()
            ]);
            $this->attributes['services_data'] = null;
        }
    }

    /**
     * Scope for failed records
     */
    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * Scope for successful records
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope for pending records
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope for processing records
     */
    public function scopeProcessing($query)
    {
        return $query->where('status', self::STATUS_PROCESSING);
    }

    /**
     * Scope for skipped records
     */
    public function scopeSkipped($query)
    {
        return $query->where('status', self::STATUS_SKIPPED);
    }

    /**
     * Generate unique row hash
     */
    public static function generateRowHash(int $importId, array $rowData, string $identifier): string
    {
        $dataString = $importId . '|' . $identifier . '|' . json_encode($rowData);
        return hash('sha256', $dataString);
    }

    /**
     * Generate transaction hash
     */
    public static function generateTransactionHash(int $userId, int $importId, array $servicesData): string
    {
        $dataString = $userId . '|' . $importId . '|' . json_encode($servicesData);
        return hash('sha256', $dataString);
    }

    /**
     * Check if record was successful
     */
    public function isSuccessful(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if record has failed
     */
    public function hasFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Check if record can be retried
     */
    public function canRetry(): bool
    {
        return $this->hasFailed() && $this->retry_count < 3;
    }

    /**
     * Mark as processing
     */
    public function markAsProcessing(): bool
    {
        return $this->update([
            'status' => self::STATUS_PROCESSING,
            'error_message' => null,
        ]);
    }

    /**
     * Mark as completed
     */
    public function markAsCompleted(string $summary, float $updatedBalance, array $servicesData = []): bool
    {
        return $this->update([
            'status' => self::STATUS_COMPLETED,
            'summary' => $summary,
            'updated_balance' => $updatedBalance,
            'services_data' => $servicesData,
            'processed_at' => now(),
            'error_message' => null,
        ]);
    }

    /**
     * Mark as failed
     */
    public function markAsFailed(string $errorMessage): bool
    {
        return $this->update([
            'status' => self::STATUS_FAILED,
            'error_message' => $errorMessage,
            'processed_at' => now(),
            'retry_count' => $this->retry_count + 1,
        ]);
    }

    /**
     * Mark as skipped
     */
    public function markAsSkipped(string $reason): bool
    {
        return $this->update([
            'status' => self::STATUS_SKIPPED,
            'summary' => $reason,
            'processed_at' => now(),
        ]);
    }

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        // Generate row hash on creation
        static::creating(function ($record) {
            if (empty($record->row_hash) && !empty($record->data)) {
                $identifier = $record->reg_number ?? $record->school_pay ?? 'unknown';
                $rowData = is_string($record->data) ? json_decode($record->data, true) : $record->data;
                $record->row_hash = self::generateRowHash($record->fees_data_import_id, $rowData, $identifier);
            }
        });
    }
}
