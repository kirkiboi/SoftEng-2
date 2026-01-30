<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthorizationController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\IngredientController;
use App\Http\Controllers\ProductAuditLogController;

// Login Route
Route::get('/', function () {return view('login');})->name('login');
Route::post('/login', [AuthorizationController::class, 'login'])->name('login.submit')->middleware('throttle:5,2');

// Main dashboard route
Route::get('/main', function () {return view('main');})->middleware('auth');

// Kitchen Production Routes
Route::get('/kitchenproduction', function () {return view('kitchen-system');})->name('kp');

// Analysis and Reporting Routes
Route::get('/analysisandreporting', function () {return view('AR');})->name('ar');

// Point of Sales Routes
Route::get('/pointofsales', function () {return view('POS');})->name('pos');
Route::get('/transactionhistory', function () {return view('POShistory');})->name('POShistory');

// Ingredient Management Routes
Route::get('/stockhistory', [IngredientController::class, 'auditLog'])->name('stock-history'); // Ingredient audit trail
Route::get('/inventorymanagement', [IngredientController::class, 'index'])->name('im'); // Ingredient information padung sa table
Route::post('/ingredients', [IngredientController::class, 'store'])->name('ingredients.store'); // Route pag mag add item
Route::delete('/ingredients/{ingredients}', [IngredientController::class, 'destroy'])->name('ingredients.destroy'); // Route pang delete sa ingredient

// Menu & Pricing Routes
Route::get('/menuandpricing', [ProductController::class, 'index'])->name('mp'); // List sa products padung sa table
Route::get('/menuandpricing/{product}/edit', [ProductController::class, 'edit'])->name('editMP'); // Route pang edit sa product
Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update'); // Route pang update sa product
Route::post('/products', [ProductController::class, 'store'])->name('products.store'); // Roure pang add sa product
Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy'); // Route pang delete sa product
Route::get('/pricinghistory', [ProductAuditLogController::class, 'index'])->name('pricing-history'); // Product audit trail view