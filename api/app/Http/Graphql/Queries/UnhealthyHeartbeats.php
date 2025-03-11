<?php

namespace App\Http\Graphql\Queries;

use App\Services\HeartbeatService;

final class UnhealthyHeartbeats
{
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
        $applicationKeys = $args['applicationKeys'] ?? [];

        return $this->heartbeatService->getFormattedUnhealthyHeartbeats($applicationKeys);
    }
}
