<?php

namespace App\Http\Graphql\Types;

use App\Constants\HeartbeatKeys;

final class SendHeartbeatPayloadType
{
    public function heartbeat(array $payload): array
    {
        return $payload[HeartbeatKeys::HEARTBEAT];
    }
}
