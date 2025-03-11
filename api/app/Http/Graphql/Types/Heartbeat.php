<?php

namespace App\Http\Graphql\Types;

use App\Constants\HeartbeatKeys;

final class Heartbeat
{
    public function applicationKey(array $heartbeat): string
    {
        return $heartbeat[HeartbeatKeys::APPLICATION_KEY] ?? $heartbeat[HeartbeatKeys::DB_APPLICATION_KEY] ?? '';
    }

    public function heartbeatKey(array $heartbeat): string
    {
        return $heartbeat[HeartbeatKeys::HEARTBEAT_KEY] ?? $heartbeat[HeartbeatKeys::DB_HEARTBEAT_KEY] ?? '';
    }

    public function unhealthyAfterMinutes(array $heartbeat): int
    {
        return $heartbeat[HeartbeatKeys::UNHEALTHY_AFTER_MINUTES] ?? $heartbeat[HeartbeatKeys::DB_UNHEALTHY_AFTER_MINUTES] ?? 0;
    }

    public function lastCheckIn(array $heartbeat): string
    {
        return $heartbeat[HeartbeatKeys::LAST_CHECK_IN] ?? $heartbeat[HeartbeatKeys::DB_LAST_CHECK_IN] ?? '';
    }
}
