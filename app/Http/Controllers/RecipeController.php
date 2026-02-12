<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Recipe;
use App\Models\Product;
use App\Models\Ingredient;
use App\Models\BatchSize;

class RecipeController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id'    => 'required|exists:products,id',
            'ingredient_id' => 'required|exists:ingredients,id',
            'quantity'      => 'required|numeric|min:0.01',
        ]);

        // Find or create a batch size for this product
        $batchSize = BatchSize::firstOrCreate(
            ['product_id' => $validated['product_id']],
            ['servings' => 1]
        );

        Recipe::updateOrCreate(
            [
                'product_id'    => $validated['product_id'],
                'ingredient_id' => $validated['ingredient_id'],
            ],
            [
                'quantity'       => $validated['quantity'],
                'batch_sizes_id' => $batchSize->id,
            ]
        );
        return response()->json([
            'message' => 'Recipe saved successfully',
        ]);
    }

    public function update(Request $request, $id)
    {
        $recipe = Recipe::findOrFail($id);
        $validated = $request->validate([
            'quantity' => 'required|numeric|min:0.01',
        ]);
        $recipe->update(['quantity' => $validated['quantity']]);
        return response()->json(['message' => 'Recipe updated successfully']);
    }

    public function destroy($id)
    {
        $recipe = Recipe::findOrFail($id);
        $recipe->delete();
        return response()->json(['message' => 'Ingredient removed from recipe']);
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
        $products = Product::with('recipes')->get();
        $ingredients = Ingredient::all();

        return view('Kitchen-system', compact('products', 'ingredients'));
    }
}