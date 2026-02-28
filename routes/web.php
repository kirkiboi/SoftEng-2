<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthorizationController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\IngredientController;
use App\Http\Controllers\ProductAuditLogController;
use App\Http\Controllers\RecipeController;

// Login Route
Route::get('/', function () {return view('login');})->name('login');
Route::post('/login', [AuthorizationController::class, 'login'])->name('login.submit')->middleware('throttle:5,2');

// Main dashboard route
Route::get('/main', function () {return view('main');})->middleware('auth');

// Kitchen Production Routes
Route::get('/kitchenproduction', [RecipeController::class, 'index'])->name('kp')->middleware('auth');
Route::post('/recipes', [RecipeController::class, 'store'])->name('recipes.store')->middleware('auth');
Route::get('/recipes/{product}', [RecipeController::class, 'show'])->name('recipes.show')->middleware('auth');
Route::delete('/recipes/{recipe}', [RecipeController::class, 'destroy'])->name('recipes.destroy')->middleware('auth');
Route::post('/kitchen/produce', [RecipeController::class, 'produce'])->name('kitchen.produce')->middleware('auth');
Route::get('/ingredients/all', function () {return \App\Models\Ingredient::select('id', 'name', 'unit')->get();})->middleware('auth');

<<<<<<< Updated upstream
// Analysis and Reporting Routes
Route::get('/analysisandreporting', function () {return view('AR');})->name('ar')->middleware('auth');
=======
// === Analysis and Reporting (Admin only) ===
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/reports/dashboard', [ReportController::class, 'dashboard'])->name('reports.dashboard');
    Route::get('/reports/cost-variance', [ReportController::class, 'costVariance'])->name('reports.cost-variance');
    Route::get('/reports/yield-forecasting', [ReportController::class, 'yieldForecasting'])->name('reports.yield');
    Route::get('/reports/end-of-day', [ReportController::class, 'endOfDay'])->name('reports.end-of-day');
});
Route::get('/analysisandreporting', function () {return redirect()->route('reports.dashboard');})->name('ar')->middleware(['auth', 'role:admin']);
>>>>>>> Stashed changes

// Point of Sales Routes
Route::get('/pointofsales', function () {return view('POS');})->name('pos')->middleware('auth');
Route::get('/transactionhistory', function () {return view('POShistory');})->name('POShistory')->middleware('auth');

// Ingredient Management Routes
Route::get('/stockhistory', [IngredientController::class, 'auditLog'])->name('stock-history')->middleware('auth');
Route::get('/inventorymanagement', [IngredientController::class, 'index'])->name('im')->middleware('auth');
Route::post('/ingredients', [IngredientController::class, 'store'])->name('ingredients.store')->middleware('auth');
Route::delete('/ingredients/{ingredients}', [IngredientController::class, 'destroy'])->name('ingredients.destroy')->middleware('auth');
Route::put('/ingredients/{ingredient}', [IngredientController::class, 'update'])->name('ingredients.update')->middleware('auth');

// Menu & Pricing Routes
Route::get('/menuandpricing', [ProductController::class, 'index'])->name('mp')->middleware('auth');
Route::get('/menuandpricing/{product}/edit', [ProductController::class, 'edit'])->name('editMP')->middleware('auth');
Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update')->middleware('auth');
Route::post('/products', [ProductController::class, 'store'])->name('products.store')->middleware('auth');
Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy')->middleware('auth');
Route::get('/pricinghistory', [ProductAuditLogController::class, 'index'])->name('pricing-history')->middleware('auth');