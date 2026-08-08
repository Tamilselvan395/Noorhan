<?php

namespace App\Listeners\Notifications;

use App\Events\Quotations\QuotationAccepted;
use App\Events\Quotations\QuotationApproved;
use App\Events\Quotations\QuotationRejected;
use App\Events\Quotations\QuotationSubmitted;
use App\Models\User;
use App\Notifications\Sales\ApprovalRequiredNotification;
use App\Notifications\Sales\QuotationAcceptedNotification;
use App\Notifications\Sales\QuotationApprovedNotification;
use App\Notifications\Sales\QuotationRejectedNotification;

class NotifyQuotationLifecycle
{
    public function handleApproved(QuotationApproved $event): void
    {
        $event->quotation->creator?->notify(new QuotationApprovedNotification($event->quotation));
    }

    public function handleRejected(QuotationRejected $event): void
    {
        $event->quotation->creator?->notify(new QuotationRejectedNotification($event->quotation));
    }

    public function handleAccepted(QuotationAccepted $event): void
    {
        $event->quotation->creator?->notify(new QuotationAcceptedNotification($event->quotation));
    }

    /** Separation of duties: everyone except the author is a potential approver. */
    public function handleSubmitted(QuotationSubmitted $event): void
    {
        User::query()
            ->where('id', '!=', $event->quotation->created_by)
            ->get()
            ->each(fn (User $user) => $user->notify(new ApprovalRequiredNotification($event->quotation)));
    }
}