<?php

declare(strict_types=1);

namespace App\Repositories\Interfaces;

use App\Models\Heartbeat;

interface HeartbeatRepositoryInterface
{
    public function saveHeartbeat(string $applicationKey, string $heartbeatKey, int $unhealthyAfterMinutes): Heartbeat;

    public function getUnhealthyHeartbeats(array $applicationKeys = []): array;
}
