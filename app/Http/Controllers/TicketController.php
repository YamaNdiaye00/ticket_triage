<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Jobs\TicketClassificationJob;

class TicketController extends Controller
{
    // GET /tickets
    public function index(Request $request): JsonResponse
    {
        $q        = $request->string('q')->toString();
        $status   = $request->string('status')->toString();
        $category = $request->string('category')->toString();
        $perPage  = (int) $request->get('per_page', 10);

        $query = Ticket::query();

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('subject', 'like', "%{$q}%")
                    ->orWhere('body', 'like', "%{$q}%");
            });
        }

        if ($status !== '') {
            $query->where('status', $status);
        }

        if ($category !== '') {
            $query->where('category', $category);
        }

        $query->orderByDesc('created_at');

        return response()->json(
            $query->paginate($perPage)->appends($request->query())
        );
    }

    // POST /tickets
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'subject'  => ['required', 'string', 'max:200'],
            'body'     => ['required', 'string'],
            'category' => ['nullable', 'string', 'max:50'],
        ]);

        $ticket = Ticket::create([
            'subject' => $data['subject'],
            'body'    => $data['body'],
            'status'  => 'new',
            'category'=> $data['category'] ?? null,
            // note/explanation/confidence null by default
        ]);

        return response()->json($ticket, 201);
    }

    // GET /tickets/{id}
    public function show(Ticket $ticket): JsonResponse
    {
        return response()->json($ticket);
    }

    // PATCH /tickets/{id}
    public function update(Request $request, Ticket $ticket): JsonResponse
    {
        $data = $request->validate([
            'status'   => ['sometimes', 'string', Rule::in(Ticket::STATUSES)],
            'category' => ['sometimes', 'nullable', 'string', 'max:50'],
            'note'     => ['sometimes', 'nullable', 'string'],
        ]);

        $ticket->fill($data)->save();

        return response()->json($ticket);
    }

    // POST /tickets/{id}/classify
    public function classify(Ticket $ticket): JsonResponse
    {
        // Queue the classification job (sync by default unless queue driver changed)
        TicketClassificationJob::dispatch($ticket);

        return response()->json([
            'queued' => true,
            'ticket' => $ticket->id,
        ], 202);
    }
}
