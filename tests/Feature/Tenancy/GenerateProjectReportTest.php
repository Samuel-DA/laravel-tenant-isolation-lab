<?php

namespace Tests\Feature\Tenancy;

use App\Jobs\GenerateProjectReport;
use App\Models\Project;
use App\Models\Tenant;
use App\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GenerateProjectReportTest extends TestCase
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

    public function test_job_generates_report_inside_correct_tenant(): void
    {
        Storage::fake('local');

        $acme = Tenant::factory()->create(['slug' => 'acme']);

        $project = Project::factory()->create([
            'tenant_id' => $acme->id,
            'name' => 'Billing Platform',
        ]);

        $context = app(TenantContext::class);

        (new GenerateProjectReport(
            tenantId: $acme->id,
            projectId: $project->id,
        ))->handle($context);

        Storage::disk('local')->assertExists(
            "tenants/{$acme->id}/reports/project-{$project->id}.txt"
        );

        $this->assertFalse($context->hasTenant());
    }

    public function test_job_rejects_project_from_another_tenant(): void
{
    Storage::fake('local');

    $acme = Tenant::factory()->create(['slug' => 'acme']);
    $globex = Tenant::factory()->create(['slug' => 'globex']);

    $globexProject = Project::factory()->create([
        'tenant_id' => $globex->id,
    ]);

    $context = app(TenantContext::class);

    try {
        (new GenerateProjectReport(
            tenantId: $acme->id,
            projectId: $globexProject->id,
        ))->handle($context);

        $this->fail('The mismatched job should have failed.');
    } catch (ModelNotFoundException) {
        $this->assertFalse($context->hasTenant());

        Storage::disk('local')->assertMissing(
            "tenants/{$acme->id}/reports/project-{$globexProject->id}.txt"
        );
    }
}
}
