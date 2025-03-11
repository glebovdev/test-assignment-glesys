<?php

namespace App\Http\Graphql\Queries;

use App\Constants\HeartbeatKeys;
use App\Services\HeartbeatService;

final class UnhealthyHeartbeats
{
    /**
     * Constructor with dependency injection
     */
    public function __construct(private readonly HeartbeatService $heartbeatService)
    {
    }

    /**
     * Handle the unhealthyHeartbeats query.
     *
     * @param mixed $source
     * @param array $args
     * @return array
     */
    public function __invoke($source, $args)
    {
        $applicationKeys = $args[HeartbeatKeys::APPLICATION_KEYS] ?? [];

        return $this->heartbeatService->getFormattedUnhealthyHeartbeats($applicationKeys);
    }
}
