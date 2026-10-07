<?php

namespace Tests\Feature\Tenancy;

use App\Models\Tenant;
use App\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantContextTest extends TestCase
{
    use RefreshDatabase;


    public function test_it_fails_when_tenant_context_is_missing(): void
    {
        $this->expectException(\LogicException::class);

        app(TenantContext::class)->tenant();
    }

    public function test_it_returns_the_active_tenant(): void
    {
        $tenant = Tenant::factory()->create([
            'name' => 'Acme Ltd',
            'slug' => 'acme',
        ]);

        $context = app(TenantContext::class);
        $context->set($tenant);

        $this->assertTrue($context->hasTenant());
        $this->assertTrue($tenant->is($context->tenant()));
    }

    public function test_it_clears_the_active_tenant(): void
    {
        $tenant = Tenant::factory()->create();

        $context = app(TenantContext::class);
        $context->set($tenant);
        $context->clear();

        $this->assertFalse($context->hasTenant());
    }
}
