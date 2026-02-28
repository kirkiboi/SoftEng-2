<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthorizationController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\IngredientController;
use App\Http\Controllers\ProductAuditLogController;
use App\Http\Controllers\RecipeController;
use App\Http\Controllers\KitchenProductionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\POSController;

// Login Route
Route::get('/', function () {return view('login');})->name('login');
Route::post('/login', [AuthorizationController::class, 'login'])->name('login.submit')->middleware('throttle:5,2');

// Main dashboard route
Route::get('/main', function () {return view('main');})->middleware('auth');

// === Kitchen Production Routes (Admin + Chef) ===
Route::middleware(['auth', 'role:admin,chef'])->group(function () {
    Route::get('/kitchenproduction', [KitchenProductionController::class, 'index'])->name('kp');
    Route::post('/kitchen/start-production', [KitchenProductionController::class, 'startProduction'])->name('kitchen.startProduction');
    Route::patch('/kitchen/update-status/{id}', [KitchenProductionController::class, 'updateStatus'])->name('kitchen.updateStatus');
    Route::get('/kitchen/recipes/{product}', [KitchenProductionController::class, 'getRecipes'])->name('kitchen.getRecipes');
    Route::get('/kitchenproductionlogs', [KitchenProductionController::class, 'logs'])->name('kitchen.logs');
    Route::post('/kitchen/start-shift', [KitchenProductionController::class, 'startShift'])->name('kitchen.startShift');
    Route::post('/kitchen/end-shift', [KitchenProductionController::class, 'endShift'])->name('kitchen.endShift');
    Route::delete('/kitchen/cancel/{id}', [KitchenProductionController::class, 'cancelProduction'])->name('kitchen.cancel');
});

// === Recipe Management (Admin only) ===
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::post('/recipes', [RecipeController::class, 'store'])->name('recipes.store');
    Route::put('/recipes/{recipe}', [RecipeController::class, 'update'])->name('recipes.update');
    Route::delete('/recipes/{recipe}', [RecipeController::class, 'destroy'])->name('recipes.destroy');
});
Route::get('/recipes/{product}', [RecipeController::class, 'show'])->name('recipes.show')->middleware('auth');
Route::get('/ingredients/all', function () {return \App\Models\Ingredient::select('id', 'name', 'unit')->get();})->middleware('auth');

// === Analysis and Reporting (Admin only) ===
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/reports/dashboard', [ReportController::class, 'dashboard'])->name('reports.dashboard');
    Route::get('/reports/cost-variance', [ReportController::class, 'costVariance'])->name('reports.cost-variance');
    Route::get('/reports/yield-forecasting', [ReportController::class, 'yieldForecasting'])->name('reports.yield');
    Route::get('/reports/end-of-day', [ReportController::class, 'endOfDay'])->name('reports.end-of-day');
});
Route::get('/analysisandreporting', function () {return redirect()->route('reports.dashboard');})->name('ar')->middleware(['auth', 'role:admin']);

// === Point of Sales Routes (Admin + Cashier) ===
Route::middleware(['auth', 'role:admin,cashier'])->group(function () {
    Route::get('/pointofsales', [POSController::class, 'index'])->name('pos');
    Route::post('/pos/checkout', [POSController::class, 'checkout'])->name('pos.checkout');
    Route::get('/transactionhistory', [POSController::class, 'history'])->name('POShistory');
});

// === Inventory Management (Admin + Chef) ===
Route::middleware(['auth', 'role:admin,chef'])->group(function () {
    Route::get('/stockhistory', [IngredientController::class, 'auditLog'])->name('stock-history');
    Route::get('/inventorymanagement', [IngredientController::class, 'index'])->name('im');
    Route::post('/ingredients', [IngredientController::class, 'store'])->name('ingredients.store');
    Route::put('/ingredients/{ingredient}', [IngredientController::class, 'update'])->name('ingredients.update');
    Route::post('/ingredients/stock-in', [IngredientController::class, 'stockIn'])->name('ingredients.stockIn');
    Route::post('/ingredients/stock-out', [IngredientController::class, 'stockOut'])->name('ingredients.stockOut');
    Route::get('/ingredient-history', [IngredientController::class, 'ingredientHistory'])->name('ingredient-history');
    Route::post('/products/stock-in', [IngredientController::class, 'stockInProduct'])->name('products.stockIn');
});

// Delete ingredients — Admin only
Route::delete('/ingredients/{ingredients}', [IngredientController::class, 'destroy'])->name('ingredients.destroy')->middleware(['auth', 'role:admin']);

// Stock Reconciliation (Admin only)
Route::get('/ingredients/reconcile', [IngredientController::class, 'reconcile'])->name('ingredients.reconcile')->middleware(['auth', 'role:admin']);

// === Menu & Pricing Routes (Admin) ===
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/menuandpricing', [ProductController::class, 'index'])->name('mp');
    Route::get('/menuandpricing/{product}/edit', [ProductController::class, 'edit'])->name('editMP');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::post('/products/{product}/waste', [ProductController::class, 'waste'])->name('products.waste');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    Route::get('/pricinghistory', [ProductAuditLogController::class, 'index'])->name('pricing-history');
    Route::get('/wastelogs', [ProductAuditLogController::class, 'wasteLogs'])->name('waste.logs');
});