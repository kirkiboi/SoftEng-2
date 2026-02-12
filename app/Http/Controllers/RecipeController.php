<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Recipe;
use App\Models\Product;
use App\Models\Ingredient;
use App\Models\KitchenLog;

class RecipeController extends Controller
{
    /**
     * Kitchen Production page
     */
    public function index()
    {
        $products = Product::all();
        $ingredients = Ingredient::all();
        return view('Kitchen-system', compact('products', 'ingredients'));
    }

    /**
     * Save a single recipe entry (product + batch_size + ingredient + quantity)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id'    => 'required|exists:products,id',
            'batch_size'    => 'required|integer|in:10,20,30,40,50',
            'ingredient_id' => 'required|exists:ingredients,id',
            'quantity'      => 'required|numeric|min:0.01',
        ]);

        Recipe::updateOrCreate(
            [
                'product_id'    => $validated['product_id'],
                'batch_size'    => $validated['batch_size'],
                'ingredient_id' => $validated['ingredient_id'],
            ],
            [
                'quantity' => $validated['quantity'],
            ]
        );

        return response()->json(['message' => 'Recipe saved successfully']);
    }

    /**
     * Get recipes for a product + batch_size combo
     */
    public function show($productId, Request $request)
    {
        $batchSize = $request->query('batch_size');

        $recipes = Recipe::with('ingredient')
            ->where('product_id', $productId)
            ->where('batch_size', $batchSize)
            ->get();

        return response()->json($recipes);
    }

    /**
     * Delete a recipe entry
     */
    public function destroy($id)
    {
        $recipe = Recipe::findOrFail($id);
        $recipe->delete();
        return response()->json(['message' => 'Recipe deleted']);
    }

    /**
     * Produce: deduct ingredients and log production
     */
    public function produce(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'batch_size' => 'required|integer|in:10,20,30,40,50',
            'quantity'   => 'required|integer|min:1',
        ]);

        $recipes = Recipe::where('product_id', $validated['product_id'])
            ->where('batch_size', $validated['batch_size'])
            ->get();

        if ($recipes->isEmpty()) {
            return response()->json(['message' => 'No recipe found for this product and batch size.'], 404);
        }

        // Check stock first
        foreach ($recipes as $recipe) {
            $requiredQuantity = $recipe->quantity * $validated['quantity'];
            if ($recipe->ingredient->stock < $requiredQuantity) {
                return response()->json([
                    'message' => "Insufficient stock for {$recipe->ingredient->name}. Required: {$requiredQuantity}, Available: {$recipe->ingredient->stock}"
                ], 400);
            }
        }

        // Deduct stock
        foreach ($recipes as $recipe) {
            $requiredQuantity = $recipe->quantity * $validated['quantity'];
            $recipe->ingredient->decrement('stock', $requiredQuantity);
        }

        // Log the production
        KitchenLog::create([
            'product_id'   => $validated['product_id'],
            'batch_size'   => $validated['batch_size'],
            'times_cooked' => $validated['quantity'],
            'cooked_at'    => now(),
        ]);

        return response()->json(['message' => 'Production started. Ingredients deducted.']);
    }
}