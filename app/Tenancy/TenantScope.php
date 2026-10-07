<?php

namespace App\Tenancy;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

final class TenantScope implements Scope
{
    public function __construct(
        private readonly TenantContext $context,
    ) {}

    public function apply(Builder $builder, Model $model): void
    {
        $builder->where(
            $model->qualifyColumn('tenant_id'),
            $this->context->id(),
        );
    }
}