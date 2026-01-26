<?php
namespace App\Http\Controllers;
use App\Models\Product;
use Illuminate\Http\Request;
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
        return view('MP', compact('products', 'search'));
    }
    public function edit(Product $product)
    {
        return view('editMP', compact('product'));
    }
    public function destroy(Product $product)
    {
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
        Product::create([
            'name'     => $request->name,
            'category' => $request->category,
            'price'    => $request->price,
            'image'    => $imagePath,
        ]);
        return redirect()->back()->with('success', 'Item Added');
    }
}
