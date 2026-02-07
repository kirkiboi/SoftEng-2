<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Recipe;
use App\Models\Product;
use App\Models\Ingredient;

class RecipeController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id'    => 'required|exists:products,id',
            'ingredient_id' => 'required|exists:ingredients,id',
            'quantity'      => 'required|numeric|min:0.01',
        ]);
        Recipe::updateOrCreate(
            [
                'product_id'    => $validated['product_id'],
                'ingredient_id' => $validated['ingredient_id'],
            ],
            [
                'quantity' => $validated['quantity'],
            ]
        );
        return response()->json([
            'message' => 'Recipe saved successfully',
        ]);
    }
    public function show($productId)
    {
        $recipes = Recipe::with('ingredient')
                        ->where('product_id', $productId)
                        ->get();
        return response()->json($recipes);
    }
    public function index()
    {
        $products = Product::all();
        $ingredients = Ingredient::all();

        return view('Kitchen-system', compact('products', 'ingredients'));
    }
}