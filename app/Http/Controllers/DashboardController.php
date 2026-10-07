<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Tenancy\TenantCache;
use App\Tenancy\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class DashboardController extends Controller
{
    public function __invoke(
        TenantCache $cache,
        TenantContext $context,
    ): JsonResponse {
        Gate::authorize('viewAny', Project::class);

        $statistics = $cache->remember(
            'dashboard.statistics',
            600,
            fn () => [
                'tenant_id' => $context->id(),
                'project_count' => Project::query()->count(),
            ],
        );

        return response()->json($statistics);
    }
}