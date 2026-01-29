<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\ProductAuditLog;
class ProductAuditLogController extends Controller
{
    public function index()
    {
        $logs = ProductAuditLog::latest()->paginate(10); 
        return view('pricing-history', compact('logs'));
    }
}