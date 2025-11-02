<?php

namespace App\Jobs;

use App\Mail\ConsultationNotificationMail;
use App\Models\Consultation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendConsultationNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Consultation $consultation;

    /**
     * Create a new job instance.
     */
    public function __construct(Consultation $consultation)
    {
        $this->consultation = $consultation;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to($this->consultation->user->email)->send(new ConsultationNotificationMail($this->consultation));
    }
}