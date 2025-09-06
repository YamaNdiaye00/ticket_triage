<?php

namespace App\Http\Controllers;

use App\Jobs\ClassifyTicket;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TicketController extends Controller
{
    // GET /tickets
    public function index(Request $request): JsonResponse
    {
        $q = $request->string('q')->toString();
        $status = $request->string('status')->toString();
        $category = $request->string('category')->toString();
        $perPage = (int)$request->get('per_page', 10);

        $query = Ticket::query()
            // 1) search on subject/body
            ->when($q !== '', function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('subject', 'like', "%{$q}%")
                        ->orWhere('body', 'like', "%{$q}%");
                });
            })

            // 2) filters
            ->when($status, fn($qq) => $qq->where('status', $status))
            ->when($category, fn($qq) => $qq->where('category', $category))

            // 3) sort by most recent
            ->orderByDesc('created_at');

        if ($status !== '') {
            $query->where('status', $status);
        }

        if ($category !== '') {
            $query->where('category', $category);
        }

        // From most recently updated to last.
        // Change column as the user wishes
        $query->orderByDesc('updated_at');

        $p = $query->paginate($perPage); // uses ?page= automatically

        // Normalize the paginator to { data, meta } so the FE is simple
        return response()->json([
            'data' => $p->items(),
            'meta' => [
                'current_page' => $p->currentPage(),
                'last_page' => $p->lastPage(),
                'per_page' => $p->perPage(),
                'total' => $p->total(),
                'from' => $p->firstItem(),
                'to' => $p->lastItem(),
            ],
        ]);
    }

    // POST /tickets
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'subject' => ['required', 'string', 'max:200'],
            'body' => ['required', 'string'],
            'category' => ['nullable', 'string', 'max:50'],
        ]);

        $ticket = Ticket::create([
            'subject' => $data['subject'],
            'body' => $data['body'],
            'status' => 'new',
            'category' => $data['category'] ?? null,
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
            'status' => ['sometimes', 'string', Rule::in(Ticket::STATUSES)],
            'category' => ['sometimes', 'nullable', 'string', 'max:50'],
            'note' => ['sometimes', 'nullable', 'string'],
        ]);

        $categoryProvided = array_key_exists('category', $data);

        $ticket->fill($data);

        // If category was provided and will change, mark manual override
        if ($categoryProvided && $ticket->isDirty('category')) {
            $ticket->manual_category_at = now();
        }

        $ticket->save();

        return response()->json($ticket);
    }

    // POST /tickets/{id}/classify
    public function classify(Ticket $ticket): JsonResponse
    {
        // Queue the classification job (sync by default unless queue driver changed)
        ClassifyTicket::dispatch($ticket);

        return response()->json([
            'queued' => true,
            'ticket' => $ticket->id,
        ], 202);
    }

    // GET /tickets/export
    public function export(Request $request): StreamedResponse
    {
        $q        = trim((string) $request->query('q', ''));
        $status   = $request->query('status');
        $category = $request->query('category');

        $query = Ticket::query()
            ->when($q !== '', function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('subject', 'like', "%{$q}%")
                        ->orWhere('body', 'like', "%{$q}%");
                });
            })
            ->when($status,   fn($qq) => $qq->where('status',   $status))
            ->when($category, fn($qq) => $qq->where('category', $category))
            ->orderByDesc('created_at');

        $filename = 'tickets_export_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control'       => 'no-store, no-cache, must-revalidate',
        ];

        $columns = ['id','created_at','subject','status','category','confidence','note','explanation'];

        return response()->streamDownload(function () use ($query, $columns) {
            $out = fopen('php://output', 'w');
            // UTF-8 BOM for Excel compatibility (optional)
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($out, $columns);

            $query->chunk(100, function ($rows) use ($out, $columns) {
                foreach ($rows as $t) {
                    fputcsv($out, [
                        $t->id,
                        optional($t->created_at)->toDateTimeString(),
                        $t->subject,
                        $t->status,
                        $t->category,
                        $t->confidence,
                        $t->note,
                        $t->explanation,
                    ]);
                }
            });

            fclose($out);
        }, $filename, $headers);
    }

}
