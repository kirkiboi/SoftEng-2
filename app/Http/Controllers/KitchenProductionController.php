<?php
namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\KitchenProductionLog;
use App\Models\KitchenStockDeduction;
use App\Models\IngredientAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KitchenProductionController extends Controller
{
    public function index()
    {
        $products = Product::with('recipes.ingredient')->get();
        $ingredients = Ingredient::all();
        $productionLogs = KitchenProductionLog::with('deductions')
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('status');

        $queued = $productionLogs->get('queued', collect());
        $cooking = $productionLogs->get('cooking', collect());
        $done = $productionLogs->get('done', collect())->take(10);

        return view('Kitchen-system', compact('products', 'ingredients', 'queued', 'cooking', 'done'));
    }

    public function startProduction(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'times_cooked' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $recipes = Recipe::where('product_id', $product->id)->with('ingredient')->get();

        if ($recipes->isEmpty()) {
            return response()->json(['error' => 'No recipe found for this product. Please add ingredients via Recipe Manager first.'], 422);
        }

        // Check if enough stock for all ingredients
        $insufficientStock = [];
        foreach ($recipes as $recipe) {
            $required = $recipe->quantity * $validated['times_cooked'];
            if ($recipe->ingredient && $recipe->ingredient->stock < $required) {
                $insufficientStock[] = $recipe->ingredient->name . ' (need ' . $required . $recipe->ingredient->unit . ', have ' . $recipe->ingredient->stock . $recipe->ingredient->unit . ')';
            }
        }

        if (!empty($insufficientStock)) {
            return response()->json([
                'error' => 'Insufficient stock for: ' . implode(', ', $insufficientStock)
            ], 422);
        }

        // Create production log and deduct stock in a transaction
        DB::beginTransaction();
        try {
            $log = KitchenProductionLog::create([
                'user_id' => Auth::id(),
                'product_id' => $product->id,
                'product_name' => $product->name,
                'batch_size' => $recipes->count(),
                'times_cooked' => $validated['times_cooked'],
                'total_servings' => $validated['times_cooked'],
                'status' => 'queued',
            ]);

            foreach ($recipes as $recipe) {
                if (!$recipe->ingredient) continue;

                $deductQty = $recipe->quantity * $validated['times_cooked'];
                $ingredient = $recipe->ingredient;
                $oldStock = $ingredient->stock;
                $newStock = $oldStock - $deductQty;

                $ingredient->update(['stock' => $newStock]);

                KitchenStockDeduction::create([
                    'kitchen_production_log_id' => $log->id,
                    'ingredient_id' => $ingredient->id,
                    'ingredient_name' => $ingredient->name,
                    'quantity_deducted' => $deductQty,
                    'unit' => $ingredient->unit,
                ]);

                IngredientAuditLog::create([
                    'user_id' => Auth::id(),
                    'ingredient_id' => $ingredient->id,
                    'action' => 'stock_out',
                    'ingredient_name' => $ingredient->name,
                    'unit_cost' => $ingredient->cost_per_unit,
                    'total_cost' => $ingredient->cost_per_unit * $deductQty,
                    'quantity_changed' => $deductQty,
                    'old_stock' => $oldStock,
                    'new_stock' => $newStock,
                ]);
            }

            DB::commit();
            return response()->json(['success' => true, 'log' => $log->load('deductions')]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Production failed: ' . $e->getMessage()], 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $log = KitchenProductionLog::findOrFail($id);
        $validated = $request->validate([
            'status' => 'required|in:queued,cooking,done',
        ]);

        // If newly marked as done, increment product stock
        if ($validated['status'] === 'done' && $log->status !== 'done') {
            $product = Product::find($log->product_id);
            if ($product) {
                // Total servings = times_cooked (since batch_size usually implies 1 batch = X servings, 
                // but here times_cooked seems to be the multiplier for the batch. 
                // The log says 'total_servings' => $validated['times_cooked'].
                // Let's assume total_servings is the quantity to add.
                $product->increment('stock', $log->total_servings);
            }
        }

        $log->update(['status' => $validated['status']]);
        return response()->json(['success' => true, 'log' => $log]);
    }

    public function getRecipes($productId)
    {
        $recipes = Recipe::with('ingredient')
            ->where('product_id', $productId)
            ->get();
        return response()->json($recipes);
    }

    public function logs(Request $request)
    {
        $query = KitchenProductionLog::with(['user', 'deductions'])->latest();

        if ($request->filled('search')) {
            $query->where('product_name', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $logs = $query->simplePaginate(10)->withQueryString();
        return view('kitchen-production-logs', compact('logs'));
    }
}
