<?php

namespace Tests\Feature\Tenancy;

use App\Models\Tenant;
use App\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IdentifyTenantMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_missing_tenant_header_returns_404(): void
    {
        $this->getJson('/demo/current-tenant')
            ->assertNotFound();
    }

    public function test_unknown_tenant_slug_returns_404(): void
    {
        $this->getJson(
            '/demo/current-tenant',
            ['X-Tenant' => 'unknown-company'],
        )->assertNotFound();
    }

    public function test_valid_tenant_header_resolves_the_tenant(): void
    {
        $acme = Tenant::factory()->create([
            'name' => 'Acme Ltd',
            'slug' => 'acme',
        ]);

        $response = $this->getJson(
            '/demo/current-tenant',
            ['X-Tenant' => 'acme'],
        );

        $response
            ->assertOk()
            ->assertJson([
                'tenant_id' => $acme->id,
                'tenant' => 'Acme Ltd',
            ]);
    }

    public function test_tenant_context_is_cleared_after_response(): void
    {
        Tenant::factory()->create([
            'name' => 'Acme Ltd',
            'slug' => 'acme',
        ]);

        $this->getJson(
            '/demo/current-tenant',
            ['X-Tenant' => 'acme'],
        )->assertOk();

        $this->assertFalse(
            app(TenantContext::class)->hasTenant()
        );
    }
}