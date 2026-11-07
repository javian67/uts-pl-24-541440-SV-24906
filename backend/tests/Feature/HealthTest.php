<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HealthTest extends TestCase
{
    /**
     * Test health check endpoint returns 200 and healthy JSON when DB and Cache are ok.
     */
    public function test_health_check_returns_healthy_when_all_services_ok(): void
    {
        $response = $this->getJson('/health');

        $response->assertStatus(200)
            ->assertExactJson([
                'status' => 'healthy',
                'checks' => [
                    'database' => 'ok',
                    'cache' => 'ok',
                ],
            ]);
    }

    /**
     * Test health check endpoint returns 200 via api prefix as well.
     */
    public function test_health_check_returns_healthy_via_api_route(): void
    {
        $response = $this->getJson('/api/health');

        $response->assertStatus(200)
            ->assertExactJson([
                'status' => 'healthy',
                'checks' => [
                    'database' => 'ok',
                    'cache' => 'ok',
                ],
            ]);
    }
}

