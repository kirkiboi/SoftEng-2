<?php
namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class POSController extends Controller
{
    public function index()
    {
        $products = Product::orderBy('category')->orderBy('name')->get();
        return view('POS', compact('products'));
    }

    public function checkout(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'payment_method' => 'required|in:cash,gcash',
            'amount_paid' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $totalAmount = 0;
            $itemsData = [];

            foreach ($validated['items'] as $item) {
                $product = Product::lockForUpdate()->findOrFail($item['product_id']);
                
                if ($product->stock < $item['quantity']) {
                    throw new \Exception("Insufficient stock for {$product->name}. available: {$product->stock}");
                }

                $subtotal = $product->price * $item['quantity'];
                $totalAmount += $subtotal;
                
                // Decrement Stock
                $product->decrement('stock', $item['quantity']);

                $itemsData[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $item['quantity'],
                    'unit_price' => $product->price,
                    'subtotal' => $subtotal,
                ];
            }

            $changeAmount = max(0, $validated['amount_paid'] - $totalAmount);

            // Generate order ID: P-YYYYMMDD-NNNN
            $today = now()->format('Ymd');
            $lastOrder = Transaction::where('order_id', 'like', "P-{$today}-%")
                ->orderBy('id', 'desc')
                ->first();
            $nextNum = $lastOrder
                ? ((int)substr($lastOrder->order_id, -4)) + 1
                : 1;
            $orderId = "P-{$today}-" . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

            $transaction = Transaction::create([
                'user_id' => Auth::id(),
                'order_id' => $orderId,
                'total_amount' => $totalAmount,
                'payment_method' => $validated['payment_method'],
                'amount_paid' => $validated['amount_paid'],
                'change_amount' => $changeAmount,
                'status' => 'completed',
            ]);

            foreach ($itemsData as $itemData) {
                TransactionItem::create(array_merge($itemData, [
                    'transaction_id' => $transaction->id,
                ]));
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'transaction' => $transaction->load('items'),
                'order_id' => $orderId,
                'total_amount' => $totalAmount,
                'payment_method' => $validated['payment_method'],
                'amount_paid' => $validated['amount_paid'],
                'change_amount' => $changeAmount,
                'items' => $itemsData,
                'date' => now()->format('m/d/Y h:i A'),
                'cashier' => Auth::user()->first_name . ' ' . Auth::user()->last_name,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Checkout failed: ' . $e->getMessage()], 500);
        }
    }

    public function history(Request $request)
    {
        $query = Transaction::with(['items', 'user'])->latest();

        if ($request->filled('search')) {
            $query->where('order_id', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $transactions = $query->simplePaginate(10)->withQueryString();
        return view('POShistory', compact('transactions'));
    }
}
