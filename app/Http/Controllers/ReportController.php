<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\KitchenProductionLog;
use App\Models\KitchenStockDeduction;
use App\Models\Ingredient;
use App\Models\IngredientAuditLog;
use App\Models\Recipe;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Financial Dashboard
     */
    public function dashboard()
    {
        // 1. Revenue Metrics
        $totalRevenue = Transaction::sum('total_amount');
        $todayRevenue = Transaction::whereDate('created_at', Carbon::today())->sum('total_amount');
        
        // 2. Cost Analysis — Only count costs for products actually SOLD via POS
        // Calculate average cost-per-serving for each product from production data,
        // then multiply by quantity sold via transactions.
        $soldItems = DB::table('transaction_items')
            ->select('product_id', DB::raw('SUM(quantity) as total_sold'))
            ->groupBy('product_id')
            ->get();

        $totalCost = 0;
        foreach ($soldItems as $item) {
            // Get total cost and total servings for this product from served/done batches
            $productionCost = DB::table('kitchen_stock_deductions')
                ->join('kitchen_production_logs', 'kitchen_stock_deductions.kitchen_production_log_id', '=', 'kitchen_production_logs.id')
                ->where('kitchen_production_logs.product_id', $item->product_id)
                ->whereIn('kitchen_production_logs.status', ['served', 'done'])
                ->select(DB::raw('SUM(kitchen_stock_deductions.quantity_deducted * COALESCE(kitchen_stock_deductions.cost_per_unit, 0)) as cost'))
                ->value('cost') ?? 0;

            // If snapshot costs are zero (legacy data), fall back to current ingredient prices
            if ($productionCost == 0) {
                $productionCost = DB::table('kitchen_stock_deductions')
                    ->join('kitchen_production_logs', 'kitchen_stock_deductions.kitchen_production_log_id', '=', 'kitchen_production_logs.id')
                    ->join('ingredients', 'kitchen_stock_deductions.ingredient_id', '=', 'ingredients.id')
                    ->where('kitchen_production_logs.product_id', $item->product_id)
                    ->whereIn('kitchen_production_logs.status', ['served', 'done'])
                    ->select(DB::raw('SUM(kitchen_stock_deductions.quantity_deducted * ingredients.cost_per_unit) as cost'))
                    ->value('cost') ?? 0;
            }

            $totalServings = DB::table('kitchen_production_logs')
                ->where('product_id', $item->product_id)
                ->whereIn('status', ['served', 'done'])
                ->sum('total_servings');

            // Average cost per serving × quantity sold
            if ($totalServings > 0) {
                $costPerServing = $productionCost / $totalServings;
                $totalCost += $costPerServing * $item->total_sold;
            }
        }

        // Wasted cost (for transparency — not counted in profit margin)
        $wasteCost = DB::table('kitchen_stock_deductions')
            ->join('kitchen_production_logs', 'kitchen_stock_deductions.kitchen_production_log_id', '=', 'kitchen_production_logs.id')
            ->where('kitchen_production_logs.status', 'wasted')
            ->select(DB::raw('SUM(kitchen_stock_deductions.quantity_deducted * COALESCE(NULLIF(kitchen_stock_deductions.cost_per_unit, 0), (SELECT cost_per_unit FROM ingredients WHERE ingredients.id = kitchen_stock_deductions.ingredient_id))) as total_cost'))
            ->value('total_cost') ?? 0;

        $grossProfit = $totalRevenue - $totalCost;
        $profitMargin = $totalRevenue > 0 ? ($grossProfit / $totalRevenue) * 100 : 0;

        // 3. Top Selling Products
        $topProducts = DB::table('transaction_items')
            ->select('product_name', DB::raw('SUM(quantity) as total_sold'), DB::raw('SUM(subtotal) as revenue'))
            ->groupBy('product_name')
            ->orderBy('total_sold', 'desc')
            ->limit(5)
            ->get();

        // 4. Sales Trend (Past 7 days)
        $salesTrend = Transaction::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_amount) as total')
            )
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get();

        return view('dashboard', compact(
            'totalRevenue', 'todayRevenue', 'totalCost', 'wasteCost',
            'grossProfit', 'profitMargin', 'topProducts', 'salesTrend'
        ));
    }

    /**
     * Cost & Variance Report
     * Compares theoretical usage (from recipes × times_cooked) vs actual usage (deductions)
     */
    public function costVariance()
    {
        // Actual usage: total deducted per ingredient
        $variances = DB::table('kitchen_stock_deductions')
            ->join('ingredients', 'kitchen_stock_deductions.ingredient_id', '=', 'ingredients.id')
            ->select(
                'kitchen_stock_deductions.ingredient_id',
                'kitchen_stock_deductions.ingredient_name',
                'ingredients.unit',
                'ingredients.cost_per_unit',
                DB::raw('SUM(kitchen_stock_deductions.quantity_deducted) as actual_usage')
            )
            ->groupBy('kitchen_stock_deductions.ingredient_id', 'kitchen_stock_deductions.ingredient_name', 'ingredients.unit', 'ingredients.cost_per_unit')
            ->get();

        // Theoretical usage: SUM(times_cooked * recipe.quantity) for completed batches
        foreach($variances as $v) {
            $theoretical = DB::table('kitchen_production_logs')
                ->join('recipes', 'kitchen_production_logs.product_id', '=', 'recipes.product_id')
                ->where('recipes.ingredient_id', $v->ingredient_id)
                ->whereIn('kitchen_production_logs.status', ['done', 'served'])
                ->select(DB::raw('SUM(kitchen_production_logs.times_cooked * recipes.quantity) as theoretical_usage'))
                ->value('theoretical_usage') ?? 0;
            
            $v->theoretical_usage = $theoretical;
            // Positive variance = used less than expected (good), Negative = used more (bad)
            $v->variance = $v->theoretical_usage - $v->actual_usage;
            $v->variance_percent = $v->theoretical_usage > 0 ? ($v->variance / $v->theoretical_usage) * 100 : 0;
            $v->variance_cost = $v->variance * $v->cost_per_unit;
        }

        return view('cost-variance', compact('variances'));
    }

    /**
     * Yield & Forecasting Report
     */
    public function yieldForecasting()
    {
        // 1. Production Yield (batch outcome breakdown)
        $productionStats = DB::table('kitchen_production_logs')
            ->select(
                'status',
                DB::raw('count(*) as count'),
                DB::raw('SUM(total_servings) as total_servings')
            )
            ->groupBy('status')
            ->get();

        // 2. Success Rate (done + served vs total)
        $doneCount = $productionStats->whereIn('status', ['done', 'served'])->sum('count');
        $wastedCount = $productionStats->where('status', 'wasted')->sum('count');
        $totalCount = $productionStats->sum('count');
        $yieldRate = $totalCount > 0 ? ($doneCount / $totalCount) * 100 : 0;
        $wasteRate = $totalCount > 0 ? ($wastedCount / $totalCount) * 100 : 0;

        // 3. Forecasting (Simple 7-day projection with zero-division protection)
        $last7DaysRevenue = Transaction::where('created_at', '>=', Carbon::now()->subDays(7))
            ->sum('total_amount');
        $avgDailySales = $last7DaysRevenue > 0 ? $last7DaysRevenue / 7 : 0;
        $projectedWeeklyRevenue = $avgDailySales * 7;

        // 4. Top produced products
        $topProduced = DB::table('kitchen_production_logs')
            ->select(
                'product_name',
                DB::raw('count(*) as batch_count'),
                DB::raw('SUM(total_servings) as total_servings')
            )
            ->whereIn('status', ['done', 'served'])
            ->groupBy('product_name')
            ->orderBy('batch_count', 'desc')
            ->limit(5)
            ->get();

        // 5. Waste Reason Breakdown
        $wasteReasons = DB::table('kitchen_production_logs')
            ->select(
                DB::raw("COALESCE(waste_reason, 'Unspecified') as reason"),
                DB::raw('count(*) as count')
            )
            ->where('status', 'wasted')
            ->groupBy('reason')
            ->orderBy('count', 'desc')
            ->get();

        // 6. Most Wasted Products
        $mostWasted = DB::table('kitchen_production_logs')
            ->select(
                'product_name',
                DB::raw('count(*) as waste_count'),
                DB::raw('SUM(total_servings) as wasted_servings')
            )
            ->where('status', 'wasted')
            ->groupBy('product_name')
            ->orderBy('waste_count', 'desc')
            ->limit(5)
            ->get();

        return view('yield-forecasting', compact(
            'productionStats', 'yieldRate', 'wasteRate',
            'avgDailySales', 'projectedWeeklyRevenue', 'topProduced',
            'wasteReasons', 'mostWasted'
        ));
    }

    /**
     * End of Day Report
     */
    public function endOfDay(Request $request)
    {
        $date = $request->query('date', Carbon::today()->toDateString());
        $carbonDate = Carbon::parse($date);

        // ==========================================
        // TAB 1: POINT OF SALES
        // ==========================================
        $posSales = DB::table('transaction_items')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->whereDate('transactions.created_at', $carbonDate)
            ->select(
                'transaction_items.product_name',
                DB::raw('SUM(transaction_items.quantity) as total_qty'),
                DB::raw('SUM(transaction_items.subtotal) as total_sales')
            )
            ->groupBy('transaction_items.product_name')
            ->orderBy('total_qty', 'desc')
            ->get();

        $posTotalRevenue = $posSales->sum('total_sales');
        $posTotalQty = $posSales->sum('total_qty');

        // ==========================================
        // TAB 2: KITCHEN PRODUCTION
        // ==========================================
        $servedLogs = KitchenProductionLog::with('deductions')
            ->whereDate('created_at', $carbonDate)
            ->whereIn('status', ['served', 'done'])
            ->orderBy('created_at', 'desc')
            ->get();

        $wastedLogs = KitchenProductionLog::with('deductions')
            ->whereDate('created_at', $carbonDate)
            ->where('status', 'wasted')
            ->orderBy('created_at', 'desc')
            ->get();

        // ==========================================
        // TAB 3: INVENTORY MANAGEMENT
        // ==========================================
        $stockIns = IngredientAuditLog::with('ingredient')
            ->whereDate('created_at', $carbonDate)
            ->where('action', 'stock_in')
            ->orderBy('created_at', 'desc')
            ->get();

        $stockOuts = IngredientAuditLog::with('ingredient')
            ->whereDate('created_at', $carbonDate)
            ->where('action', 'stock_out')
            ->orderBy('created_at', 'desc')
            ->get();

        $totalStockInCost = $stockIns->sum('total_cost');
        $totalStockOutCost = $stockOuts->sum('total_cost');

        // ==========================================
        // TAB 4: END OF DAY SALES SUMMARY
        // ==========================================
        // Total ingredient costs from kitchen deductions for the day (served/done batches)
        $dayIngredientCost = DB::table('kitchen_stock_deductions')
            ->join('kitchen_production_logs', 'kitchen_stock_deductions.kitchen_production_log_id', '=', 'kitchen_production_logs.id')
            ->whereDate('kitchen_production_logs.created_at', $carbonDate)
            ->whereIn('kitchen_production_logs.status', ['served', 'done'])
            ->select(DB::raw('SUM(kitchen_stock_deductions.quantity_deducted * COALESCE(NULLIF(kitchen_stock_deductions.cost_per_unit, 0), (SELECT cost_per_unit FROM ingredients WHERE ingredients.id = kitchen_stock_deductions.ingredient_id))) as total_cost'))
            ->value('total_cost') ?? 0;

        // Waste cost for the day
        $dayWasteCost = DB::table('kitchen_stock_deductions')
            ->join('kitchen_production_logs', 'kitchen_stock_deductions.kitchen_production_log_id', '=', 'kitchen_production_logs.id')
            ->whereDate('kitchen_production_logs.created_at', $carbonDate)
            ->where('kitchen_production_logs.status', 'wasted')
            ->select(DB::raw('SUM(kitchen_stock_deductions.quantity_deducted * COALESCE(NULLIF(kitchen_stock_deductions.cost_per_unit, 0), (SELECT cost_per_unit FROM ingredients WHERE ingredients.id = kitchen_stock_deductions.ingredient_id))) as total_cost'))
            ->value('total_cost') ?? 0;

        $dayTotalCosts = $dayIngredientCost + $dayWasteCost;
        $dayNetProfit = $posTotalRevenue - $dayTotalCosts;

        return view('end-of-day', compact(
            'date',
            'posSales', 'posTotalRevenue', 'posTotalQty',
            'servedLogs', 'wastedLogs',
            'stockIns', 'stockOuts', 'totalStockInCost', 'totalStockOutCost',
            'dayIngredientCost', 'dayWasteCost', 'dayTotalCosts', 'posTotalRevenue', 'dayNetProfit'
        ));
    }
}
