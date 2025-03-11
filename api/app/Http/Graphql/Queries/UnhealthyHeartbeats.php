<?php

declare(strict_types=1);

namespace App\Http\Graphql\Queries;

use App\Constants\HeartbeatKeys;
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
        $applicationKeys = $args[HeartbeatKeys::APPLICATION_KEYS] ?? [];

        return $this->heartbeatService->getFormattedUnhealthyHeartbeats($applicationKeys);
    }
}
