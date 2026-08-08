<?php

namespace App\Notifications\Leads;

use App\Concerns\HasNotificationChannels;
use App\Models\Lead;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FollowUpDueNotification extends Notification
{
    use HasNotificationChannels;

    public function __construct(public Lead $lead) {}

    public function category(): string
    {
        return 'leads';
    }

    public function toArray(object $notifiable): array
    {
        // Extract the ?? expression — it cannot live inside string interpolation.
        $contact = $this->lead->company_name ?? $this->lead->phone ?? 'no contact';

        return [
            'message' => "Follow-up due: {$this->lead->name} ({$contact}).",
            'url' => route('leads.show', $this->lead),
            'category' => 'leads',
            'lead_id' => $this->lead->id, // used by the daily digest for dedupe
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Follow-up due — '.$this->lead->name)
            ->line($this->toArray($notifiable)['message'])
            ->action('View Lead', route('leads.show', $this->lead));
    }
}