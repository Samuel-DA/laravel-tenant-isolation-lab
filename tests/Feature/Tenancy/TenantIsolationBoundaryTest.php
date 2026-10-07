<?php

namespace Tests\Feature\Tenancy;

use App\Jobs\GenerateProjectReport;
use App\Models\Project;
use App\Models\Tenant;
use App\Tenancy\TenantContext;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TenantIsolationBoundaryTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_rejects_cross_tenant_document_relationship(): void
    {
        $acme = Tenant::factory()->create(['slug' => 'acme']);
        $globex = Tenant::factory()->create(['slug' => 'globex']);

        $globexProject = Project::factory()->create([
            'tenant_id' => $globex->id,
        ]);

        $this->expectException(QueryException::class);

        DB::table('documents')->insert([
            'tenant_id' => $acme->id,
            'project_id' => $globexProject->id,
            'original_name' => 'invalid.pdf',
            'storage_path' => 'tenants/invalid/document.pdf',
            'mime_type' => 'application/pdf',
            'size' => 100,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_sequential_jobs_do_not_share_tenant_context(): void
    {
        Storage::fake('local');

        $acme = Tenant::factory()->create(['slug' => 'acme']);
        $globex = Tenant::factory()->create(['slug' => 'globex']);

        $acmeProject = Project::factory()->create([
            'tenant_id' => $acme->id,
            'name' => 'Acme Project',
        ]);

        $globexProject = Project::factory()->create([
            'tenant_id' => $globex->id,
            'name' => 'Globex Project',
        ]);

        $context = app(TenantContext::class);

        (new GenerateProjectReport(
            tenantId: $acme->id,
            projectId: $acmeProject->id,
        ))->handle($context);

        $this->assertFalse($context->hasTenant());

        (new GenerateProjectReport(
            tenantId: $globex->id,
            projectId: $globexProject->id,
        ))->handle($context);

        $this->assertFalse($context->hasTenant());

        Storage::disk('local')->assertExists(
            "tenants/{$acme->id}/reports/project-{$acmeProject->id}.txt"
        );

        Storage::disk('local')->assertExists(
            "tenants/{$globex->id}/reports/project-{$globexProject->id}.txt"
        );
    }
}