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
        
        // 2. Cost Analysis — Only count ingredient costs from SERVED batches (realized cost)
        $totalCost = KitchenStockDeduction::join('ingredients', 'kitchen_stock_deductions.ingredient_id', '=', 'ingredients.id')
            ->join('kitchen_production_logs', 'kitchen_stock_deductions.kitchen_production_log_id', '=', 'kitchen_production_logs.id')
            ->whereIn('kitchen_production_logs.status', ['served', 'done'])
            ->select(DB::raw('SUM(kitchen_stock_deductions.quantity_deducted * ingredients.cost_per_unit) as total_cost'))
            ->value('total_cost') ?? 0;

        // Wasted cost (for transparency)
        $wasteCost = KitchenStockDeduction::join('ingredients', 'kitchen_stock_deductions.ingredient_id', '=', 'ingredients.id')
            ->join('kitchen_production_logs', 'kitchen_stock_deductions.kitchen_production_log_id', '=', 'kitchen_production_logs.id')
            ->where('kitchen_production_logs.status', 'wasted')
            ->select(DB::raw('SUM(kitchen_stock_deductions.quantity_deducted * ingredients.cost_per_unit) as total_cost'))
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

        return view('yield-forecasting', compact(
            'productionStats', 'yieldRate', 'wasteRate',
            'avgDailySales', 'projectedWeeklyRevenue', 'topProduced'
        ));
    }
}
