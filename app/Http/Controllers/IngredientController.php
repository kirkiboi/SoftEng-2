<?php
namespace App\Http\Controllers;
use App\Models\Ingredient;
use Illuminate\Http\Request;
use App\Models\IngredientAuditLog;
use Illuminate\Support\Facades\Auth;
class IngredientController extends Controller
{
    public function index(Request $request)
    {
        $ingredients = Ingredient::query();
        if ($request->filled('search')) {
            $ingredients->where('name', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('filter-category')) {
            $ingredients->where('category', $request->input('filter-category'));
        }
        $ingredients = $ingredients
            ->paginate(6)
            ->withQueryString(); 
        
        $products = \App\Models\Product::where('category', 'ready_made')->orderBy('name')->get();

        return view('ingredient-list', compact('ingredients', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'unit' => 'required|string|max:50',
            'cost_per_unit' => 'required|numeric|min:0',
            'stock' => 'required|numeric|min:0',
            'threshold' => 'required|numeric|min:0',
        ]);

        $ingredient = Ingredient::create($validated);

        // Log ingredient creation with JSON values
        IngredientAuditLog::create([
            'user_id' => Auth::id(),
            'ingredient_id' => $ingredient->id,
            'action' => 'created',
            'ingredient_name' => $ingredient->name,
            'unit_cost' => $ingredient->cost_per_unit,
            'total_cost' => $ingredient->cost_per_unit * $ingredient->stock,
            'quantity_changed' => $ingredient->stock,
            'old_stock' => 0,
            'new_stock' => $ingredient->stock,
            'supplier' => 'Initial stock',
            'new_values' => [
                'name' => $ingredient->name,
                'category' => $ingredient->category,
                'unit' => $ingredient->unit,
                'cost_per_unit' => $ingredient->cost_per_unit,
                'stock' => $ingredient->stock,
                'threshold' => $ingredient->threshold,
            ],
        ]);

        return redirect()->back()->with('success', 'Ingredient added successfully!');
    }

    public function update(Request $request, Ingredient $ingredient)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'unit' => 'required|string|max:50',
            'cost_per_unit' => 'required|numeric|min:0.01',
            'threshold' => 'required|numeric|min:0',
        ]);

        // Capture old values before update
        $oldValues = [
            'name' => $ingredient->name,
            'category' => $ingredient->category,
            'unit' => $ingredient->unit,
            'cost_per_unit' => $ingredient->cost_per_unit,
            'threshold' => $ingredient->threshold,
        ];

        // Build human-readable change summary
        $changes = [];
        if ($ingredient->name !== $validated['name']) $changes[] = 'name: ' . $ingredient->name . ' → ' . $validated['name'];
        if ($ingredient->category !== $validated['category']) $changes[] = 'category: ' . $ingredient->category . ' → ' . $validated['category'];
        if ($ingredient->unit !== $validated['unit']) $changes[] = 'unit: ' . $ingredient->unit . ' → ' . $validated['unit'];
        if ((float)$ingredient->cost_per_unit !== (float)$validated['cost_per_unit']) $changes[] = 'cost: ₱' . number_format($ingredient->cost_per_unit, 2) . ' → ₱' . number_format($validated['cost_per_unit'], 2);
        if ((float)$ingredient->threshold !== (float)$validated['threshold']) $changes[] = 'threshold: ' . $ingredient->threshold . ' → ' . $validated['threshold'];
        $changeDetails = !empty($changes) ? implode(', ', $changes) : 'No changes detected';

        $ingredient->update($validated);

        // Log with structured JSON old/new values
        IngredientAuditLog::create([
            'user_id' => Auth::id(),
            'ingredient_id' => $ingredient->id,
            'action' => 'edited',
            'ingredient_name' => $ingredient->name,
            'unit_cost' => $ingredient->cost_per_unit,
            'total_cost' => 0,
            'quantity_changed' => 0,
            'old_stock' => $ingredient->stock,
            'new_stock' => $ingredient->stock,
            'supplier' => $changeDetails,
            'old_values' => $oldValues,
            'new_values' => [
                'name' => $validated['name'],
                'category' => $validated['category'],
                'unit' => $validated['unit'],
                'cost_per_unit' => $validated['cost_per_unit'],
                'threshold' => $validated['threshold'],
            ],
        ]);

        return redirect()->back()->with('success', 'Ingredient updated successfully!');
    }

    public function destroy($id)
    {
        $ingredient = Ingredient::findOrFail($id);
        
        // Log ingredient deletion with old values
        IngredientAuditLog::create([
            'user_id' => Auth::id(),
            'ingredient_id' => $ingredient->id,
            'action' => 'deleted',
            'ingredient_name' => $ingredient->name,
            'unit_cost' => $ingredient->cost_per_unit,
            'total_cost' => 0,
            'quantity_changed' => $ingredient->stock,
            'old_stock' => $ingredient->stock,
            'new_stock' => 0,
            'supplier' => 'Deleted by ' . (Auth::user()->name ?? 'Admin'),
            'old_values' => [
                'name' => $ingredient->name,
                'category' => $ingredient->category,
                'unit' => $ingredient->unit,
                'cost_per_unit' => $ingredient->cost_per_unit,
                'stock' => $ingredient->stock,
                'threshold' => $ingredient->threshold,
            ],
        ]);

        $ingredient->delete();
        return redirect()->back()->with('success', 'Ingredient deleted successfully!');
    }

    public function stockIn(Request $request)
    {
        $validated = $request->validate([
            'ingredient_id' => 'required|exists:ingredients,id',
            'quantity' => 'required|numeric|min:0.01',
            'supplier' => 'nullable|string|max:255',
        ]);

        $ingredient = Ingredient::findOrFail($validated['ingredient_id']);
        $oldStock = $ingredient->stock;
        $newStock = $oldStock + $validated['quantity'];

        $ingredient->update(['stock' => $newStock]);

        IngredientAuditLog::create([
            'user_id' => Auth::id(),
            'ingredient_id' => $ingredient->id,
            'action' => 'stock_in',
            'ingredient_name' => $ingredient->name,
            'unit_cost' => $ingredient->cost_per_unit,
            'total_cost' => $ingredient->cost_per_unit * $validated['quantity'],
            'quantity_changed' => $validated['quantity'],
            'old_stock' => $oldStock,
            'new_stock' => $newStock,
            'supplier' => $validated['supplier'] ?? null,
        ]);

        return redirect()->back()->with('success', 'Stock updated successfully! Added ' . $validated['quantity'] . ' ' . $ingredient->unit . ' of ' . $ingredient->name);
    }

    /**
     * Manual Stock-Out (Expired, Damaged, Spilled, etc.)
     */
    public function stockOut(Request $request)
    {
        $validated = $request->validate([
            'ingredient_id' => 'required|exists:ingredients,id',
            'quantity' => 'required|numeric|min:0.01',
            'reason' => 'required|string|max:255',
        ]);

        $ingredient = Ingredient::findOrFail($validated['ingredient_id']);
        
        if ($ingredient->stock < $validated['quantity']) {
            return redirect()->back()->withErrors(['quantity' => 'Cannot stock out more than available stock (' . $ingredient->stock . ' ' . $ingredient->unit . ').']);
        }

        $oldStock = $ingredient->stock;
        $newStock = $oldStock - $validated['quantity'];

        $ingredient->update(['stock' => $newStock]);

        IngredientAuditLog::create([
            'user_id' => Auth::id(),
            'ingredient_id' => $ingredient->id,
            'action' => 'stock_out',
            'ingredient_name' => $ingredient->name,
            'unit_cost' => $ingredient->cost_per_unit,
            'total_cost' => $ingredient->cost_per_unit * $validated['quantity'],
            'quantity_changed' => $validated['quantity'],
            'old_stock' => $oldStock,
            'new_stock' => $newStock,
            'supplier' => $validated['reason'],
        ]);

        return redirect()->back()->with('success', 'Stock out recorded! Removed ' . $validated['quantity'] . ' ' . $ingredient->unit . ' of ' . $ingredient->name . ' (' . $validated['reason'] . ')');
    }

    public function stockInProduct(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = \App\Models\Product::findOrFail($validated['product_id']);
        $product->increment('stock', $validated['quantity']);
        
        return redirect()->back()->with('success', 'Product stock updated! Added ' . $validated['quantity'] . ' to ' . $product->name);
    }

    public function auditLog(Request $request)
    {
        $query = IngredientAuditLog::with('user')
            ->whereIn('action', ['stock_in', 'stock_out']);

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('search')) {
            $query->where('ingredient_name', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }
        $logs = $query->latest()->paginate(10)->withQueryString();
        $users = \App\Models\User::all();
        return view('stock-history', compact('logs', 'users'));
    }

    /**
     * Ingredient History — all actions (created, edited, deleted, stock_in, stock_out)
     */
    public function ingredientHistory(Request $request)
    {
        $query = IngredientAuditLog::with('user')
            ->whereIn('action', ['created', 'edited', 'deleted']);

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        if ($request->filled('search')) {
            $query->where('ingredient_name', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $logs = $query->latest()->paginate(15)->withQueryString();
        return view('ingredient-history', compact('logs'));
    }

    /**
     * Stock Reconciliation — compares actual vs expected stock
     */
    public function reconcile()
    {
        $ingredients = Ingredient::all();
        $discrepancies = [];

        foreach ($ingredients as $ing) {
            // Sum all stock-in quantities
            $totalIn = IngredientAuditLog::where('ingredient_id', $ing->id)
                ->where('action', 'stock_in')
                ->sum('quantity_changed');

            // Sum all stock-out quantities (manual + production)
            $totalOut = IngredientAuditLog::where('ingredient_id', $ing->id)
                ->where('action', 'stock_out')
                ->sum('quantity_changed');

            // Initial stock from creation
            $initialStock = IngredientAuditLog::where('ingredient_id', $ing->id)
                ->where('action', 'created')
                ->sum('quantity_changed');

            $expectedStock = $initialStock + $totalIn - $totalOut;
            $actualStock = $ing->stock;
            $difference = round($actualStock - $expectedStock, 4);

            if (abs($difference) > 0.01) {
                $discrepancies[] = [
                    'ingredient' => $ing->name,
                    'expected' => round($expectedStock, 4),
                    'actual' => round($actualStock, 4),
                    'difference' => $difference,
                    'unit' => $ing->unit,
                ];
            }
        }

        return response()->json([
            'total_ingredients' => $ingredients->count(),
            'discrepancies_found' => count($discrepancies),
            'discrepancies' => $discrepancies,
            'status' => count($discrepancies) === 0 ? 'All stock values are consistent.' : 'Discrepancies detected.',
        ]);
    }
}