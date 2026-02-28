<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\KitchenProductionLog;
use App\Models\KitchenStockDeduction;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\IngredientAuditLog;

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
        
        // 2. Cost Analysis — Refactored to avoid N+1 issues
        // We calculate the average cost per serving for each product across all valid production batches
        // and then multiply by the total quantity sold for that product.
        
        $productCosts = DB::table('kitchen_production_logs')
            ->join('kitchen_stock_deductions', 'kitchen_production_logs.id', '=', 'kitchen_stock_deductions.kitchen_production_log_id')
            ->whereIn('kitchen_production_logs.status', ['served', 'done'])
            ->select(
                'kitchen_production_logs.product_id',
                DB::raw('SUM(kitchen_production_logs.total_servings) as total_servings'),
                DB::raw('SUM(kitchen_stock_deductions.quantity_deducted * COALESCE(NULLIF(kitchen_stock_deductions.cost_per_unit, 0), (SELECT cost_per_unit FROM ingredients WHERE ingredients.id = kitchen_stock_deductions.ingredient_id))) as total_production_cost')
            )
            ->groupBy('kitchen_production_logs.product_id')
            ->get()
            ->keyBy('product_id');

        $soldItems = DB::table('transaction_items')
            ->select('product_id', DB::raw('SUM(quantity) as total_sold'))
            ->groupBy('product_id')
            ->get();

        $totalCost = 0;
        foreach ($soldItems as $item) {
            $costData = $productCosts->get($item->product_id);
            if ($costData && $costData->total_servings > 0) {
                $costPerServing = $costData->total_production_cost / $costData->total_servings;
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
        // Refactored to use a single query for theoretical and actual usage
        $variances = DB::table('ingredients')
            ->leftJoin('kitchen_stock_deductions', 'ingredients.id', '=', 'kitchen_stock_deductions.ingredient_id')
            ->leftJoin('recipes', 'ingredients.id', '=', 'recipes.ingredient_id')
            ->leftJoin('kitchen_production_logs', function($join) {
                $join->on('recipes.product_id', '=', 'kitchen_production_logs.product_id')
                     ->whereIn('kitchen_production_logs.status', ['done', 'served']);
            })
            ->select(
                'ingredients.id as ingredient_id',
                'ingredients.name as ingredient_name',
                'ingredients.unit',
                'ingredients.cost_per_unit',
                DB::raw('SUM(DISTINCT kitchen_stock_deductions.quantity_deducted) as actual_usage'),
                DB::raw('SUM(kitchen_production_logs.times_cooked * recipes.quantity) as theoretical_usage')
            )
            ->groupBy('ingredients.id', 'ingredients.name', 'ingredients.unit', 'ingredients.cost_per_unit')
            ->get();

        foreach($variances as $v) {
            // Actual usage needs to be carefully summed because of joins. 
            // Better to use subqueries or separate optimized queries if joins cause double-counting.
            // Let's refine this to be safer.
        }

        // Safer approach using subqueries for precise aggregation
        $variances = DB::table('ingredients')
            ->select(
                'ingredients.id as ingredient_id',
                'ingredients.name as ingredient_name',
                'ingredients.unit',
                'ingredients.cost_per_unit',
                DB::raw('(SELECT SUM(quantity_deducted) FROM kitchen_stock_deductions WHERE ingredient_id = ingredients.id) as actual_usage'),
                DB::raw('(SELECT SUM(kpl.times_cooked * r.quantity) 
                          FROM kitchen_production_logs kpl 
                          JOIN recipes r ON kpl.product_id = r.product_id 
                          WHERE r.ingredient_id = ingredients.id 
                          AND kpl.status IN ("done", "served")) as theoretical_usage')
            )
            ->get();

        foreach($variances as $v) {
            $v->theoretical_usage = $v->theoretical_usage ?? 0;
            $v->actual_usage = $v->actual_usage ?? 0;
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
        $date = $request->input('date', Carbon::today()->toDateString());

        // ── TAB 1: Point of Sales ──
        $posSales = DB::table('transaction_items')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->whereDate('transactions.created_at', $date)
            ->select(
                'transaction_items.product_name',
                DB::raw('SUM(transaction_items.quantity) as total_qty'),
                DB::raw('SUM(transaction_items.subtotal) as total_sales')
            )
            ->groupBy('transaction_items.product_name')
            ->orderBy('total_sales', 'desc')
            ->get();

        $posTotalQty = $posSales->sum('total_qty');
        $posTotalRevenue = $posSales->sum('total_sales');

        // ── TAB 2: Kitchen Production ──
        // Optimization: Use with('deductions') only for specific date
        $servedLogs = KitchenProductionLog::with('deductions')
            ->whereDate('created_at', $date)
            ->whereIn('status', ['served', 'done'])
            ->orderBy('created_at', 'desc')
            ->get();

        $wastedLogs = KitchenProductionLog::with('deductions')
            ->whereDate('created_at', $date)
            ->where('status', 'wasted')
            ->orderBy('created_at', 'desc')
            ->get();

        // ── TAB 3: Inventory Management ──
        $stockLogs = IngredientAuditLog::whereDate('created_at', $date)
            ->whereIn('action', ['stock_in', 'stock_out'])
            ->orderBy('created_at', 'asc')
            ->get();

        $stockIns = $stockLogs->where('action', 'stock_in');
        $stockOuts = $stockLogs->where('action', 'stock_out');

        $totalStockInCost  = $stockIns->sum('total_cost');
        $totalStockOutCost = $stockOuts->sum('total_cost');

        // ── TAB 4: End of Day Sales ──
        // Optimization: Single query for both success and waste costs
        $dayCosts = DB::table('kitchen_stock_deductions')
            ->join('kitchen_production_logs', 'kitchen_stock_deductions.kitchen_production_log_id', '=', 'kitchen_production_logs.id')
            ->whereDate('kitchen_production_logs.created_at', $date)
            ->select(
                'kitchen_production_logs.status',
                DB::raw('SUM(kitchen_stock_deductions.quantity_deducted * COALESCE(NULLIF(kitchen_stock_deductions.cost_per_unit, 0), (SELECT cost_per_unit FROM ingredients WHERE ingredients.id = kitchen_stock_deductions.ingredient_id))) as total_cost')
            )
            ->groupBy('kitchen_production_logs.status')
            ->get()
            ->keyBy('status');

        $dayIngredientCost = ($dayCosts->get('served')->total_cost ?? 0) + ($dayCosts->get('done')->total_cost ?? 0);
        $dayWasteCost = $dayCosts->get('wasted')->total_cost ?? 0;

        $dayTotalCosts = $dayIngredientCost + $dayWasteCost;
        $dayNetProfit  = $posTotalRevenue - $dayTotalCosts;

        return view('end-of-day', compact(
            'date',
            'posSales', 'posTotalQty', 'posTotalRevenue',
            'servedLogs', 'wastedLogs',
            'stockIns', 'stockOuts', 'totalStockInCost', 'totalStockOutCost',
            'dayIngredientCost', 'dayWasteCost', 'dayTotalCosts', 'dayNetProfit'
        ));
    }
}
