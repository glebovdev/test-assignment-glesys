<?php

namespace Tests\Feature\Mutations;

use App\Models\Heartbeat;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

final class SendHeartbeatMutationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAsConsumer();
    }

    public function test_it_creates_a_new_heartbeat()
    {
        $query = <<<'GQL'
            mutation {
                sendHeartbeat(input: {
                    applicationKey: "app-1",
                    heartbeatKey: "sync-job",
                    unhealthyAfterMinutes: 5
                }) {
                    heartbeat {
                        applicationKey
                        heartbeatKey
                        unhealthyAfterMinutes
                        lastCheckIn
                    }
                }
            }
            GQL;

        $response = $this->graphql($query)
            ->assertOk()
            ->assertJsonPath('data.sendHeartbeat.heartbeat.applicationKey', 'app-1')
            ->assertJsonPath('data.sendHeartbeat.heartbeat.heartbeatKey', 'sync-job')
            ->assertJsonPath('data.sendHeartbeat.heartbeat.unhealthyAfterMinutes', 5);

        $this->assertDatabaseHas('heartbeats', [
            'application_key' => 'app-1',
            'heartbeat_key' => 'sync-job',
            'unhealthy_after_minutes' => 5,
        ]);
    }

    public function test_it_updates_existing_heartbeat()
    {
        // Create an initial heartbeat
        Heartbeat::create([
            'application_key' => 'app-2',
            'heartbeat_key' => 'backup-job',
            'unhealthy_after_minutes' => 10,
            'last_check_in' => now()->subMinutes(5),
        ]);

        $query = <<<'GQL'
            mutation {
                sendHeartbeat(input: {
                    applicationKey: "app-2",
                    heartbeatKey: "backup-job",
                    unhealthyAfterMinutes: 15
                }) {
                    heartbeat {
                        applicationKey
                        heartbeatKey
                        unhealthyAfterMinutes
                        lastCheckIn
                    }
                }
            }
            GQL;

        $this->graphql($query)
            ->assertOk()
            ->assertJsonPath('data.sendHeartbeat.heartbeat.applicationKey', 'app-2')
            ->assertJsonPath('data.sendHeartbeat.heartbeat.heartbeatKey', 'backup-job')
            ->assertJsonPath('data.sendHeartbeat.heartbeat.unhealthyAfterMinutes', 15);

        $this->assertDatabaseHas('heartbeats', [
            'application_key' => 'app-2',
            'heartbeat_key' => 'backup-job',
            'unhealthy_after_minutes' => 15,
        ]);

        // Make sure there's only one record (it was updated, not duplicated)
        $this->assertEquals(1,
            Heartbeat::where('application_key', 'app-2')
                ->where('heartbeat_key', 'backup-job')
                ->count()
        );
    }

    public function test_it_updates_last_check_in_when_updating_heartbeat()
    {
        // Create an initial heartbeat with a specific last_check_in time
        $initialCheckIn = now()->subMinutes(30);

        $heartbeat = Heartbeat::create([
            'application_key' => 'app-3',
            'heartbeat_key' => 'log-job',
            'unhealthy_after_minutes' => 10,
            'last_check_in' => $initialCheckIn,
        ]);

        // Store the initial last_check_in value for comparison
        $initialLastCheckIn = $heartbeat->last_check_in->toDateTimeString();

        // Sleep briefly to ensure timestamp will be different
        sleep(1);

        $query = <<<'GQL'
            mutation {
                sendHeartbeat(input: {
                    applicationKey: "app-3",
                    heartbeatKey: "log-job",
                    unhealthyAfterMinutes: 10
                }) {
                    heartbeat {
                        applicationKey
                        heartbeatKey
                        unhealthyAfterMinutes
                        lastCheckIn
                    }
                }
            }
            GQL;

        $this->graphql($query)->assertOk();

        // Reload the heartbeat from the database
        $updatedHeartbeat = Heartbeat::where('application_key', 'app-3')
            ->where('heartbeat_key', 'log-job')
            ->first();

        // Verify that last_check_in was updated
        $this->assertNotEquals(
            $initialLastCheckIn,
            $updatedHeartbeat->last_check_in->toDateTimeString(),
            'The last_check_in timestamp should be updated'
        );
    }

    public function test_it_validates_required_fields()
    {
        // Test missing applicationKey
        $query = <<<'GQL'
            mutation {
                sendHeartbeat(input: {
                    heartbeatKey: "sync-job",
                    unhealthyAfterMinutes: 5
                }) {
                    heartbeat {
                        applicationKey
                        heartbeatKey
                        unhealthyAfterMinutes
                        lastCheckIn
                    }
                }
            }
            GQL;

        $response = $this->graphql($query);
        $this->assertStringContainsString('applicationKey', $response->json('errors.0.message'));

        // Test missing heartbeatKey
        $query = <<<'GQL'
            mutation {
                sendHeartbeat(input: {
                    applicationKey: "app-1",
                    unhealthyAfterMinutes: 5
                }) {
                    heartbeat {
                        applicationKey
                        heartbeatKey
                        unhealthyAfterMinutes
                        lastCheckIn
                    }
                }
            }
            GQL;

        $response = $this->graphql($query);
        $this->assertStringContainsString('heartbeatKey', $response->json('errors.0.message'));

        // Test missing unhealthyAfterMinutes
        $query = <<<'GQL'
            mutation {
                sendHeartbeat(input: {
                    applicationKey: "app-1",
                    heartbeatKey: "sync-job"
                }) {
                    heartbeat {
                        applicationKey
                        heartbeatKey
                        unhealthyAfterMinutes
                        lastCheckIn
                    }
                }
            }
            GQL;

        $response = $this->graphql($query);
        $this->assertStringContainsString('unhealthyAfterMinutes', $response->json('errors.0.message'));
    }

    public function test_it_returns_all_required_fields_in_response()
    {
        $query = <<<'GQL'
            mutation {
                sendHeartbeat(input: {
                    applicationKey: "app-4",
                    heartbeatKey: "health-check",
                    unhealthyAfterMinutes: 5
                }) {
                    heartbeat {
                        applicationKey
                        heartbeatKey
                        unhealthyAfterMinutes
                        lastCheckIn
                    }
                }
            }
            GQL;

        $response = $this->graphql($query)
            ->assertOk()
            ->assertJsonPath('data.sendHeartbeat.heartbeat.applicationKey', 'app-4')
            ->assertJsonPath('data.sendHeartbeat.heartbeat.heartbeatKey', 'health-check')
            ->assertJsonPath('data.sendHeartbeat.heartbeat.unhealthyAfterMinutes', 5)
            ->assertJsonStructure([
                'data' => [
                    'sendHeartbeat' => [
                        'heartbeat' => [
                            'applicationKey',
                            'heartbeatKey',
                            'unhealthyAfterMinutes',
                            'lastCheckIn'
                        ]
                    ]
                ]
            ]);
    }
}
