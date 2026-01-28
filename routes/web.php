<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthorizationController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\IngredientController;

//FOR MY LOGIN ROUTE
Route::get('/', function () {
    return view('login');
})->name('login');
Route::post('/login', [AuthorizationController::class, 'login'])
    ->name('login.submit')
    ->middleware('throttle:5,2');

//DASHBOARD ROUTE NI, KATONG MAIN JUD
Route::get('/main', function () {
    return view('main');
})->middleware('auth');

//NEW TRANSACTION ROUTE
Route::get('/pointofsales', function () {
    return view('POS');
})->name('pos');

//KITCHEN ROUTE
Route::get('/kitchenproduction', function () {
    return view('kitchen-system');
})->name('kp');

//INGREDIENT LIST NGA ROUTE
Route::get('/inventorymanagement', function () {
    return view('ingredient-list');
})->name('im');

//CVAM NGA WALA PATAWUN NAHUMAN GIATAY
Route::get('/analysisandreporting', function () {
    return view('AR');
})->name('ar');

//POS PART JAPUN NI KATONG TRANSACTION HISTORY
Route::get('/transactionhistory', function () {
    return view('POShistory');
})->name('POShistory');

//INVENTORY MANAGEMENT NI SYA KATONG STOCK IN HISTORY NGA WAPAJAPUN NA SUGDAN YAWA
Route::get('/stockhistory', function () {
    return view('stock-history');
})->name('stock-history');

//MENU AND PRICING NGA AUDIT TRAIL
Route::get('/pricinghistory', function () {
    return view('pricing-history');
})->name('pricing-history');

//STORING INGREDIENTS SHIT
Route::post('/ingredients', [IngredientController::class, 'store'])
    ->name('ingredients.store');

Route::get('/ingredients', [IngredientController::class, 'index'])
->name('ingredients.index');

Route::get('/menuandpricing', [ProductController::class, 'index'])->name('mp');
Route::post('/products', [ProductController::class, 'store'])->name('products.store');
Route::get('/menuandpricing/{product}/edit', [ProductController::class, 'edit'])->name('editMP');
Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
Route::get('/inventorymanagement', [IngredientController::class, 'index'])->name('im');
Route::delete('/products/{product}', [IngredientController::class, 'destroy'])->name('ingredients.destroy');

