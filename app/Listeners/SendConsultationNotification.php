<?php

namespace App\Listeners;

use App\Events\ConsultationModified;
use App\Jobs\SendConsultationNotificationJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendConsultationNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ConsultationModified $event): void
    {
        SendConsultationNotificationJob::dispatch($event->consultation);
    }
}