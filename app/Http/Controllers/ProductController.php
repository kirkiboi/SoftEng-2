<?php
namespace App\Http\Controllers;
use App\Models\Product;
use App\Models\ProductAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class ProductController extends Controller
{
    public function index(Request $request)
    {
        $search = trim($request->input('search'));
        $category = trim($request->input('category'));

        $query = Product::with('recipes');

        if ($search) {
            $query->where('name', 'LIKE', "%{$search}%");
        }

        if ($category && strtolower($category) !== 'all') {
            $query->where('category', $category);
        }

        $products = $query->paginate(5)->withQueryString();

        return view('Menu-Pricing', compact('products', 'search'));
    }

    public function waste(Request $request, Product $product)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|string|max:255',
        ]);



        $product->decrement('stock', $request->quantity);

        ProductAuditLog::create([
            'product_id'   => $product->id,
            'product_name' => $product->name,
            'user_id'      => Auth::id(),
            'action'       => "Wasted {$request->quantity} units. Reason: {$request->reason}",
            'old_price'    => $product->price,
            'new_price'    => null,
        ]);
        
        return redirect()->back()->with('success', 'Product stock marked as wasted.');
    }
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name'     => 'required|string',
            'category' => 'required|in:drinks,snacks,meals,ready_made',
            'price'    => 'required|numeric',
            'image'    => 'nullable|image|mimes:jpg,jpeg,png',
        ]);
        $old_name  = $product->name;
        $old_price = $product->price;
        $old_category = $product->category;
        $old_image = $product->image;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
            $product->image = $imagePath;
        }
        $product->name     = $request->name;
        $product->category = $request->category;
        $product->price    = $request->price;
        $product->save();
        ProductAuditLog::create([
            'product_id'   => $product->id,
            'product_name' => $product->name, 
            'user_id'      => Auth::id(),
            'action'       => 'edited',
            'old_price'    => $old_price,
            'new_price'    => $product->price,
        ]);
        return redirect()->back()->with('success', 'Product updated successfully.');
    }
    public function destroy(Product $product)
    {
        ProductAuditLog::create([
            'product_id'   => $product->id,
            'product_name' => $product->name,
            'user_id'      => Auth::id(),
            'action'       => 'deleted',
            'old_price'    => $product->price,
            'new_price'    => null,
        ]);
        $product->delete();
        return redirect()->back()->with('success', 'Product deleted successfully.');
    }
    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string',
            'category' => 'required|in:drinks,snacks,meals,ready_made',
            'price'    => 'required|numeric',
            'image'    => 'required|image|mimes:jpg,jpeg,png',
        ]);
        $imagePath = $request->file('image')->store('products', 'public');       
        $product = Product::create([
            'name'     => $request->name,
            'category' => $request->category,
            'price'    => $request->price,
            'image'    => $imagePath,
        ]); 
        ProductAuditLog::create([
            'product_id'   => $product->id,
            'product_name' => $product->name,
            'user_id'      => Auth::id(),
            'action'       => 'added',
            'old_price'    => null,
            'new_price'    => $product->price,
        ]);
        return redirect()->back()->with('success', 'Item Added');
    }
}