<?php

namespace App\Repositories;

use App\Models\Heartbeat;
use App\Repositories\Interfaces\HeartbeatRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

final class EloquentHeartbeatRepository implements HeartbeatRepositoryInterface
{
    /**
     * Create or update a heartbeat
     *
     * @param string $applicationKey
     * @param string $heartbeatKey
     * @param int $unhealthyAfterMinutes
     * @return Heartbeat
     */
    public function saveHeartbeat(string $applicationKey, string $heartbeatKey, int $unhealthyAfterMinutes): Heartbeat
    {
        return DB::transaction(static function () use ($applicationKey, $heartbeatKey, $unhealthyAfterMinutes) {
            return Heartbeat::updateOrCreate(
                [
                    'application_key' => $applicationKey,
                    'heartbeat_key' => $heartbeatKey,
                ],
                [
                    'unhealthy_after_minutes' => $unhealthyAfterMinutes,
                    'last_check_in' => Carbon::now(),
                ]
            );
        });
    }

    /**
     * Get unhealthy heartbeats, optionally filtered by application keys
     *
     * @param array $applicationKeys
     * @return array
     */
    public function getUnhealthyHeartbeats(array $applicationKeys = []): array
    {
        $query = Heartbeat::unhealthy();

        if (!empty($applicationKeys)) {
            $query->whereIn('application_key', $applicationKeys);
        }

        $heartbeats = $query->get();

        // Convert Eloquent models to arrays with properly formatted dates
        return $heartbeats->map(function ($heartbeat) {
            return [
                'application_key' => $heartbeat->application_key,
                'heartbeat_key' => $heartbeat->heartbeat_key,
                'unhealthy_after_minutes' => $heartbeat->unhealthy_after_minutes,
                'last_check_in' => $heartbeat->last_check_in->toIso8601String(),
            ];
        })->all();
    }
}
