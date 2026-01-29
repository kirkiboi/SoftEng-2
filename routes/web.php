<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthorizationController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\IngredientController;
use App\Http\Controllers\ProductAuditLogController;

// ----------------- LOGIN ROUTES -----------------
Route::get('/', function () {
    return view('login');
})->name('login');

Route::post('/login', [AuthorizationController::class, 'login'])
    ->name('login.submit')
    ->middleware('throttle:5,2');

// ----------------- DASHBOARD -----------------
Route::get('/main', function () {
    return view('main');
})->middleware('auth');

// ----------------- POS ROUTE -----------------
Route::get('/pointofsales', function () {
    return view('POS');
})->name('pos');

// ----------------- KITCHEN ROUTE -----------------
Route::get('/kitchenproduction', function () {
    return view('kitchen-system');
})->name('kp');

// ----------------- INVENTORY / INGREDIENT ROUTES -----------------
Route::get('/inventorymanagement', [IngredientController::class, 'index'])->name('im');
Route::post('/ingredients', [IngredientController::class, 'store'])->name('ingredients.store');
Route::delete('/ingredients/{ingredients}', [IngredientController::class, 'destroy'])->name('ingredients.destroy');

// ----------------- REPORTS / ANALYSIS -----------------
Route::get('/analysisandreporting', function () {
    return view('AR');
})->name('ar');

Route::get('/transactionhistory', function () {
    return view('POShistory');
})->name('POShistory');

Route::get('/stockhistory', function () {
    return view('stock-history');
})->name('stock-history');

// ----------------- MENU & PRICING / AUDIT LOG -----------------
// Audit trail view
Route::get('/pricinghistory', [ProductAuditLogController::class, 'index'])->name('pricing-history');

// Menu & Pricing
Route::get('/menuandpricing', [ProductController::class, 'index'])->name('mp');           // List products
Route::get('/menuandpricing/{product}/edit', [ProductController::class, 'edit'])->name('editMP'); // Edit form
Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update'); // Update
Route::post('/products', [ProductController::class, 'store'])->name('products.store');      // Add new
Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy'); // Delete
