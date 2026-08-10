<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', Document::class);

        return view('documents.index');
    }

    public function download(Document $document): StreamedResponse
    {
        Gate::authorize('download', $document);

        abort_unless(Storage::disk($document->disk)->exists($document->path), 404);

        return Storage::disk($document->disk)->download($document->path, $document->name);
    }
    
    public function destroy(Document $document, \App\Actions\Documents\DeleteDocumentAction $action)
    {
        Gate::authorize('delete', $document);

        $action->execute($document);

        return back()->with('status', 'Document deleted.');
    }
}