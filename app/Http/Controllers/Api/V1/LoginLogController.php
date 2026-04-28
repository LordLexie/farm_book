<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\LoginLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoginLogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 25);
        $from    = $request->query('from');
        $to      = $request->query('to');
        $status  = $request->query('status');

        $query = LoginLog::with('user')->orderBy('created_at', 'desc');

        if ($from) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to) {
            $query->whereDate('created_at', '<=', $to);
        }
        if ($status && in_array($status, ['success', 'failed'])) {
            $query->where('status', $status);
        }

        $paginated = $query->paginate($perPage);

        $today = now()->toDateString();
        $monthStart = now()->startOfMonth()->toDateString();

        $summary = DB::table('login_logs')->selectRaw("
            SUM(CASE WHEN DATE(created_at) = ? THEN 1 ELSE 0 END) AS today_total,
            SUM(CASE WHEN DATE(created_at) = ? AND status = 'failed' THEN 1 ELSE 0 END) AS today_failed,
            SUM(CASE WHEN DATE(created_at) >= ? THEN 1 ELSE 0 END) AS month_total,
            COUNT(DISTINCT CASE WHEN DATE(created_at) = ? AND status = 'success' THEN user_id END) AS unique_users_today
        ", [$today, $today, $monthStart, $today])->first();

        return response()->json([
            'login_logs' => $paginated->items(),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'per_page'     => $paginated->perPage(),
                'total'        => $paginated->total(),
            ],
            'summary' => [
                'today_total'        => (int) ($summary->today_total ?? 0),
                'today_failed'       => (int) ($summary->today_failed ?? 0),
                'month_total'        => (int) ($summary->month_total ?? 0),
                'unique_users_today' => (int) ($summary->unique_users_today ?? 0),
            ],
        ]);
    }
}
