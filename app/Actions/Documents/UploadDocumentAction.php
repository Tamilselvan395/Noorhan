<?php

namespace App\Actions\Documents;

use App\Helpers\UploadHelper;
use App\Models\Document;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;

class UploadDocumentAction
{
    public function execute(
        Model $entity,
        UploadedFile $file,
        ?User $user = null,
        string $category = 'other',
        ?string $expiresAt = null,
    ): Document {
        $path = UploadHelper::upload($file, strtolower(class_basename($entity)).'s/'.$entity->id.'/documents');

        $document = $entity->documents()->create([
            'name' => $file->getClientOriginalName(),
            'path' => $path,
            'disk' => 'public',
            'size' => $file->getSize(),
            'mime' => $file->getMimeType(),
            'category' => $category,
            'expires_at' => $expiresAt,
            'uploaded_by' => $user?->id,
        ]);

        if (method_exists($entity, 'logActivity')) {
            $entity->logActivity("uploaded document {$document->name}");
        }

        return $document;
    }
}