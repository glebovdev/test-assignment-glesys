<?php

declare(strict_types=1);

namespace App\Http\Graphql\Mutations;

use App\Constants\HeartbeatKeys;
use App\Services\HeartbeatService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final class SendHeartbeat
{
    public function __construct(private readonly HeartbeatService $heartbeatService)
    {
    }

    /**
     * Handle the sendHeartbeat mutation.
     *
     * @param mixed $root The parent object (null for root-level queries)
     * @param array $args The arguments passed to the mutation
     * @return array
     * @throws ValidationException
     */
    public function __invoke($root, array $args): array
    {
        // Extract and validate input parameters
        $input = $args[HeartbeatKeys::INPUT];
        $this->validateInput($input);

        // Use the service to record the heartbeat and get the formatted result
        return [
            HeartbeatKeys::HEARTBEAT => $this->heartbeatService->recordAndFormatHeartbeat(
                $input[HeartbeatKeys::APPLICATION_KEY],
                $input[HeartbeatKeys::HEARTBEAT_KEY],
                $input[HeartbeatKeys::UNHEALTHY_AFTER_MINUTES]
            ),
        ];
    }

    /**
     * Validate the input parameters.
     *
     * @param array $input
     * @throws ValidationException
     */
    private function validateInput(array $input): void
    {
        Validator::make($input, [
            HeartbeatKeys::APPLICATION_KEY => 'required|string|max:255',
            HeartbeatKeys::HEARTBEAT_KEY => 'required|string|max:255',
            HeartbeatKeys::UNHEALTHY_AFTER_MINUTES => 'required|integer|min:1',
        ])->validate();
    }
}
