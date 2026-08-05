<?php

namespace App\Actions\Customers;

use App\Helpers\UploadHelper;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Document;
use App\Models\User;
use Illuminate\Http\UploadedFile;

class UploadCustomerDocumentAction
{
    public function execute(
        Customer|Company $entity,
        UploadedFile $file,
        ?User $user = null,
    ): Document {
        $path = UploadHelper::upload(
            $file,
            strtolower(class_basename($entity)) . "s/{$entity->id}/documents"
        );

        $document = $entity->documents()->create([
            'name'        => $file->getClientOriginalName(),
            'path'        => $path,
            'disk'        => 'public',
            'size'        => $file->getSize(),
            'mime'        => $file->getMimeType(),
            'uploaded_by' => $user?->id,
        ]);

        $entity->update([
            'last_activity_at' => now(),
        ]);

        $entity->logActivity(
            "uploaded document {$document->name}"
        );

        return $document;
    }
}