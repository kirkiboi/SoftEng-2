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
            ->simplePaginate(6)
            ->withQueryString(); 
        
        $products = \App\Models\Product::whereIn('category', ['drinks', 'snacks'])->orderBy('name')->get();

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

        Ingredient::create($validated);

        return redirect()->back()->with('success', 'Ingredient added successfully!');
    }

    public function update(Request $request, Ingredient $ingredient)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'unit' => 'required|string|max:50',
        ]);

        $ingredient->update($validated);

        return redirect()->back()->with('success', 'Ingredient updated successfully!');
    }

    public function destroy($id)
    {
        $ingredient = Ingredient::findOrFail($id);
        $ingredient->delete();
        return redirect()->back()->with('success', 'Ingredient deleted successfully!');
    }

    public function stockIn(Request $request)
    {
        // ... (existing stockIn for ingredients)
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
        $query = IngredientAuditLog::with('user');

        // FILTER ROPDOWN PANG FILTER VIA ACTION TAKEN
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        // FILTER DROPDOWN TONG PARA SA USER
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        // SEARCH INPUT PANG SEARCH USING NAME SA INGREDIENT
        if ($request->filled('search')) {
            $query->where('ingredient_name', 'like', '%' . $request->search . '%');
        }
        // FILTER BY DATE
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }
        $logs = $query->latest()->simplePaginate(10)->withQueryString();
        $users = \App\Models\User::all(); // For the user filter dropdown
        return view('stock-history', compact('logs', 'users'));
    }
}