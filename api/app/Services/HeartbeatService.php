<?php

namespace App\Services;

use App\Constants\HeartbeatKeys;
use App\Models\Heartbeat;
use App\Repositories\Interfaces\HeartbeatRepositoryInterface;

final class HeartbeatService
{
    public function __construct(private readonly HeartbeatRepositoryInterface $heartbeatRepository)
    {
    }

    /**
     * Record a heartbeat for an application
     *
     * @param string $applicationKey
     * @param string $heartbeatKey
     * @param int $unhealthyAfterMinutes
     * @return Heartbeat
     */
    public function recordHeartbeat(string $applicationKey, string $heartbeatKey, int $unhealthyAfterMinutes): Heartbeat
    {
        return $this->heartbeatRepository->saveHeartbeat($applicationKey, $heartbeatKey, $unhealthyAfterMinutes);
    }

    /**
     * Record a heartbeat and format it for GraphQL response
     *
     * @param string $applicationKey
     * @param string $heartbeatKey
     * @param int $unhealthyAfterMinutes
     * @return array
     */
    public function recordAndFormatHeartbeat(string $applicationKey, string $heartbeatKey, int $unhealthyAfterMinutes): array
    {
        $heartbeat = $this->recordHeartbeat($applicationKey, $heartbeatKey, $unhealthyAfterMinutes);

        return $this->formatHeartbeat($heartbeat);
    }

    /**
     * Format a Heartbeat model for GraphQL response
     *
     * @param Heartbeat $heartbeat
     * @return array
     */
    private function formatHeartbeat(Heartbeat $heartbeat): array
    {
        return [
            HeartbeatKeys::APPLICATION_KEY => $heartbeat->{HeartbeatKeys::DB_APPLICATION_KEY},
            HeartbeatKeys::HEARTBEAT_KEY => $heartbeat->{HeartbeatKeys::DB_HEARTBEAT_KEY},
            HeartbeatKeys::UNHEALTHY_AFTER_MINUTES => $heartbeat->{HeartbeatKeys::DB_UNHEALTHY_AFTER_MINUTES},
            HeartbeatKeys::LAST_CHECK_IN => $heartbeat->{HeartbeatKeys::DB_LAST_CHECK_IN}->toIso8601String(),
        ];
    }

    /**
     * Get all unhealthy heartbeats, optionally filtered by application keys
     *
     * @param array $applicationKeys
     * @return array
     */
    public function getUnhealthyHeartbeats(array $applicationKeys = []): array
    {
        return $this->heartbeatRepository->getUnhealthyHeartbeats($applicationKeys);
    }

    /**
     * Get formatted unhealthy heartbeats for GraphQL response
     *
     * @param array $applicationKeys
     * @return array
     */
    public function getFormattedUnhealthyHeartbeats(array $applicationKeys = []): array
    {
        $heartbeats = $this->getUnhealthyHeartbeats($applicationKeys);

        return array_map(static function ($heartbeat) {
            return [
                HeartbeatKeys::APPLICATION_KEY => $heartbeat[HeartbeatKeys::DB_APPLICATION_KEY],
                HeartbeatKeys::HEARTBEAT_KEY => $heartbeat[HeartbeatKeys::DB_HEARTBEAT_KEY],
                HeartbeatKeys::UNHEALTHY_AFTER_MINUTES => $heartbeat[HeartbeatKeys::DB_UNHEALTHY_AFTER_MINUTES],
                HeartbeatKeys::LAST_CHECK_IN => $heartbeat[HeartbeatKeys::DB_LAST_CHECK_IN],
            ];
        }, $heartbeats);
    }
}
