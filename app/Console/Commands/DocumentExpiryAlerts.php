<?php

namespace App\Console\Commands;

use App\Models\Document;
use App\Notifications\System\DocumentExpiringNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DocumentExpiryAlerts extends Command
{
    protected $signature = 'documents:expiry-alerts';
    protected $description = 'Notify uploaders about documents expiring within 14 days.';

    public function handle(): int
    {
        Document::query()->expiringSoon(14)->with('uploader')->get()
            ->filter(fn (Document $doc) => $doc->uploader)
            ->filter(fn (Document $doc) => ! $this->alreadyNotified($doc))
            ->each(fn (Document $doc) => $doc->uploader->notify(new DocumentExpiringNotification($doc)));

        $this->info('Expiry alerts processed.');

        return self::SUCCESS;
    }

    private function alreadyNotified(Document $doc): bool
    {
        return DB::table('notifications')
            ->where('notifiable_type', $doc->uploader->getMorphClass())
            ->where('notifiable_id', $doc->uploader->id)
            ->where('type', DocumentExpiringNotification::class)
            ->where('data->document_id', $doc->id)
            ->whereDate('created_at', today())
            ->exists();
    }
}