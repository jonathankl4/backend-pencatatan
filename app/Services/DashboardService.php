<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Sale;
use Carbon\Carbon;

class DashboardService
{
    /**
     * Get aggregated dashboard data.
     *
     * @param int $userId
     * @param string|null $startDate
     * @param string|null $endDate
     * @return array
     */
    public function getDashboardData(int $userId, ?string $startDate = null, ?string $endDate = null): array
    {
        $start = $startDate ? Carbon::parse($startDate)->startOfDay() : Carbon::now()->startOfMonth();
        $end = $endDate ? Carbon::parse($endDate)->endOfDay() : Carbon::now()->endOfMonth();

        // 1. Total Revenue
        $totalRevenue = Sale::where('user_id', $userId)
            ->whereBetween('sale_date', [$start, $end])
            ->sum('total_revenue');

        // 2. Total Gross Profit
        $totalGrossProfit = Sale::where('user_id', $userId)
            ->whereBetween('sale_date', [$start, $end])
            ->sum('gross_profit');

        // 3. Total Expenses
        $totalExpenses = Expense::where('user_id', $userId)
            ->whereBetween('expense_date', [$start, $end])
            ->sum('amount');

        // 4. Net Profit (Laba Bersih = Laba Kotor - Pengeluaran)
        $netProfit = $totalGrossProfit - $totalExpenses;

        // 5. Recent Transactions (Sales & Expenses limit 5)
        $recentSales = Sale::where('user_id', $userId)
            ->whereBetween('sale_date', [$start, $end])
            ->orderBy('sale_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $recentExpenses = Expense::where('user_id', $userId)
            ->whereBetween('expense_date', [$start, $end])
            ->orderBy('expense_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $itemRecap = \DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where('sales.user_id', $userId)
            ->whereBetween('sales.sale_date', [$start, $end])
            ->select(
                'sale_items.product_name',
                \DB::raw('SUM(sale_items.quantity) as total_quantity'),
                \DB::raw('SUM(sale_items.subtotal_revenue) as total_revenue')
            )
            ->groupBy('sale_items.product_name')
            ->orderBy('total_quantity', 'desc')
            ->get();

        return [
            'period' => [
                'start' => $start->toDateString(),
                'end' => $end->toDateString(),
            ],
            'summary' => [
                'total_revenue' => (float) $totalRevenue,
                'total_gross_profit' => (float) $totalGrossProfit,
                'total_expenses' => (float) $totalExpenses,
                'net_profit' => (float) $netProfit,
            ],
            'recent_sales' => $recentSales,
            'recent_expenses' => $recentExpenses,
            'item_recap' => $itemRecap,
        ];
    }
}
