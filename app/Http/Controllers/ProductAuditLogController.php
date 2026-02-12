<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\ProductAuditLog;
use Carbon\Carbon;
use App\Models\User;

class ProductAuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ProductAuditLog::with('user')->latest();

        if ($request->filled('search')) {
            $query->where('product_name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('date')) {
            $date = Carbon::parse($request->date);
            $query->whereDate('created_at', $date);
        }
         if ($request->filled('action')) {
        $query->where('action', $request->action);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $users = User::orderBy('first_name')->get();
        $logs = $query->paginate(10)->withQueryString();
        return view('pricing-history', compact('logs', 'users'));
    }

    public function wasteLogs(Request $request)
    {
        $query = ProductAuditLog::with('user')
            ->where('action', 'LIKE', 'Wasted%')
            ->latest();

        if ($request->filled('search')) {
            $query->where('product_name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('date')) {
            $date = Carbon::parse($request->date);
            $query->whereDate('created_at', $date);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $users = User::orderBy('first_name')->get();
        $logs = $query->paginate(10)->withQueryString();
        return view('Waste-Logs', compact('logs', 'users'));
    }
}