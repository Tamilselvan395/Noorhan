<?php

namespace App\Actions\Documents;

use App\Models\Document;
use Illuminate\Support\Facades\Storage;

class DeleteDocumentAction
{
    public function execute(Document $document): void
    {
        $owner = $document->documentable;

        if (Storage::disk($document->disk)->exists($document->path)) {
            Storage::disk($document->disk)->delete($document->path);
        }

        $name = $document->name;

        $document->delete();

        if ($owner && method_exists($owner, 'logActivity')) {
            $owner->logActivity("deleted document {$name}");
        }
    }
}