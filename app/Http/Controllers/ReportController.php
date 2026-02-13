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
        
        // 2. Cost Analysis (Simplified estimation)
        // Calculating total cost based on all deductions made so far
        $totalCost = KitchenStockDeduction::join('ingredients', 'kitchen_stock_deductions.ingredient_id', '=', 'ingredients.id')
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
            'totalRevenue', 'todayRevenue', 'totalCost', 
            'grossProfit', 'profitMargin', 'topProducts', 'salesTrend'
        ));
    }

    /**
     * Cost & Variance Report
     */
    public function costVariance()
    {
        // Compare Theoretical Usage (Recipes) vs Actual Usage (Deductions)
        // This is a complex query: We aggregate deductions by ingredient and compare to recipe expectations
        
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

        // For each ingredient, we'd ideally calculate theoretical usage from finished production logs
        // Theoretical = SUM(batch_size * recipe_qty)
        foreach($variances as $v) {
            $theoretical = DB::table('kitchen_production_logs')
                ->join('recipes', 'kitchen_production_logs.product_id', '=', 'recipes.product_id')
                ->where('recipes.ingredient_id', $v->ingredient_id)
                ->where('kitchen_production_logs.status', 'done')
                ->select(DB::raw('SUM(kitchen_production_logs.total_servings * recipes.quantity / (SELECT servings FROM batch_sizes WHERE product_id = kitchen_production_logs.product_id LIMIT 1)) as theoretical_usage'))
                ->value('theoretical_usage') ?? 0;
            
            $v->theoretical_usage = $theoretical;
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
        // 1. Production Yield (Done vs Wasted)
        $productionStats = DB::table('kitchen_production_logs')
            ->select(
                'status',
                DB::raw('count(*) as count'),
                DB::raw('SUM(total_servings) as total_servings')
            )
            ->groupBy('status')
            ->get();

        // 2. Success Rate
        $doneCount = $productionStats->where('status', 'done')->first()->count ?? 0;
        $totalCount = $productionStats->sum('count');
        $yieldRate = $totalCount > 0 ? ($doneCount / $totalCount) * 100 : 0;

        // 3. Forecasting (Simple 7-day projection)
        $avgDailySales = Transaction::where('created_at', '>=', Carbon::now()->subDays(7))
            ->sum('total_amount') / 7;
        
        $projectedWeeklyRevenue = $avgDailySales * 7;

        return view('yield-forecasting', compact('productionStats', 'yieldRate', 'avgDailySales', 'projectedWeeklyRevenue'));
    }
}
