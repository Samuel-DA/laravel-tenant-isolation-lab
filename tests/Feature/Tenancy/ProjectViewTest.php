<?php

namespace Tests\Feature\Tenancy;

use App\Models\Project;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_a_project_from_active_tenant(): void
    {
        $acme = Tenant::factory()->create([
            'name' => 'Acme Ltd',
            'slug' => 'acme',
        ]);

        $alice = User::factory()->create([
            'tenant_id' => $acme->id,
        ]);

        $acmeProject = Project::factory()->create([
            'tenant_id' => $acme->id,
            'name' => 'Acme Project',
            'slug' => 'acme-project',
        ]);

        $response = $this->actingAs($alice)
            ->getJson(
                "/demo/projects/{$acmeProject->id}",
                ['X-Tenant' => 'acme'],
            );

        $response
            ->assertOk()
            ->assertJson([
                'id' => $acmeProject->id,
                'tenant_id' => $acme->id,
                'name' => 'Acme Project',
                'slug' => 'acme-project',
            ]);
    }

    public function test_user_cannot_view_a_project_from_another_tenant(): void
    {
        $acme = Tenant::factory()->create([
            'name' => 'Acme Ltd',
            'slug' => 'acme',
        ]);

        $globex = Tenant::factory()->create([
            'name' => 'Globex Ltd',
            'slug' => 'globex',
        ]);

        $alice = User::factory()->create([
            'tenant_id' => $acme->id,
        ]);

        $globexProject = Project::factory()->create([
            'tenant_id' => $globex->id,
            'name' => 'Globex Project',
            'slug' => 'globex-project',
        ]);

        $this->actingAs($alice)
            ->getJson(
                "/demo/projects/{$globexProject->id}",
                ['X-Tenant' => 'acme'],
            )
            ->assertNotFound();
    }
}
