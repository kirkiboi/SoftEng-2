<?php
namespace App\Http\Controllers;
use App\Models\Ingredient;
use Illuminate\Http\Request;
class IngredientController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->input('search');
        $ingredients = Ingredient::query();
        if ($query) {
            $ingredients->where('name', 'like', '%' . $query . '%');
        }

        $ingredients = $ingredients->paginate(8);

        $ingredients->appends($request->all());

        return view('ingredient-list', compact('ingredients'));
    }
    public function destroy(Ingredient $ingredients)
    {
        $ingredients->delete();
        return redirect()->back()->with('success', 'Product deleted successfully.');
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

        Ingredient::create($validated);
        return redirect()->back()->with('success', 'Ingredient added successfully');
    }
}