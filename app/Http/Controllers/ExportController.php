<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\ProductAuditLog;
use App\Models\IngredientAuditLog;
use App\Models\KitchenProductionLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ExportController extends Controller
{
    public function posHistory(Request $request)
    {
        $query = Transaction::with(['items', 'user'])->latest();
        $this->applyDateRange($query, $request);
        if ($request->filled('search')) {
            $query->where('order_id', 'like', '%' . $request->search . '%');
        }
        $rows = $query->get();

        return $this->streamCsv('POS Transaction History', $request, 
            ['Order ID', 'Date & Time', 'Items Sold', 'Payment Method', 'Grand Total', 'Cashier'],
            $rows->map(function ($txn) {
                return [
                    $txn->order_id,
                    $txn->created_at->format('m/d/Y h:i A'),
                    $txn->items->sum('quantity'),
                    ucfirst($txn->payment_method),
                    number_format($txn->total_amount, 2),
                    $txn->user ? $txn->user->first_name . ' ' . $txn->user->last_name : 'System',
                ];
            })->toArray(),
            'pos-transaction-history'
        );
    }
    public function pricingHistory(Request $request)
    {
        $query = ProductAuditLog::with('user')->latest();
        $this->applyDateRange($query, $request);
        if ($request->filled('search')) {
            $query->where('product_name', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        $rows = $query->get();

        return $this->streamCsv('Product Audit Log', $request, 
            ['Product Name', 'Action', 'Date & Time', 'User', 'Previous Price', 'New Price'],
            $rows->map(function ($log) {
                return [
                    $log->product_name,
                    ucfirst($log->action),
                    $log->created_at->format('m/d/Y h:i A'),
                    $log->user ? $log->user->first_name . ' ' . $log->user->last_name : 'System',
                    $log->old_values['price'] ?? '-',
                    $log->new_values['price'] ?? '-',
                ];
            })->toArray(),
            'pricing-history'
        );
    }
    public function stockHistory(Request $request)
    {
        $query = IngredientAuditLog::with('user')->whereIn('action', ['stock_in', 'stock_out'])->latest();
        $this->applyDateRange($query, $request);
        if ($request->filled('search')) {
            $query->where('ingredient_name', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        $rows = $query->get();

        return $this->streamCsv('Stock History', $request, 
            ['Item Name', 'Action', 'Date & Time', 'User', 'Unit Cost', 'Total Cost'],
            $rows->map(function ($log) {
                return [
                    $log->ingredient_name ?? 'Deleted Ingredient',
                    $log->action === 'stock_in' ? 'Stock In' : 'Stock Out',
                    $log->created_at->format('m/d/Y h:i A'),
                    $log->user ? $log->user->first_name . ' ' . $log->user->last_name : 'System',
                    number_format($log->unit_cost, 2),
                    number_format($log->total_cost, 2),
                ];
            })->toArray(),
            'stock-history'
        );
    }
    public function ingredientHistory(Request $request)
    {
        $query = IngredientAuditLog::with('user')->whereIn('action', ['created', 'edited', 'deleted'])->latest();
        $this->applyDateRange($query, $request);
        if ($request->filled('search')) {
            $query->where('ingredient_name', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        $rows = $query->get();

        return $this->streamCsv('Ingredient History', $request,
            ['Ingredient', 'Action', 'Date & Time', 'User', 'Old Stock', 'New Stock'],
            $rows->map(function ($log) {
                return [
                    $log->ingredient_name,
                    ucfirst($log->action),
                    $log->created_at->format('m/d/Y h:i A'),
                    $log->user ? $log->user->first_name . ' ' . $log->user->last_name : 'System',
                    $log->old_stock,
                    $log->new_stock,
                ];
            })->toArray(),
            'ingredient-history'
        );
    }

    public function kitchenLogs(Request $request)
    {
        $query = KitchenProductionLog::with(['user', 'deductions'])->latest();
        $this->applyDateRange($query, $request);
        if ($request->filled('search')) {
            $query->where('product_name', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        $rows = $query->get();

        return $this->streamCsv('Kitchen Production Logs', $request,
            ['Product', 'Times Cooked', 'Servings', 'Status', 'User', 'Ingredients Used', 'Waste Reason', 'Date & Time'],
            $rows->map(function ($log) {
                $deductions = $log->deductions->map(fn($d) => $d->ingredient_name . ': -' . number_format($d->quantity_deducted, 2) . $d->unit)->implode(', ');
                return [
                    $log->product_name,
                    $log->times_cooked,
                    $log->total_servings,
                    ucfirst($log->status),
                    $log->user ? $log->user->first_name : 'System',
                    $deductions,
                    $log->status === 'wasted' ? ($log->waste_reason ?? 'N/A') : '-',
                    $log->created_at->format('m/d/Y h:i A'),
                ];
            })->toArray(),
            'kitchen-production-logs'
        );
    }

    public function wasteLogs(Request $request)
    {
        $query = ProductAuditLog::with('user')->where('action', 'LIKE', 'Wasted%')->latest();
        $this->applyDateRange($query, $request);
        if ($request->filled('search')) {
            $query->where('product_name', 'like', '%' . $request->search . '%');
        }
        $rows = $query->get();

        return response()->streamDownload(function () use ($title, $dateLabel, $headers, $data) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($handle, [$title]);
            fputcsv($handle, [$dateLabel]);
            fputcsv($handle, ['Generated: ' . now()->format('m/d/Y h:i A')]);
            fputcsv($handle, []);
            fputcsv($handle, $headers);
            foreach ($data as $row) fputcsv($handle, $row);
            fputcsv($handle, []);
            fputcsv($handle, ['Total Records: ' . count($data)]);
            fclose($handle);
        }, $fullFilename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function costVariance(Request $request)
    {
        $variances = DB::table('ingredients')
            ->select(
                'ingredients.id as ingredient_id',
                'ingredients.name as ingredient_name',
                'ingredients.unit',
                'ingredients.cost_per_unit',
                DB::raw('(SELECT SUM(quantity_deducted) FROM kitchen_stock_deductions WHERE ingredient_id = ingredients.id) as actual_usage'),
                DB::raw('(SELECT SUM(kpl.times_cooked * r.quantity) FROM kitchen_production_logs kpl JOIN recipes r ON kpl.product_id = r.product_id WHERE r.ingredient_id = ingredients.id AND kpl.status IN ("done", "served")) as theoretical_usage')
            )->get();

        $rows = $variances->map(function ($v) {
            $tu = $v->theoretical_usage ?? 0;
            $au = $v->actual_usage ?? 0;
            $variance = $tu - $au;
            $variancePercent = $tu > 0 ? ($variance / $tu) * 100 : 0;
            $lossGain = $variance * $v->cost_per_unit;
            return [
                $v->ingredient_name,
                number_format($au, 2) . ' ' . $v->unit,
                number_format($tu, 2) . ' ' . $v->unit,
                number_format($variance, 2) . ' ' . $v->unit,
                number_format($variancePercent, 1) . '%',
                number_format($lossGain, 2)
            ];
        })->toArray();

        return $this->streamCsv('Cost & Variance Report', $request, 
            ['Ingredient Name', 'Actual Usage', 'Theoretical Usage', 'Variance', 'Variance %', 'Loss/Gain (PHP)'],
            $rows, 'cost-variance-report'
        );
    }

    public function yieldForecasting(Request $request)
    {
        $topProduced = DB::table('kitchen_production_logs')
            ->select('product_name', DB::raw('count(*) as batch_count'), DB::raw('SUM(total_servings) as total_servings'))
            ->whereIn('status', ['done', 'served'])
            ->groupBy('product_name')
            ->orderBy('batch_count', 'desc')
            ->get();

        $rows = $topProduced->map(function ($p) {
            return [ $p->product_name, 'Top Produced', $p->batch_count, $p->total_servings ];
        })->toArray();

        $mostWasted = DB::table('kitchen_production_logs')
            ->select('product_name', DB::raw('count(*) as batch_count'), DB::raw('SUM(total_servings) as total_servings'))
            ->where('status', 'wasted')
            ->groupBy('product_name')
            ->orderBy('batch_count', 'desc')
            ->get();
            
        foreach ($mostWasted as $w) {
            $rows[] = [ $w->product_name, 'Most Wasted', $w->batch_count, $w->total_servings ];
        }

        return $this->streamCsv('Yield & Forecasting Overview', $request, 
            ['Product Name', 'Category', 'Batch Count', 'Total Servings'],
            $rows, 'yield-forecasting-report'
        );
    }

    private function applyDateRange($query, Request $request)
    {
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', Carbon::parse($request->date_from));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', Carbon::parse($request->date_to));
        }
    }

    private function streamCsv($title, Request $request, array $headers, array $data, string $filename)
    {
        $dateFrom = $request->input('date_from', 'All');
        $dateTo = $request->input('date_to', 'All');
        $dateLabel = ($dateFrom !== 'All' || $dateTo !== 'All') 
            ? "Date Range: {$dateFrom} to {$dateTo}" 
            : "Date Range: All Records";

        $timestamp = now()->format('Y-m-d_His');
        $fullFilename = "{$filename}_{$timestamp}.csv";

        return response()->streamDownload(function () use ($title, $dateLabel, $headers, $data) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($handle, [$title]);
            fputcsv($handle, [$dateLabel]);
            fputcsv($handle, ['Generated: ' . now()->format('m/d/Y h:i A')]);
            fputcsv($handle, []); 
            fputcsv($handle, $headers);
            foreach ($data as $row) {
                fputcsv($handle, $row);
            }
            fputcsv($handle, []);
            fputcsv($handle, ['Total Records: ' . count($data)]);
            fclose($handle);
        }, $fullFilename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}