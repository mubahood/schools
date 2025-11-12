<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\SystemLog;

class MonitoringService
{
    /**
     * Log system events with context
     */
    public static function logEvent($type, $message, $context = [])
    {
        $logData = [
            'type' => $type,
            'message' => $message,
            'context' => json_encode($context),
            'user_id' => auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
            'created_at' => now(),
        ];

        // Log to file
        Log::channel('daily')->info($message, $context);

        // Log to database for analytics
        try {
            DB::table('system_logs')->insert($logData);
        } catch (\Exception $e) {
            // Fail silently - don't break the application
            Log::error('Failed to log to database: ' . $e->getMessage());
        }
    }

    /**
     * Log SMS events
     */
    public static function logSms($action, $messageId, $status, $details = [])
    {
        self::logEvent('sms', "SMS $action", [
            'message_id' => $messageId,
            'status' => $status,
            'details' => $details
        ]);
    }

    /**
     * Log attendance events
     */
    public static function logAttendance($action, $details = [])
    {
        self::logEvent('attendance', "Attendance $action", $details);
    }

    /**
     * Log wallet transactions
     */
    public static function logWalletTransaction($enterpriseId, $amount, $details)
    {
        self::logEvent('wallet', "Wallet transaction", [
            'enterprise_id' => $enterpriseId,
            'amount' => $amount,
            'details' => $details
        ]);
    }

    /**
     * Log errors with full context
     */
    public static function logError(\Throwable $e, $context = [])
    {
        self::logEvent('error', $e->getMessage(), [
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            'context' => $context
        ]);
    }

    /**
     * Get system health metrics
     */
    public static function getHealthMetrics()
    {
        return [
            'database' => self::checkDatabase(),
            'cache' => self::checkCache(),
            'queue' => self::checkQueue(),
            'storage' => self::checkStorage(),
            'sms_service' => self::checkSmsService(),
        ];
    }

    /**
     * Check database connectivity
     */
    private static function checkDatabase()
    {
        try {
            DB::select('SELECT 1');
            return ['status' => 'healthy', 'message' => 'Database connected'];
        } catch (\Exception $e) {
            return ['status' => 'unhealthy', 'message' => $e->getMessage()];
        }
    }

    /**
     * Check cache
     */
    private static function checkCache()
    {
        try {
            cache()->put('health_check', true, 10);
            $result = cache()->get('health_check');
            return ['status' => $result ? 'healthy' : 'unhealthy', 'message' => 'Cache working'];
        } catch (\Exception $e) {
            return ['status' => 'unhealthy', 'message' => $e->getMessage()];
        }
    }

    /**
     * Check queue
     */
    private static function checkQueue()
    {
        try {
            $failedJobs = DB::table('failed_jobs')->count();
            return [
                'status' => $failedJobs < 100 ? 'healthy' : 'warning',
                'message' => "Failed jobs: $failedJobs"
            ];
        } catch (\Exception $e) {
            return ['status' => 'unknown', 'message' => $e->getMessage()];
        }
    }

    /**
     * Check storage
     */
    private static function checkStorage()
    {
        try {
            $storageSpace = disk_free_space(storage_path());
            $freeGB = round($storageSpace / (1024 * 1024 * 1024), 2);
            return [
                'status' => $freeGB > 1 ? 'healthy' : 'warning',
                'message' => "$freeGB GB free"
            ];
        } catch (\Exception $e) {
            return ['status' => 'unknown', 'message' => $e->getMessage()];
        }
    }

    /**
     * Check SMS service
     */
    private static function checkSmsService()
    {
        $recentFailures = DB::table('direct_messages')
            ->where('status', 'Failed')
            ->where('created_at', '>', now()->subHour())
            ->count();

        return [
            'status' => $recentFailures < 10 ? 'healthy' : 'warning',
            'message' => "Recent failures: $recentFailures"
        ];
    }
}
