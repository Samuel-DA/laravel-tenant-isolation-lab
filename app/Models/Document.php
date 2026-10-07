<?php

namespace App\Models;

use App\Tenancy\TenantScope;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


#[Fillable(['storage_path', 'original_name', 'project_id', 'tenant_id', 'mime_type', 'size'])]

class Document extends Model
{

    /** @use HasFactory<\Database\Factories\DocumentFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::addGlobalScope(app(TenantScope::class));
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
