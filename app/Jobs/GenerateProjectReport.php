<?php

namespace App\Jobs;

use App\Models\Project;
use App\Models\Tenant;
use App\Tenancy\TenantContext;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

class GenerateProjectReport implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public readonly int $tenantId,
        public readonly int $projectId,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(TenantContext $context): void
    {
        $tenant = Tenant::query()->findOrFail($this->tenantId);

        $context->set($tenant);

        try {
            $project = Project::query()
                ->where('tenant_id', $this->tenantId)
                ->findOrFail($this->projectId);

            Storage::disk('local')->put(
                "tenants/{$tenant->id}/reports/project-{$project->id}.txt",
                "Report for {$project->name}",
            );
        } finally {
            $context->clear();
        }
    }
}
