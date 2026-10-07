<?php

namespace Tests\Feature\Tenancy;

use App\Models\Project;
use App\Models\Tenant;
use App\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProjectTenantScopeTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_projects_are_limited_to_the_active_tenant(): void
    {
        $acme = Tenant::factory()->create();
        $globex = Tenant::factory()->create();

        $acmeProject = Project::factory()->create([
            'tenant_id' => $acme->id,
        ]);

        Project::factory()->create([
            'tenant_id' => $globex->id,
        ]);

        app(TenantContext::class)->set($acme);

        $projects = Project::query()->get();

        $this->assertCount(1, $projects);
        $this->assertTrue($acmeProject->is($projects->first()));
    }

    public function test_project_queries_fail_without_tenant_context(): void
    {
        $this->expectException(\LogicException::class);

        Project::query()->get();
    }
}
