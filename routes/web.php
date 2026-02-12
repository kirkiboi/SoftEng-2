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
use App\Http\Controllers\KitchenProductionController;
Route::get('/kitchenproduction', [KitchenProductionController::class, 'index'])->name('kp')->middleware('auth');
Route::post('/kitchen/start-production', [KitchenProductionController::class, 'startProduction'])->name('kitchen.startProduction')->middleware('auth');
Route::patch('/kitchen/update-status/{id}', [KitchenProductionController::class, 'updateStatus'])->name('kitchen.updateStatus')->middleware('auth');
Route::get('/kitchen/recipes/{product}', [KitchenProductionController::class, 'getRecipes'])->name('kitchen.getRecipes')->middleware('auth');
Route::get('/kitchenproductionlogs', [KitchenProductionController::class, 'logs'])->name('kitchen.logs')->middleware('auth');
Route::post('/kitchen/close', [KitchenProductionController::class, 'closeKitchen'])->name('kitchen.close')->middleware('auth');
Route::delete('/kitchen/cancel/{id}', [KitchenProductionController::class, 'cancelProduction'])->name('kitchen.cancel')->middleware('auth');
Route::post('/recipes', [RecipeController::class, 'store'])->name('recipes.store');
Route::put('/recipes/{recipe}', [RecipeController::class, 'update'])->name('recipes.update');
Route::delete('/recipes/{recipe}', [RecipeController::class, 'destroy'])->name('recipes.destroy');
Route::get('/recipes/{product}', [RecipeController::class, 'show'])->name('recipes.show');
Route::get('/ingredients/all', function () {return \App\Models\Ingredient::select('id', 'name', 'unit')->get();})->middleware('auth');

// Analysis and Reporting Routes
Route::get('/analysisandreporting', function () {return view('AR');})->name('ar')->middleware('auth');

// Point of Sales Routes
use App\Http\Controllers\POSController;
Route::get('/pointofsales', [POSController::class, 'index'])->name('pos')->middleware('auth');
Route::post('/pos/checkout', [POSController::class, 'checkout'])->name('pos.checkout')->middleware('auth');
Route::get('/transactionhistory', [POSController::class, 'history'])->name('POShistory')->middleware('auth');

// Ingredient Management Routes
Route::get('/stockhistory', [IngredientController::class, 'auditLog'])->name('stock-history')->middleware('auth');; // Ingredient audit trail
Route::get('/inventorymanagement', [IngredientController::class, 'index'])->name('im')->middleware('auth');; // Ingredient information padung sa table
Route::post('/ingredients', [IngredientController::class, 'store'])->name('ingredients.store')->middleware('auth');; // Route pag mag add item
Route::delete('/ingredients/{ingredients}', [IngredientController::class, 'destroy'])->name('ingredients.destroy')->middleware('auth');; // Route pang delete sa ingredient
Route::put('/ingredients/{ingredient}', [IngredientController::class, 'update'])->name('ingredients.update')->middleware('auth');; // ROUTE TO UPDATE INGREDIENT
Route::post('/ingredients/stock-in', [IngredientController::class, 'stockIn'])->name('ingredients.stockIn')->middleware('auth'); // Stock-in route
Route::post('/products/stock-in', [IngredientController::class, 'stockInProduct'])->name('products.stockIn')->middleware('auth');
    
// Menu & Pricing Routes
Route::get('/menuandpricing', [ProductController::class, 'index'])->name('mp')->middleware('auth');; // List sa products padung sa table
Route::get('/menuandpricing/{product}/edit', [ProductController::class, 'edit'])->name('editMP')->middleware('auth');; // Route pang edit sa product
Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update')->middleware('auth');; // Route pang update sa product
Route::post('/products', [ProductController::class, 'store'])->name('products.store')->middleware('auth');; // Roure pang add sa product
Route::post('/products/{product}/waste', [ProductController::class, 'waste'])->name('products.waste')->middleware('auth');
Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy')->middleware('auth');; // Route pang delete sa product
Route::get('/pricinghistory', [ProductAuditLogController::class, 'index'])->name('pricing-history')->middleware('auth');; // Product audit trail view
Route::get('/wastelogs', [ProductAuditLogController::class, 'wasteLogs'])->name('waste.logs')->middleware('auth'); // Waste Logs view