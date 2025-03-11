<?php

namespace App\Http\Graphql\Mutations;

use App\Models\Heartbeat;
use Carbon\Carbon;

final class SendHeartbeat
{
    public function __invoke($root, array $args): array
    {
        $input = $args['input'];
        $applicationKey = $input['applicationKey'];
        $heartbeatKey = $input['heartbeatKey'];
        $unhealthyAfterMinutes = $input['unhealthyAfterMinutes'];

        $heartbeat = Heartbeat::updateOrCreate(
            [
                'application_key' => $applicationKey,
                'heartbeat_key' => $heartbeatKey,
            ],
            [
                'unhealthy_after_minutes' => $unhealthyAfterMinutes,
                'last_check_in' => Carbon::now(),
            ]
        );

        return [
            'heartbeat' => [
                'application_key' => $heartbeat->application_key,
                'heartbeat_key' => $heartbeat->heartbeat_key,
                'unhealthy_after_minutes' => $heartbeat->unhealthy_after_minutes,
                'last_check_in' => $heartbeat->last_check_in->toIso8601String(),
                'is_healthy' => true,
            ],
        ];
    }
}
