<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class IdentifyTenant
{
    public function __construct(
        private readonly TenantContext $context,
    ) {}

    public function handle(
        Request $request,
        Closure $next,
    ): Response {
        $slug = $request->header('X-Tenant');

        abort_if(blank($slug), 404);

        $tenant = Tenant::query()
            ->where('slug', $slug)
            ->firstOrFail();

        $this->context->set($tenant);

        try {
            return $next($request);
        } finally {
            $this->context->clear();
        }
    }
}