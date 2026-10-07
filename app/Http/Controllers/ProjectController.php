<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class ProjectController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Project::class);

        return response()->json(
            Project::query()
                ->orderBy('id')
                ->get(['id', 'name', 'slug', 'status'])
        );
    }

    public function show(
        Request $request,
        int $projectId,
    ): JsonResponse {
        $project = Project::query()->findOrFail($projectId);

        Gate::authorize('view', $project);

        return response()->json($project);
    }


    public function update(
        Request $request,
        int $projectId,
    ): JsonResponse {
        $project = Project::query()->findOrFail($projectId);

        Gate::authorize('update', $project);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'status' => ['sometimes', 'string', 'in:active,archived'],
        ]);

        $project->update($data);

        return response()->json($project->refresh());
    }

    public function destroy(
        Request $request,
        int $projectId,
    ): Response {
        $project = Project::query()->findOrFail($projectId);

        Gate::authorize('delete', $project);

        $project->delete();

        return response()->noContent();
    }
}
