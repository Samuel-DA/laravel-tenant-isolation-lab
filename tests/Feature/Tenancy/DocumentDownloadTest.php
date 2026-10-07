<?php

namespace Tests\Feature\Tenancy;

use App\Models\Document;
use App\Models\Project;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentDownloadTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_download_own_tenant_document(): void
    {
        Storage::fake('local');

        $acme = Tenant::factory()->create(['slug' => 'acme']);

        $alice = User::factory()->create([
            'tenant_id' => $acme->id,
        ]);

        $project = Project::factory()->create([
            'tenant_id' => $acme->id,
        ]);

        $path = "tenants/{$acme->id}/documents/contract.pdf";

        Storage::disk('local')->put($path, 'Acme contract');

        $document = Document::withoutGlobalScopes()->create([
            'tenant_id' => $acme->id,
            'project_id' => $project->id,
            'original_name' => 'contract.pdf',
            'storage_path' => $path,
            'mime_type' => 'application/pdf',
            'size' => 13,
        ]);

        $this->actingAs($alice)
            ->get(
                "/demo/documents/{$document->id}/download",
                ['X-Tenant' => 'acme'],
            )
            ->assertOk()
            ->assertDownload('contract.pdf');
    }

    public function test_user_cannot_download_another_tenant_document(): void
    {
        Storage::fake('local');

        $acme = Tenant::factory()->create(['slug' => 'acme']);
        $globex = Tenant::factory()->create(['slug' => 'globex']);

        $alice = User::factory()->create([
            'tenant_id' => $acme->id,
        ]);

        $globexProject = Project::factory()->create([
            'tenant_id' => $globex->id,
        ]);

        $path = "tenants/{$globex->id}/documents/secret.pdf";

        Storage::disk('local')->put($path, 'Globex secret');

        $document = Document::withoutGlobalScopes()->create([
            'tenant_id' => $globex->id,
            'project_id' => $globexProject->id,
            'original_name' => 'secret.pdf',
            'storage_path' => $path,
            'mime_type' => 'application/pdf',
            'size' => 13,
        ]);

        $this->actingAs($alice)
            ->get(
                "/demo/documents/{$document->id}/download",
                ['X-Tenant' => 'acme'],
            )
            ->assertNotFound();
    }
}
