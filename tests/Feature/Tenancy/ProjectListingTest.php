<?php

namespace Tests\Feature\Tenancy;

use App\Models\Project;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectListingTest extends TestCase
{
    use RefreshDatabase;


    public function test_user_cannot_switch_to_another_tenant(): void
    {
        $acme = Tenant::factory()->create(['slug' => 'acme']);
        Tenant::factory()->create(['slug' => 'globex']);

        $alice = User::factory()->create([
            'tenant_id' => $acme->id,
        ]);

        $this->actingAs($alice)
            ->getJson(
                '/demo/projects',
                ['X-Tenant' => 'globex'],
            )
            ->assertForbidden();
    }

    public function test_user_sees_only_projects_from_active_tenant(): void
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

        Project::factory()->create([
            'tenant_id' => $acme->id,
            'name' => 'Acme Project',
            'slug' => 'acme-project',
        ]);

        Project::factory()->create([
            'tenant_id' => $globex->id,
            'name' => 'Globex Project',
            'slug' => 'globex-project',
        ]);

        $response = $this->actingAs($alice)
            ->getJson(
                '/demo/projects',
                ['X-Tenant' => 'acme'],
            );

        $response
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonFragment([
                'name' => 'Acme Project',
            ])
            ->assertJsonMissing([
                'name' => 'Globex Project',
            ]);
    }


}
