<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use App\Models\Enterprise;
use App\Models\AcademicClass;
use App\Models\User;

class CacheService
{
    /**
     * Cache duration in seconds (1 hour)
     */
    const CACHE_TTL = 3600;

    /**
     * Get enterprise with caching
     */
    public static function getEnterprise($enterpriseId)
    {
        return Cache::remember(
            "enterprise:{$enterpriseId}",
            self::CACHE_TTL,
            fn() => Enterprise::find($enterpriseId)
        );
    }

    /**
     * Get active classes for enterprise
     */
    public static function getActiveClasses($academicYearId)
    {
        return Cache::remember(
            "classes:academic_year:{$academicYearId}",
            self::CACHE_TTL,
            fn() => AcademicClass::where('academic_year_id', $academicYearId)->get()
        );
    }

    /**
     * Get active students for enterprise
     */
    public static function getActiveStudents($enterpriseId)
    {
        return Cache::remember(
            "students:enterprise:{$enterpriseId}",
            self::CACHE_TTL,
            fn() => User::where('enterprise_id', $enterpriseId)
                ->where('status', 1)
                ->where('user_type', 'student')
                ->get()
        );
    }

    /**
     * Get teachers for enterprise
     */
    public static function getTeachers($enterpriseId)
    {
        return Cache::remember(
            "teachers:enterprise:{$enterpriseId}",
            self::CACHE_TTL,
            fn() => User::where('enterprise_id', $enterpriseId)
                ->where('status', 1)
                ->whereIn('user_type', ['employee', 'admin'])
                ->orderBy('name', 'ASC')
                ->get()
        );
    }

    /**
     * Clear enterprise related caches
     */
    public static function clearEnterpriseCache($enterpriseId)
    {
        Cache::forget("enterprise:{$enterpriseId}");
        Cache::forget("students:enterprise:{$enterpriseId}");
        Cache::forget("teachers:enterprise:{$enterpriseId}");
    }

    /**
     * Clear class related caches
     */
    public static function clearClassCache($academicYearId)
    {
        Cache::forget("classes:academic_year:{$academicYearId}");
    }

    /**
     * Get wallet balance with caching (short TTL)
     */
    public static function getWalletBalance($enterpriseId)
    {
        return Cache::remember(
            "wallet:enterprise:{$enterpriseId}",
            300, // 5 minutes only - financial data
            fn() => Enterprise::find($enterpriseId)?->wallet_balance ?? 0
        );
    }

    /**
     * Invalidate wallet cache (call after wallet transactions)
     */
    public static function invalidateWalletCache($enterpriseId)
    {
        Cache::forget("wallet:enterprise:{$enterpriseId}");
    }
}
