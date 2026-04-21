<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\FarmConsumption;
use App\Models\FarmLivestock;
use App\Models\Invoice;
use App\Models\MilkProduction;
use App\Models\MilkSale;
use App\Models\Purchase;
use App\Models\Status;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    private function fmtDate(string $date): string
    {
        return Carbon::parse($date)->format('d M Y');
    }

    private function fmtMonth(string $yearMonth): string
    {
        return Carbon::parse($yearMonth . '-01')->format('M Y');
    }

    // ─── Dashboard ────────────────────────────────────────────────────────────

    public function summary(): JsonResponse
    {
        $today      = Carbon::today();
        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd   = Carbon::now()->endOfMonth();

        $paidId      = Status::where('code', 'PAID')->value('id');
        $cancelledId = Status::where('code', 'CANCELLED')->value('id');
        $aliveId     = Status::where('code', 'ALIVE')->value('id');
        $overdueId   = Status::where('code', 'OVERDUE')->value('id');

        $unpaidStatuses = Bill::whereNotIn('status_id', array_filter([$paidId, $cancelledId]));

        return response()->json([
            'total_livestock'             => FarmLivestock::where('status_id', $aliveId)->count(),
            'todays_milk_litres'          => (float) MilkProduction::whereDate('date', $today)->sum('quantity'),
            'pending_bills_amount'        => (float) (clone $unpaidStatuses)->sum('total'),
            'outstanding_invoice_balance' => (float) Invoice::where('balance', '>', 0)->sum('balance'),
            'overdue_invoices_count'      => Invoice::where('status_id', $overdueId)->count(),
            'unpaid_bills_count'          => (clone $unpaidStatuses)->count(),
            'monthly_revenue'             => (float) MilkSale::whereBetween('date', [$monthStart, $monthEnd])->sum('total'),
            'monthly_expenses'            => (float) (
                Purchase::whereBetween('date', [$monthStart, $monthEnd])->sum('total') +
                Bill::whereBetween('date', [$monthStart, $monthEnd])->sum('total')
            ),
        ]);
    }

    // ─── Milk Production ──────────────────────────────────────────────────────

    public function milkProductionSummary(): JsonResponse
    {
        $today      = Carbon::today();
        $monthStart = Carbon::now()->startOfMonth();
        $daysElapsed = max(1, $today->day);

        $mtd = MilkProduction::where('date', '>=', $monthStart)->sum('quantity');

        $peak = MilkProduction::select(DB::raw('DATE(date) as day'), DB::raw('SUM(quantity) as total'))
            ->where('date', '>=', $monthStart)
            ->groupBy('day')
            ->orderByDesc('total')
            ->first();

        return response()->json([
            'total_mtd'         => (float) $mtd,
            'today'             => (float) MilkProduction::whereDate('date', $today)->sum('quantity'),
            'daily_avg_mtd'     => (float) round($mtd / $daysElapsed, 2),
            'producing_animals' => MilkProduction::whereDate('date', $today)->distinct('livestock_id')->count('livestock_id'),
            'peak_day'          => $peak ? $this->fmtDate($peak->day) : null,
            'peak_quantity'     => $peak ? (float) $peak->total : 0,
        ]);
    }

    public function milkProductionTrend(): JsonResponse
    {
        $trend = MilkProduction::select(
            DB::raw('DATE(date) as date'),
            DB::raw('SUM(quantity) as quantity')
        )
            ->where('date', '>=', Carbon::today()->subDays(5))
            ->groupBy(DB::raw('DATE(date)'))
            ->orderBy('date')
            ->get()
            ->map(fn($r) => ['date' => $this->fmtDate($r->date), 'quantity' => (float) $r->quantity]);

        return response()->json(['data' => $trend]);
    }

    public function milkProductionBySession(): JsonResponse
    {
        $data = DB::table('milk_productions')
            ->join('farm_session_templates', 'milk_productions.farm_session_id', '=', 'farm_session_templates.id')
            ->where('milk_productions.date', '>=', Carbon::now()->startOfMonth())
            ->select('farm_session_templates.name as session', DB::raw('SUM(milk_productions.quantity) as total'))
            ->groupBy('farm_session_templates.name')
            ->orderByDesc('total')
            ->get()
            ->map(fn($r) => ['session' => $r->session, 'total' => (float) $r->total]);

        return response()->json(['data' => $data]);
    }

    public function milkProductionMonthlyTrend(): JsonResponse
    {
        $data = MilkProduction::select(
            DB::raw("DATE_FORMAT(date, '%Y-%m') as month"),
            DB::raw('SUM(quantity) as total')
        )
            ->where('date', '>=', Carbon::now()->subMonths(5)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(fn($r) => ['month' => $this->fmtMonth($r->month), 'total' => (float) $r->total]);

        return response()->json(['data' => $data]);
    }

    public function milkProductionTopAnimals(): JsonResponse
    {
        $data = DB::table('milk_productions')
            ->join('farm_livestocks', 'milk_productions.livestock_id', '=', 'farm_livestocks.id')
            ->where('milk_productions.date', '>=', Carbon::now()->startOfMonth())
            ->select(
                'farm_livestocks.name',
                'farm_livestocks.code',
                DB::raw('SUM(milk_productions.quantity) as total')
            )
            ->groupBy('farm_livestocks.id', 'farm_livestocks.name', 'farm_livestocks.code')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(fn($r) => ['name' => $r->name, 'code' => $r->code, 'total' => (float) $r->total]);

        return response()->json(['data' => $data]);
    }

    // ─── Milk Sales ───────────────────────────────────────────────────────────

    public function milkSalesTrend(): JsonResponse
    {
        $data = MilkSale::select(
            DB::raw("DATE_FORMAT(date, '%Y-%m') as month"),
            DB::raw('SUM(total) as total')
        )
            ->where('date', '>=', Carbon::now()->subMonths(5)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(fn($r) => ['month' => $this->fmtMonth($r->month), 'total' => (float) $r->total]);

        return response()->json(['data' => $data]);
    }

    public function recentMilkSales(): JsonResponse
    {
        $sales = MilkSale::with('customer')
            ->where('date', '>=', Carbon::today()->subDays(6))
            ->orderByDesc('date')
            ->get()
            ->map(fn($s) => [
                'date'     => $s->date->format('d M Y'),
                'customer' => $s->customer?->name,
                'quantity' => (float) $s->quantity,
                'total'    => (float) $s->total,
            ]);

        return response()->json(['data' => $sales]);
    }

    public function salesSummary(): JsonResponse
    {
        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd   = Carbon::now()->endOfMonth();

        $milkRevenue    = MilkSale::whereBetween('date', [$monthStart, $monthEnd])->sum('total');
        $invoiceRevenue = Invoice::whereBetween('date', [$monthStart, $monthEnd])->sum('amount_paid');

        return response()->json([
            'total_revenue_mtd'       => (float) ($milkRevenue + $invoiceRevenue),
            'milk_sales_revenue_mtd'  => (float) $milkRevenue,
            'invoice_revenue_mtd'     => (float) $invoiceRevenue,
            'outstanding_balance'     => (float) Invoice::where('balance', '>', 0)->sum('balance'),
            'active_customers'        => MilkSale::whereBetween('date', [$monthStart, $monthEnd])
                ->distinct('customer_id')->count('customer_id'),
        ]);
    }

    // ─── Expenses ─────────────────────────────────────────────────────────────

    public function expenseSummary(): JsonResponse
    {
        $today      = Carbon::today();
        $monthStart = Carbon::now()->startOfMonth();
        $daysElapsed = max(1, $today->day);

        $paidId      = Status::where('code', 'PAID')->value('id');
        $cancelledId = Status::where('code', 'CANCELLED')->value('id');

        $mtd = Purchase::where('date', '>=', $monthStart)->sum('total')
            + Bill::where('date', '>=', $monthStart)->sum('total');

        $todayExpense = Purchase::whereDate('date', $today)->sum('total')
            + Bill::whereDate('date', $today)->sum('total');

        $topCategory = DB::table('purchase_items')
            ->join('purchases', 'purchase_items.purchase_id', '=', 'purchases.id')
            ->join('item_masters', 'purchase_items.item_master_id', '=', 'item_masters.id')
            ->join('item_categories', 'item_masters.item_category_id', '=', 'item_categories.id')
            ->where('purchases.date', '>=', $monthStart)
            ->select('item_categories.name', DB::raw('SUM(purchase_items.total) as total'))
            ->groupBy('item_categories.name')
            ->orderByDesc('total')
            ->value('name');

        return response()->json([
            'total_mtd'           => (float) $mtd,
            'today'               => (float) $todayExpense,
            'daily_avg_mtd'       => (float) round($mtd / $daysElapsed, 2),
            'pending_bills_count' => Bill::whereNotIn('status_id', array_filter([$paidId, $cancelledId]))->count(),
            'top_category'        => $topCategory,
        ]);
    }

    public function expenseBreakdown(): JsonResponse
    {
        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd   = Carbon::now()->endOfMonth();

        $purchaseBreakdown = DB::table('purchase_items')
            ->join('purchases', 'purchase_items.purchase_id', '=', 'purchases.id')
            ->join('item_masters', 'purchase_items.item_master_id', '=', 'item_masters.id')
            ->join('item_categories', 'item_masters.item_category_id', '=', 'item_categories.id')
            ->whereBetween('purchases.date', [$monthStart, $monthEnd])
            ->select('item_categories.name as category', DB::raw('SUM(purchase_items.total) as total'))
            ->groupBy('item_categories.name')
            ->get();

        $billBreakdown = DB::table('bill_items')
            ->join('bills', 'bill_items.bill_id', '=', 'bills.id')
            ->join('services', 'bill_items.service_id', '=', 'services.id')
            ->join('service_types', 'services.service_type_id', '=', 'service_types.id')
            ->whereBetween('bills.date', [$monthStart, $monthEnd])
            ->select('service_types.name as category', DB::raw('SUM(bill_items.total) as total'))
            ->groupBy('service_types.name')
            ->get();

        $combined = collect($purchaseBreakdown)
            ->concat($billBreakdown)
            ->groupBy('category')
            ->map(fn($items, $category) => [
                'category' => $category,
                'total'    => (float) $items->sum('total'),
            ])
            ->sortByDesc('total')
            ->values();

        return response()->json(['data' => $combined]);
    }

    public function expenseDailyTrend(): JsonResponse
    {
        $purchaseTrend = DB::table('purchases')
            ->select(DB::raw('DATE(date) as date'), DB::raw('SUM(total) as total'))
            ->where('date', '>=', Carbon::today()->subDays(29))
            ->groupBy(DB::raw('DATE(date)'))
            ->pluck('total', 'date');

        $billTrend = DB::table('bills')
            ->select(DB::raw('DATE(date) as date'), DB::raw('SUM(total) as total'))
            ->where('date', '>=', Carbon::today()->subDays(29))
            ->groupBy(DB::raw('DATE(date)'))
            ->pluck('total', 'date');

        $allDates = collect($purchaseTrend->keys())->merge($billTrend->keys())->unique()->sort();

        $merged = $allDates->map(fn($date) => [
            'date'  => $this->fmtDate($date),
            'total' => (float) ($purchaseTrend[$date] ?? 0) + (float) ($billTrend[$date] ?? 0),
        ])->values();

        return response()->json(['data' => $merged]);
    }

    public function expenseMonthlyTrend(): JsonResponse
    {
        $purchaseTrend = DB::table('purchases')
            ->select(DB::raw("DATE_FORMAT(date, '%Y-%m') as month"), DB::raw('SUM(total) as total'))
            ->where('date', '>=', Carbon::now()->subMonths(5)->startOfMonth())
            ->groupBy('month')
            ->pluck('total', 'month');

        $billTrend = DB::table('bills')
            ->select(DB::raw("DATE_FORMAT(date, '%Y-%m') as month"), DB::raw('SUM(total) as total'))
            ->where('date', '>=', Carbon::now()->subMonths(5)->startOfMonth())
            ->groupBy('month')
            ->pluck('total', 'month');

        $allMonths = collect($purchaseTrend->keys())->merge($billTrend->keys())->unique()->sort();

        $merged = $allMonths->map(fn($month) => [
            'month' => $this->fmtMonth($month),
            'total' => (float) ($purchaseTrend[$month] ?? 0) + (float) ($billTrend[$month] ?? 0),
        ])->values();

        return response()->json(['data' => $merged]);
    }

    public function expenseTopCategories(): JsonResponse
    {
        $monthStart = Carbon::now()->startOfMonth();

        $data = DB::table('purchase_items')
            ->join('purchases', 'purchase_items.purchase_id', '=', 'purchases.id')
            ->join('item_masters', 'purchase_items.item_master_id', '=', 'item_masters.id')
            ->join('item_categories', 'item_masters.item_category_id', '=', 'item_categories.id')
            ->where('purchases.date', '>=', $monthStart)
            ->select('item_categories.name', DB::raw('SUM(purchase_items.total) as total'))
            ->groupBy('item_categories.name')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(fn($r) => ['name' => $r->name, 'total' => (float) $r->total]);

        return response()->json(['data' => $data]);
    }

    public function expenseRecentPurchases(): JsonResponse
    {
        return $this->recentPurchases();
    }

    public function recentPurchases(): JsonResponse
    {
        $purchases = Purchase::with('supplier')
            ->where('date', '>=', Carbon::today()->subDays(6))
            ->orderByDesc('date')
            ->get()
            ->map(fn($p) => [
                'date'     => $p->date->format('d M Y'),
                'supplier' => $p->supplier?->name,
                'total'    => (float) $p->total,
            ]);

        return response()->json(['data' => $purchases]);
    }

    // ─── Consumption ──────────────────────────────────────────────────────────

    public function consumptionSummary(): JsonResponse
    {
        $today      = Carbon::today();
        $monthStart = Carbon::now()->startOfMonth();
        $daysElapsed = max(1, $today->day);

        $mtd = FarmConsumption::where('consumption_date', '>=', $monthStart)->sum('quantity');

        $topItem = DB::table('farm_consumptions')
            ->join('farm_items', 'farm_consumptions.farm_item_id', '=', 'farm_items.id')
            ->join('item_masters', 'farm_items.item_master_id', '=', 'item_masters.id')
            ->where('farm_consumptions.consumption_date', '>=', $monthStart)
            ->select('item_masters.name', DB::raw('SUM(farm_consumptions.quantity) as total'))
            ->groupBy('item_masters.name')
            ->orderByDesc('total')
            ->value('name');

        return response()->json([
            'total_mtd'           => (float) $mtd,
            'today'               => (float) FarmConsumption::whereDate('consumption_date', $today)->sum('quantity'),
            'daily_avg_mtd'       => (float) round($mtd / $daysElapsed, 2),
            'unique_items'        => FarmConsumption::where('consumption_date', '>=', $monthStart)
                ->distinct('farm_item_id')->count('farm_item_id'),
            'most_consumed_item'  => $topItem,
        ]);
    }

    public function consumptionByAnimal(): JsonResponse
    {
        $data = DB::table('farm_consumptions')
            ->join('farm_livestocks', 'farm_consumptions.livestock_id', '=', 'farm_livestocks.id')
            ->where('farm_consumptions.consumption_date', '>=', Carbon::now()->startOfMonth())
            ->select(
                'farm_livestocks.name',
                'farm_livestocks.code',
                DB::raw('SUM(farm_consumptions.quantity) as total')
            )
            ->groupBy('farm_livestocks.id', 'farm_livestocks.name', 'farm_livestocks.code')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(fn($r) => ['name' => $r->name, 'code' => $r->code, 'total' => (float) $r->total]);

        return response()->json(['data' => $data]);
    }

    public function consumptionDailyTrend(): JsonResponse
    {
        $data = FarmConsumption::select(
            DB::raw('DATE(consumption_date) as date'),
            DB::raw('SUM(quantity) as quantity')
        )
            ->where('consumption_date', '>=', Carbon::today()->subDays(29))
            ->groupBy(DB::raw('DATE(consumption_date)'))
            ->orderBy('date')
            ->get()
            ->map(fn($r) => ['date' => $this->fmtDate($r->date), 'quantity' => (float) $r->quantity]);

        return response()->json(['data' => $data]);
    }

    public function consumptionMonthlyTrend(): JsonResponse
    {
        $data = FarmConsumption::select(
            DB::raw("DATE_FORMAT(consumption_date, '%Y-%m') as month"),
            DB::raw('SUM(quantity) as total')
        )
            ->where('consumption_date', '>=', Carbon::now()->subMonths(5)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(fn($r) => ['month' => $this->fmtMonth($r->month), 'total' => (float) $r->total]);

        return response()->json(['data' => $data]);
    }

    public function consumptionTopItems(): JsonResponse
    {
        $data = DB::table('farm_consumptions')
            ->join('farm_items', 'farm_consumptions.farm_item_id', '=', 'farm_items.id')
            ->join('item_masters', 'farm_items.item_master_id', '=', 'item_masters.id')
            ->where('farm_consumptions.consumption_date', '>=', Carbon::now()->startOfMonth())
            ->select('item_masters.name', DB::raw('SUM(farm_consumptions.quantity) as total'))
            ->groupBy('item_masters.name')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(fn($r) => ['name' => $r->name, 'total' => (float) $r->total]);

        return response()->json(['data' => $data]);
    }

    // ─── Revenue ──────────────────────────────────────────────────────────────

    public function revenueTrend(): JsonResponse
    {
        $milkTrend = DB::table('milk_sales')
            ->select(DB::raw("DATE_FORMAT(date, '%Y-%m') as month"), DB::raw('SUM(total) as total'))
            ->where('date', '>=', Carbon::now()->subMonths(5)->startOfMonth())
            ->groupBy('month')
            ->pluck('total', 'month');

        $invoiceTrend = DB::table('invoices')
            ->select(DB::raw("DATE_FORMAT(date, '%Y-%m') as month"), DB::raw('SUM(amount_paid) as total'))
            ->where('date', '>=', Carbon::now()->subMonths(5)->startOfMonth())
            ->groupBy('month')
            ->pluck('total', 'month');

        $allMonths = collect($milkTrend->keys())->merge($invoiceTrend->keys())->unique()->sort();

        $merged = $allMonths->map(fn($month) => [
            'month' => $this->fmtMonth($month),
            'total' => (float) ($milkTrend[$month] ?? 0) + (float) ($invoiceTrend[$month] ?? 0),
        ])->values();

        return response()->json(['data' => $merged]);
    }

    // ─── Invoice Aging ────────────────────────────────────────────────────────

    public function invoiceAgingSummary(): JsonResponse
    {
        $today = Carbon::today();

        $outstanding = Invoice::where('balance', '>', 0)->get();

        $buckets = ['current' => 0, 'days_31_60' => 0, 'days_61_90' => 0, 'days_90_plus' => 0];

        foreach ($outstanding as $inv) {
            $age = $inv->date->diffInDays($today);
            if ($age <= 30)       $buckets['current']     += $inv->balance;
            elseif ($age <= 60)   $buckets['days_31_60']  += $inv->balance;
            elseif ($age <= 90)   $buckets['days_61_90']  += $inv->balance;
            else                  $buckets['days_90_plus'] += $inv->balance;
        }

        $overdueId = Status::where('code', 'OVERDUE')->value('id');

        return response()->json([
            'total_outstanding' => (float) $outstanding->sum('balance'),
            'overdue_amount'    => (float) Invoice::where('status_id', $overdueId)->sum('balance'),
            'current'           => (float) $buckets['current'],
            'days_31_60'        => (float) $buckets['days_31_60'],
            'days_61_90'        => (float) $buckets['days_61_90'],
            'days_90_plus'      => (float) $buckets['days_90_plus'],
        ]);
    }

    public function invoiceAgingBuckets(): JsonResponse
    {
        $today    = Carbon::today();
        $buckets  = ['0-30' => 0, '31-60' => 0, '61-90' => 0, '90+' => 0];

        Invoice::where('balance', '>', 0)->each(function ($inv) use ($today, &$buckets) {
            $age = $inv->date->diffInDays($today);
            if ($age <= 30)     $buckets['0-30']  += $inv->balance;
            elseif ($age <= 60) $buckets['31-60'] += $inv->balance;
            elseif ($age <= 90) $buckets['61-90'] += $inv->balance;
            else                $buckets['90+']   += $inv->balance;
        });

        $data = collect($buckets)->map(fn($total, $bucket) => [
            'bucket' => $bucket,
            'total'  => (float) $total,
        ])->values();

        return response()->json(['data' => $data]);
    }

    public function invoiceAgingTopCustomers(): JsonResponse
    {
        $data = DB::table('invoices')
            ->join('customers', 'invoices.customer_id', '=', 'customers.id')
            ->where('invoices.balance', '>', 0)
            ->select('customers.name', DB::raw('SUM(invoices.balance) as total'))
            ->groupBy('customers.id', 'customers.name')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(fn($r) => ['name' => $r->name, 'total' => (float) $r->total]);

        return response()->json(['data' => $data]);
    }

    public function invoiceAgingTrend(): JsonResponse
    {
        $data = Invoice::select(
            DB::raw("DATE_FORMAT(date, '%Y-%m') as month"),
            DB::raw('SUM(balance) as total')
        )
            ->where('balance', '>', 0)
            ->where('date', '>=', Carbon::now()->subMonths(5)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(fn($r) => ['month' => $this->fmtMonth($r->month), 'total' => (float) $r->total]);

        return response()->json(['data' => $data]);
    }

    public function invoiceAgingRecentOverdue(): JsonResponse
    {
        $overdueId = Status::where('code', 'OVERDUE')->value('id');

        $data = Invoice::with('customer')
            ->where('status_id', $overdueId)
            ->where('balance', '>', 0)
            ->orderByDesc('date')
            ->limit(10)
            ->get()
            ->map(fn($inv) => [
                'code'     => $inv->code,
                'customer' => $inv->customer?->name,
                'date'     => $inv->date->format('d M Y'),
                'balance'  => (float) $inv->balance,
            ]);

        return response()->json(['data' => $data]);
    }
}
