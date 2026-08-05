<?php

namespace App\Notifications;

use App\Models\Lead;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeadAssignedNotification extends Notification
{
    public function __construct(public Lead $lead) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'lead_id' => $this->lead->id,
            'lead_name' => $this->lead->name,
            'url' => route('leads.show', $this->lead),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New lead assigned: '.$this->lead->name)
            ->line('You have been assigned a new lead: '.$this->lead->name.($this->lead->company_name ? " ({$this->lead->company_name})" : ''))
            ->action('View Lead', route('leads.show', $this->lead));
    }
}