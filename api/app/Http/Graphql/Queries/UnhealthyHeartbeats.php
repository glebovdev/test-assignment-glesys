<?php

namespace App\Http\Graphql\Queries;

use App\Models\Heartbeat;

final class UnhealthyHeartbeats
{
    public function __invoke($source, $args, $context, $info)
    {
        $query = Heartbeat::unhealthy();

        // Apply application keys filter if provided
        if (isset($args['applicationKeys']) && !empty($args['applicationKeys'])) {
            $query->whereIn('application_key', $args['applicationKeys']);
        }

        $heartbeats = $query->get();

        // Convert database model to GraphQL type format
        return $heartbeats->map(function ($heartbeat) {
            return [
                'applicationKey' => $heartbeat->application_key,
                'heartbeatKey' => $heartbeat->heartbeat_key,
                'unhealthyAfterMinutes' => $heartbeat->unhealthy_after_minutes,
                'lastCheckIn' => $heartbeat->last_check_in->toIso8601String(),
            ];
        })->all();
    }
}
