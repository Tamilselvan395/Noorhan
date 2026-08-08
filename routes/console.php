<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Services\System\TaskLogger;

Schedule::command('whatsapp:automations')->dailyAt('09:00');
Schedule::command('whatsapp:campaigns')->everyFiveMinutes();
Schedule::command('notifications:digest')->dailyAt('08:00');
Schedule::command('system:prune-logs')->monthlyOn(1, '02:00');

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

TaskLogger::wrap(
    Schedule::command('notifications:digest')->dailyAt('08:00')->withoutOverlapping(),
    'Notification Digest',
);

TaskLogger::wrap(
    Schedule::command('whatsapp:automations')->dailyAt('09:00')->withoutOverlapping(),
    'WhatsApp Automations',
);

TaskLogger::wrap(
    Schedule::command('whatsapp:campaigns')->everyFiveMinutes()->withoutOverlapping(),
    'Scheduled WhatsApp Campaigns',
);

TaskLogger::wrap(
    Schedule::command('quotations:expire')->dailyAt('01:00'),
    'Expire Quotations',
);

TaskLogger::wrap(
    Schedule::command('zoho:retry-failed')->everyThirtyMinutes()->withoutOverlapping(),
    'Retry Failed Zoho Syncs',
);

TaskLogger::wrap(
    Schedule::command('system:prune-logs')->monthlyOn(1, '02:00'),
    'Prune System Logs',
);
