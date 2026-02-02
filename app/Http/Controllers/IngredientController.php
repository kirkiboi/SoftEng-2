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
        return view('ingredient-list', compact('ingredients'));
    }
    public function destroy(Ingredient $ingredients)
    {
        IngredientAuditLog::create([
            'user_id' => Auth::id(),
            'ingredient_id' => $ingredients->id,
            'action' => 'deleted',
            'ingredient_name' => $ingredients->name,
            'unit_cost' => $ingredients->cost_per_unit,
            'total_cost' => $ingredients->cost_per_unit * $ingredients->stock,
        ]);

        $ingredients->delete();

        return redirect()->back()->with('success', 'Product deleted successfully.');
    }
    public function update(Request $request, Ingredient $ingredient)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required',
            'unit' => 'required',
        ]);

        $ingredient->update($validated);
        IngredientAuditLog::create([
            'user_id' => Auth::id(),
            'ingredient_id' => $ingredient->id,
            'action' => 'updated',
            'ingredient_name' => $ingredient->name,
            'unit_cost' => $ingredient->cost_per_unit,
            'total_cost' => $ingredient->cost_per_unit * $ingredient->stock,
        ]);

        return redirect()->back()->with('success', 'Ingredient updated successfully');
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required',
            'unit' => 'required',
            'cost_per_unit' => 'required|numeric|min:0',
            'stock' => 'required|numeric|min:0',
            'threshold' => 'required'
        ]);

        $ingredient = Ingredient::create($validated);

        IngredientAuditLog::create([
            'user_id' => Auth::id(),
            'ingredient_id' => $ingredient->id,
            'action' => 'created',
            'ingredient_name' => $ingredient->name,
            'unit_cost' => $ingredient->cost_per_unit,
            'total_cost' => $ingredient->cost_per_unit * $ingredient->stock,
        ]);

        return redirect()->back()->with('success', 'Ingredient added successfully');
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