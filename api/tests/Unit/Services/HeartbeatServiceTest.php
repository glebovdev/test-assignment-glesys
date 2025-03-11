<?php

namespace Tests\Unit\Services;

use App\Models\Heartbeat;
use App\Repositories\Interfaces\HeartbeatRepositoryInterface;
use App\Services\HeartbeatService;
use Carbon\Carbon;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class HeartbeatServiceTest extends TestCase
{
    private MockInterface $heartbeatRepository;
    private HeartbeatService $heartbeatService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->heartbeatRepository = Mockery::mock(HeartbeatRepositoryInterface::class);
        $this->heartbeatService = new HeartbeatService($this->heartbeatRepository);
    }

    public function test_record_and_format_heartbeat()
    {
        $now = Carbon::parse('2023-07-01T12:00:00+00:00');

        $heartbeat = new Heartbeat([
            'application_key' => 'test-app',
            'heartbeat_key' => 'test-heartbeat',
            'unhealthy_after_minutes' => 30,
            'last_check_in' => $now,
        ]);

        $this->heartbeatRepository->shouldReceive('saveHeartbeat')
            ->once()
            ->with('test-app', 'test-heartbeat', 30)
            ->andReturn($heartbeat);

        $result = $this->heartbeatService->recordAndFormatHeartbeat('test-app', 'test-heartbeat', 30);

        $this->assertEquals([
            'applicationKey' => 'test-app',
            'heartbeatKey' => 'test-heartbeat',
            'unhealthyAfterMinutes' => 30,
            'lastCheckIn' => '2023-07-01T12:00:00+00:00',
        ], $result);
    }

    public function test_get_formatted_unhealthy_heartbeats()
    {
        $this->heartbeatRepository->shouldReceive('getUnhealthyHeartbeats')
            ->once()
            ->with(['app-1'])
            ->andReturn([
                [
                    'application_key' => 'app-1',
                    'heartbeat_key' => 'job-1',
                    'unhealthy_after_minutes' => 5,
                    'last_check_in' => '2023-07-01T12:00:00+00:00',
                ],
                [
                    'application_key' => 'app-1',
                    'heartbeat_key' => 'job-2',
                    'unhealthy_after_minutes' => 10,
                    'last_check_in' => '2023-07-01T13:00:00+00:00',
                ],
            ]);

        $result = $this->heartbeatService->getFormattedUnhealthyHeartbeats(['app-1']);

        $this->assertEquals([
            [
                'applicationKey' => 'app-1',
                'heartbeatKey' => 'job-1',
                'unhealthyAfterMinutes' => 5,
                'lastCheckIn' => '2023-07-01T12:00:00+00:00',
            ],
            [
                'applicationKey' => 'app-1',
                'heartbeatKey' => 'job-2',
                'unhealthyAfterMinutes' => 10,
                'lastCheckIn' => '2023-07-01T13:00:00+00:00',
            ],
        ], $result);
    }
}
