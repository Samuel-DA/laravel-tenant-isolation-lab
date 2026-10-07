<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Gate;

class DocumentController extends Controller
{
    public function download(
        Request $request,
        int $documentId,
    ): StreamedResponse {
        $document = Document::query()
            ->findOrFail($documentId);

        Gate::authorize('view', $document);

        return Storage::disk('local')->download(
            $document->storage_path,
            $document->original_name,
        );
    }
}
