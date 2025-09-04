<?php

namespace App\Jobs;

use App\Models\Ticket;
use App\Services\TicketClassifier;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ClassifyTicket implements ShouldQueue
{
    use Queueable, Dispatchable, InteractsWithQueue, SerializesModels;

    public string $ticketId;

    /**
     * Create a new job instance.
     */
    public function __construct(Ticket $ticket)
    {
        $this->ticketId = $ticket->getKey();
    }

    /**
     * Execute the job.
     */
    public function handle(TicketClassifier $classifier): void
    {
        $ticket = Ticket::find($this->ticketId);
        if (! $ticket) return;

        $result = $classifier->classify($ticket);

        $update = [
            'explanation'   => $result['explanation'],
            'confidence'    => $result['confidence'],
            'classified_at' => now(),
        ];

        // Respect user override: only write category if user hasn't changed it
        if (is_null($ticket->manual_category_at)) {
            $update['category'] = $result['category'];
        }

        $ticket->update($update);
    }
}
