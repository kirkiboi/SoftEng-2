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
Route::post('/recipes', [RecipeController::class, 'store'])->name('recipes.store');
Route::get('/recipes/{product}', [RecipeController::class, 'show'])->name('recipes.show');
Route::get('/kitchenproduction', [RecipeController::class, 'index'])->name('kp')->middleware('auth');
Route::get('/ingredients/all', function () {return \App\Models\Ingredient::select('id', 'name', 'unit')->get();})->middleware('auth');

// Analysis and Reporting Routes
Route::get('/analysisandreporting', function () {return view('AR');})->name('ar')->middleware('auth');

// Point of Sales Routes
Route::get('/pointofsales', function () {return view('POS');})->name('pos')->middleware('auth');;
Route::get('/transactionhistory', function () {return view('POShistory');})->name('POShistory')->middleware('auth');;

// Ingredient Management Routes
Route::get('/stockhistory', [IngredientController::class, 'auditLog'])->name('stock-history')->middleware('auth');; // Ingredient audit trail
Route::get('/inventorymanagement', [IngredientController::class, 'index'])->name('im')->middleware('auth');; // Ingredient information padung sa table
Route::post('/ingredients', [IngredientController::class, 'store'])->name('ingredients.store')->middleware('auth');; // Route pag mag add item
Route::delete('/ingredients/{ingredients}', [IngredientController::class, 'destroy'])->name('ingredients.destroy')->middleware('auth');; // Route pang delete sa ingredient
Route::put('/ingredients/{ingredient}', [IngredientController::class, 'update'])->name('ingredients.update')->middleware('auth');; // ROUTE TO UPDATE INGREDIENT
    
// Menu & Pricing Routes
Route::get('/menuandpricing', [ProductController::class, 'index'])->name('mp')->middleware('auth');; // List sa products padung sa table
Route::get('/menuandpricing/{product}/edit', [ProductController::class, 'edit'])->name('editMP')->middleware('auth');; // Route pang edit sa product
Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update')->middleware('auth');; // Route pang update sa product
Route::post('/products', [ProductController::class, 'store'])->name('products.store')->middleware('auth');; // Roure pang add sa product
Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy')->middleware('auth');; // Route pang delete sa product
Route::get('/pricinghistory', [ProductAuditLogController::class, 'index'])->name('pricing-history')->middleware('auth');; // Product audit trail view