<?php

namespace App\Tenancy;

use Closure;
use Illuminate\Support\Facades\Cache;

final class TenantCache
{
    public function __construct(
        private readonly TenantContext $context,
    ) {}

    public function key(string $key): string
    {
        return sprintf(
            'tenant:%d:%s',
            $this->context->id(),
            $key,
        );
    }

    public function remember(
        string $key,
        int $seconds,
        Closure $callback,
    ): mixed {
        return Cache::remember(
            $this->key($key),
            $seconds,
            $callback,
        );
    }
}