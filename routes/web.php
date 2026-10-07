<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ProjectController;
use App\Tenancy\TenantContext;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('tenant')->get(
    '/demo/current-tenant',
    fn(TenantContext $context) => [
        'tenant_id' => $context->id(),
        'tenant' => $context->tenant()->name,
    ],
);

Route::middleware(['auth', 'tenant'])->get(
    '/demo/projects',
    [ProjectController::class, 'index'],
);

Route::middleware(['auth', 'tenant'])->get(
    '/demo/projects/{projectId}',
    [ProjectController::class, 'show'],
);

Route::middleware(['auth', 'tenant'])->patch(
    '/demo/projects/{projectId}',
    [ProjectController::class, 'update'],
);

Route::middleware(['auth', 'tenant'])->delete(
    '/demo/projects/{projectId}',
    [ProjectController::class, 'destroy'],
);

Route::middleware(['auth', 'tenant'])->get(
    '/demo/documents/{documentId}/download',
    [DocumentController::class, 'download'],
);


Route::middleware(['auth', 'tenant'])->get(
    '/demo/dashboard',
    DashboardController::class,
);