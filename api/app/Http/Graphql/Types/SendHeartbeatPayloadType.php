<?php

namespace App\Http\Graphql\Types;

final class SendHeartbeatPayloadType
{
    public function heartbeat(array $payload): array
    {
        return $payload['heartbeat'];
    }
}
