<?php

namespace Tests\Feature\Tenancy;

use App\Models\Project;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectMutationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_update_own_tenant_project(): void
    {
        $acme = Tenant::factory()->create(['slug' => 'acme']);

        $alice = User::factory()->create([
            'tenant_id' => $acme->id,
        ]);

        $project = Project::factory()->create([
            'tenant_id' => $acme->id,
            'name' => 'Original Name',
        ]);

        $this->actingAs($alice)
            ->patchJson(
                "/demo/projects/{$project->id}",
                ['name' => 'Updated Name'],
                ['X-Tenant' => 'acme'],
            )
            ->assertOk()
            ->assertJsonFragment([
                'name' => 'Updated Name',
            ]);

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_user_cannot_update_another_tenant_project(): void
    {
        $acme = Tenant::factory()->create(['slug' => 'acme']);
        $globex = Tenant::factory()->create(['slug' => 'globex']);

        $alice = User::factory()->create([
            'tenant_id' => $acme->id,
        ]);

        $project = Project::factory()->create([
            'tenant_id' => $globex->id,
            'name' => 'Globex Project',
        ]);

        $this->actingAs($alice)
            ->patchJson(
                "/demo/projects/{$project->id}",
                ['name' => 'Compromised'],
                ['X-Tenant' => 'acme'],
            )
            ->assertNotFound();

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'name' => 'Globex Project',
        ]);
    }

    public function test_user_can_delete_own_tenant_project(): void
    {
        $acme = Tenant::factory()->create(['slug' => 'acme']);

        $alice = User::factory()->create([
            'tenant_id' => $acme->id,
        ]);

        $project = Project::factory()->create([
            'tenant_id' => $acme->id,
        ]);

        $this->actingAs($alice)
            ->deleteJson(
                "/demo/projects/{$project->id}",
                [],
                ['X-Tenant' => 'acme'],
            )
            ->assertNoContent();

        $this->assertDatabaseMissing('projects', [
            'id' => $project->id,
        ]);
    }

    public function test_user_cannot_delete_another_tenant_project(): void
    {
        $acme = Tenant::factory()->create(['slug' => 'acme']);
        $globex = Tenant::factory()->create(['slug' => 'globex']);

        $alice = User::factory()->create([
            'tenant_id' => $acme->id,
        ]);

        $project = Project::factory()->create([
            'tenant_id' => $globex->id,
        ]);

        $this->actingAs($alice)
            ->deleteJson(
                "/demo/projects/{$project->id}",
                [],
                ['X-Tenant' => 'acme'],
            )
            ->assertNotFound();

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
        ]);
    }
}