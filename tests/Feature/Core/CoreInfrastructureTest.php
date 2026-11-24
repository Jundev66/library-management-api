<?php

declare(strict_types=1);

namespace Tests\Feature\Core;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CoreInfrastructureTest extends TestCase
{
    use RefreshDatabase;

    public function test_categories_and_books_tables_are_seeded(): void
    {
        $this->seed();

        $this->assertDatabaseCount('categories', 5);
        $this->assertDatabaseCount('books', 7);
        $this->assertDatabaseHas('categories', ['name' => 'Ficción']);
        $this->assertDatabaseHas('books', ['title' => 'Cien años de soledad']);
    }

    public function test_non_existing_api_route_returns_json_404(): void
    {
        $response = $this->get('/api/non-existing-endpoint');

        $response->assertStatus(404)
            ->assertHeader('content-type', 'application/json')
            ->assertJson([
                'message' => 'Resource not found.',
            ]);
    }

    public function test_api_health_endpoint_returns_healthy_status(): void
    {
        $response = $this->get('/api/health');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'healthy',
            ]);
    }

    public function test_api_responses_include_security_headers(): void
    {
        $response = $this->get('/api/health');

        $response->assertStatus(200)
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('Content-Security-Policy', "default-src 'none'; frame-ancestors 'none'")
            ->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_method_not_allowed_returns_json_405(): void
    {
        $response = $this->post('/api/health');

        $response->assertStatus(405)
            ->assertHeader('content-type', 'application/json')
            ->assertJson([
                'message' => 'Method not allowed.',
            ]);
    }
}
