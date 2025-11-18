<?php

namespace App\Listeners;

use App\Events\OrderStatusUpdated;
use App\Jobs\SendOrderEmailJob;

class SendOrderNotification
{
    public function handle(OrderStatusUpdated $event): void
    {
        SendOrderEmailJob::dispatch($event->order, $event->previousStatus);
    }
}