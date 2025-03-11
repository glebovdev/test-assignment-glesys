<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Constants\HeartbeatKeys;
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
                    HeartbeatKeys::DB_APPLICATION_KEY => $applicationKey,
                    HeartbeatKeys::DB_HEARTBEAT_KEY => $heartbeatKey,
                ],
                [
                    HeartbeatKeys::DB_UNHEALTHY_AFTER_MINUTES => $unhealthyAfterMinutes,
                    HeartbeatKeys::DB_LAST_CHECK_IN => Carbon::now(),
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
            $query->whereIn(HeartbeatKeys::DB_APPLICATION_KEY, $applicationKeys);
        }

        $heartbeats = $query->get();

        // Convert Eloquent models to arrays with properly formatted dates
        return $heartbeats->map(function ($heartbeat) {
            return [
                HeartbeatKeys::DB_APPLICATION_KEY => $heartbeat->{HeartbeatKeys::DB_APPLICATION_KEY},
                HeartbeatKeys::DB_HEARTBEAT_KEY => $heartbeat->{HeartbeatKeys::DB_HEARTBEAT_KEY},
                HeartbeatKeys::DB_UNHEALTHY_AFTER_MINUTES => $heartbeat->{HeartbeatKeys::DB_UNHEALTHY_AFTER_MINUTES},
                HeartbeatKeys::DB_LAST_CHECK_IN => $heartbeat->{HeartbeatKeys::DB_LAST_CHECK_IN}->toIso8601String(),
            ];
        })->all();
    }
}
