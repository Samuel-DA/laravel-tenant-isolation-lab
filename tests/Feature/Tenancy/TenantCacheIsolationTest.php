<?php

namespace Tests\Feature\Tenancy;

use App\Models\Project;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class TenantCacheIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_each_tenant_receives_its_own_cached_statistics(): void
    {
        config(['cache.default' => 'array']);
        Cache::flush();

        $acme = Tenant::factory()->create(['slug' => 'acme']);
        $globex = Tenant::factory()->create(['slug' => 'globex']);

        $alice = User::factory()->create([
            'tenant_id' => $acme->id,
        ]);

        $bob = User::factory()->create([
            'tenant_id' => $globex->id,
        ]);

        Project::factory()->create([
            'tenant_id' => $acme->id,
        ]);

        Project::factory()->count(2)->create([
            'tenant_id' => $globex->id,
        ]);

        $this->actingAs($alice)
            ->getJson(
                '/demo/dashboard',
                ['X-Tenant' => 'acme'],
            )
            ->assertOk()
            ->assertJson([
                'tenant_id' => $acme->id,
                'project_count' => 1,
            ]);

        $this->actingAs($bob)
            ->getJson(
                '/demo/dashboard',
                ['X-Tenant' => 'globex'],
            )
            ->assertOk()
            ->assertJson([
                'tenant_id' => $globex->id,
                'project_count' => 2,
            ]);
    }
}