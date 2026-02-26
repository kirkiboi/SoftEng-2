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

        // Log ingredient creation
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

        $oldName = $ingredient->name;
        $ingredient->update($validated);

        // Log ingredient edit
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
            'supplier' => 'Edited by ' . (Auth::user()->name ?? 'Admin'),
        ]);

        return redirect()->back()->with('success', 'Ingredient updated successfully!');
    }

    public function destroy($id)
    {
        $ingredient = Ingredient::findOrFail($id);
        
        // Log ingredient deletion
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
}