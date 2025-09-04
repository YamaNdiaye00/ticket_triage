<?php

namespace App\Jobs;

use App\Models\Ticket;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class TicketClassificationJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(Ticket $ticket)
    {
        $this->ticket = $ticket;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // TODO: integrate App\Services\TicketClassifier and update fields.
        // For checkpoint, we can simulate a result so UI sees something:

        // $result = app(\App\Services\TicketClassifier::class)->classify($this->ticket);
        // $this->ticket->update([
        //     'category'    => $result['category'] ?? $this->ticket->category,
        //     'explanation' => $result['explanation'] ?? 'N/A',
        //     'confidence'  => $result['confidence'] ?? null,
        // ]);

        // Temporary no-op for checkpoint.
    }
}
