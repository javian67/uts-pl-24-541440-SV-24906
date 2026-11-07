<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class HealthController extends Controller
{
    /**
     * Perform deep system health checks (Database & Cache).
     */
    public function check(): JsonResponse
    {
        $status = 'healthy';
        $httpCode = 200;
        $checks = [
            'database' => 'ok',
            'cache' => 'ok',
        ];

        // 1. Database Check
        try {
            DB::connection()->getPdo();
        } catch (Throwable $e) {
            $status = 'unhealthy';
            $httpCode = 503;
            $checks['database'] = 'error: ' . $e->getMessage();

            Log::error('Pemeriksaan kesehatan sistem gagal: koneksi basis data terputus', [
                'user' => 'system',
                'aksi' => 'health_check_database',
                'sebab' => $e->getMessage(),
            ]);
        }

        // 2. Cache Check
        try {
            $testKey = 'health_check_' . uniqid('', true);
            Cache::store()->put($testKey, 'ok', 10);
            Cache::store()->forget($testKey);
        } catch (Throwable $e) {
            $status = 'unhealthy';
            $httpCode = 503;
            $checks['cache'] = 'error: ' . $e->getMessage();

            Log::error('Pemeriksaan kesehatan sistem gagal: cache store tidak dapat diakses', [
                'user' => 'system',
                'aksi' => 'health_check_cache',
                'sebab' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'status' => $status,
            'checks' => $checks,
        ], $httpCode);
    }
}
