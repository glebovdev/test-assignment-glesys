<?php

namespace App\Http\Graphql\Mutations;

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
        $input = $args['input'];
        $this->validateInput($input);

        // Use the service to record the heartbeat and get the formatted result
        return [
            'heartbeat' => $this->heartbeatService->recordAndFormatHeartbeat(
                $input['applicationKey'],
                $input['heartbeatKey'],
                $input['unhealthyAfterMinutes']
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
            'applicationKey' => 'required|string|max:255',
            'heartbeatKey' => 'required|string|max:255',
            'unhealthyAfterMinutes' => 'required|integer|min:1',
        ])->validate();
    }
}
