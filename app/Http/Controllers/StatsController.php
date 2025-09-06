<?php


declare(strict_types=1);
namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class StatsController extends Controller
{
    // GET /stats
    public function index(): JsonResponse
    {
        $byStatus = Ticket::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        $byCategory = Ticket::select('category', DB::raw('COUNT(*) as count'))
            ->groupBy('category')
            ->pluck('count', 'category');

        return response()->json([
            'status'   => $byStatus,    // { new: 12, open: 8, ... }
            'category' => $byCategory,  // { Billing: 5, Technical: 10, ... null: 3 }
        ]);
    }
}
