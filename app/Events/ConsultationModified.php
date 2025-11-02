<?php

namespace App\Events;

use App\Models\Consultation;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ConsultationModified
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Consultation $consultation;

    /**
     * Create a new event instance.
     */
    public function __construct(Consultation $consultation)
    {
        $this->consultation = $consultation;
    }
}