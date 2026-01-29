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
        $search = $request->query('search');
        $products = Product::when($search, function ($query, $search) {
            $query->where('name', 'LIKE', "%{$search}%");
        })
        ->paginate(6)
        ->withQueryString(); 
        return view('Menu-Pricing', compact('products', 'search'));
    }
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name'     => 'required|string',
            'category' => 'required|in:drinks,snacks,meals',
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
            'category' => 'required|in:drinks,snacks,meals',
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