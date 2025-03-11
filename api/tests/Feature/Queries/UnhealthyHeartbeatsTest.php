<?php

namespace Tests\Feature\Queries;

use App\Models\Heartbeat;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

final class UnhealthyHeartbeatsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAsConsumer();
    }

    public function test_it_returns_unhealthy_heartbeats()
    {
        // Create an unhealthy heartbeat (last check-in is older than unhealthy threshold)
        Heartbeat::create([
            'application_key' => 'app-1',
            'heartbeat_key' => 'sync-job',
            'unhealthy_after_minutes' => 5,
            'last_check_in' => now()->subMinutes(10), // 10 minutes ago, unhealthy after 5
        ]);

        // Create a healthy heartbeat
        Heartbeat::create([
            'application_key' => 'app-2',
            'heartbeat_key' => 'backup-job',
            'unhealthy_after_minutes' => 15,
            'last_check_in' => now()->subMinutes(5), // 5 minutes ago, unhealthy after 15
        ]);

        $query = <<<'GQL'
            query {
                unhealthyHeartbeats {
                    applicationKey
                    heartbeatKey
                    unhealthyAfterMinutes
                    lastCheckIn
                }
            }
            GQL;

        $response = $this->graphql($query);

        // Simply test that the response is OK - we'll validate more specific content later
        $this->assertTrue($response->isOk());

        // Check if there are any errors in the response
        if (isset($response->json()['errors'])) {
            $this->fail('GraphQL response contains errors: ' . json_encode($response->json()['errors']));
        }

        // Verify that there's exactly one heartbeat
        $heartbeats = $response->json('data.unhealthyHeartbeats');
        $this->assertNotNull($heartbeats, 'data.unhealthyHeartbeats path is not found in the response');
        $this->assertCount(1, $heartbeats);

        // Check if the values match what we expect
        $this->assertEquals('app-1', $heartbeats[0]['applicationKey']);
        $this->assertEquals('sync-job', $heartbeats[0]['heartbeatKey']);
        $this->assertEquals(5, $heartbeats[0]['unhealthyAfterMinutes']);
    }

    public function test_it_filters_by_application_keys()
    {
        // Create multiple unhealthy heartbeats for different applications
        Heartbeat::create([
            'application_key' => 'app-1',
            'heartbeat_key' => 'sync-job',
            'unhealthy_after_minutes' => 5,
            'last_check_in' => now()->subMinutes(10),
        ]);

        Heartbeat::create([
            'application_key' => 'app-2',
            'heartbeat_key' => 'backup-job',
            'unhealthy_after_minutes' => 5,
            'last_check_in' => now()->subMinutes(10),
        ]);

        Heartbeat::create([
            'application_key' => 'app-3',
            'heartbeat_key' => 'health-check',
            'unhealthy_after_minutes' => 5,
            'last_check_in' => now()->subMinutes(10),
        ]);

        $query = <<<'GQL'
            query {
                unhealthyHeartbeats(applicationKeys: ["app-1", "app-3"]) {
                    applicationKey
                    heartbeatKey
                }
            }
            GQL;

        $response = $this->graphql($query);

        // Verify the response is OK
        $this->assertTrue($response->isOk());

        // Check if there are any errors in the response
        if (isset($response->json()['errors'])) {
            $this->fail('GraphQL response contains errors: ' . json_encode($response->json()['errors']));
        }

        // Verify there are exactly two heartbeats
        $heartbeats = $response->json('data.unhealthyHeartbeats');
        $this->assertNotNull($heartbeats, 'data.unhealthyHeartbeats path is not found in the response');
        $this->assertCount(2, $heartbeats);

        // Extract application keys from the response
        $applicationKeys = collect($heartbeats)->pluck('applicationKey')->all();

        // Assert that only the filtered applications are returned
        $this->assertEqualsCanonicalizing(['app-1', 'app-3'], $applicationKeys);
        $this->assertNotContains('app-2', $applicationKeys);
    }

    public function test_it_returns_empty_array_when_no_unhealthy_heartbeats()
    {
        // Create only healthy heartbeats
        Heartbeat::create([
            'application_key' => 'app-1',
            'heartbeat_key' => 'sync-job',
            'unhealthy_after_minutes' => 30,
            'last_check_in' => now()->subMinutes(5),
        ]);

        Heartbeat::create([
            'application_key' => 'app-2',
            'heartbeat_key' => 'backup-job',
            'unhealthy_after_minutes' => 20,
            'last_check_in' => now()->subMinutes(10),
        ]);

        $query = <<<'GQL'
            query {
                unhealthyHeartbeats {
                    applicationKey
                    heartbeatKey
                }
            }
            GQL;

        $response = $this->graphql($query);

        // Verify the response is OK
        $this->assertTrue($response->isOk());

        // Check if there are any errors in the response
        if (isset($response->json()['errors'])) {
            $this->fail('GraphQL response contains errors: ' . json_encode($response->json()['errors']));
        }

        // Verify that there are no unhealthy heartbeats
        $heartbeats = $response->json('data.unhealthyHeartbeats');
        $this->assertNotNull($heartbeats, 'data.unhealthyHeartbeats path is not found in the response');
        $this->assertEmpty($heartbeats);
    }

    public function test_it_returns_empty_array_when_filtering_nonexistent_applications()
    {
        // Create an unhealthy heartbeat
        Heartbeat::create([
            'application_key' => 'app-1',
            'heartbeat_key' => 'sync-job',
            'unhealthy_after_minutes' => 5,
            'last_check_in' => now()->subMinutes(10),
        ]);

        $query = <<<'GQL'
            query {
                unhealthyHeartbeats(applicationKeys: ["non-existent-app"]) {
                    applicationKey
                    heartbeatKey
                }
            }
            GQL;

        $response = $this->graphql($query);

        // Verify the response is OK
        $this->assertTrue($response->isOk());

        // Check if there are any errors in the response
        if (isset($response->json()['errors'])) {
            $this->fail('GraphQL response contains errors: ' . json_encode($response->json()['errors']));
        }

        // Verify that there are no unhealthy heartbeats for the non-existent app
        $heartbeats = $response->json('data.unhealthyHeartbeats');
        $this->assertNotNull($heartbeats, 'data.unhealthyHeartbeats path is not found in the response');
        $this->assertEmpty($heartbeats);
    }

    public function test_it_returns_correct_format_for_unhealthy_heartbeats()
    {
        // Create an unhealthy heartbeat with a specific time
        $checkInTime = Carbon::parse('2023-01-01 12:00:00');

        Heartbeat::create([
            'application_key' => 'app-1',
            'heartbeat_key' => 'sync-job',
            'unhealthy_after_minutes' => 5,
            'last_check_in' => $checkInTime,
        ]);

        // Mock the current time to ensure the heartbeat is unhealthy
        Carbon::setTestNow($checkInTime->copy()->addMinutes(10));

        $query = <<<'GQL'
            query {
                unhealthyHeartbeats {
                    applicationKey
                    heartbeatKey
                    unhealthyAfterMinutes
                    lastCheckIn
                }
            }
            GQL;

        $response = $this->graphql($query);

        // Verify the response is OK
        $this->assertTrue($response->isOk());

        // Check if there are any errors in the response
        if (isset($response->json()['errors'])) {
            $this->fail('GraphQL response contains errors: ' . json_encode($response->json()['errors']));
        }

        // Verify that there's exactly one heartbeat
        $heartbeats = $response->json('data.unhealthyHeartbeats');
        $this->assertNotNull($heartbeats, 'data.unhealthyHeartbeats path is not found in the response');
        $this->assertCount(1, $heartbeats);

        $heartbeat = $heartbeats[0];

        // Verify the format of the response
        $this->assertEquals('app-1', $heartbeat['applicationKey']);
        $this->assertEquals('sync-job', $heartbeat['heartbeatKey']);
        $this->assertEquals(5, $heartbeat['unhealthyAfterMinutes']);
        $this->assertEquals($checkInTime->toIso8601String(), $heartbeat['lastCheckIn']);

        // Reset the mock time
        Carbon::setTestNow();
    }
}
