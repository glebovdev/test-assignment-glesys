<?php

namespace App\Http\Graphql\Types;

final class Heartbeat
{
    public function applicationKey(array $heartbeat): string
    {
        return $heartbeat['applicationKey'] ?? $heartbeat['application_key'] ?? '';
    }

    public function heartbeatKey(array $heartbeat): string
    {
        return $heartbeat['heartbeatKey'] ?? $heartbeat['heartbeat_key'] ?? '';
    }

    public function unhealthyAfterMinutes(array $heartbeat): int
    {
        return $heartbeat['unhealthyAfterMinutes'] ?? $heartbeat['unhealthy_after_minutes'] ?? 0;
    }

    public function lastCheckIn(array $heartbeat): string
    {
        return $heartbeat['lastCheckIn'] ?? $heartbeat['last_check_in'] ?? '';
    }
}
