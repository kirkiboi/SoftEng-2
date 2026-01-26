<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthorizationController;
use App\Http\Controllers\ProductController;

Route::get('/', function () {
    return view('login');
})->name('login');
Route::post('/login', [AuthorizationController::class, 'login'])->name('login.submit');
Route::get('/main', function () {
    return view('main');
})->middleware('auth');
Route::get('/pointofsales', function () {
    return view('POS');
})->name('pos');
Route::get('/kitchenproduction', function () {
    return view('KP');
})->name('kp');
Route::get('/inventorymanagement', function () {
    return view('IM');
})->name('im');
Route::get('/analysisandreporting', function () {
    return view('AR');
})->name('ar');
Route::get('/transactionhistory', function () {
    return view('POShistory');
})->name('POShistory');

Route::get('/menuandpricing', [ProductController::class, 'index'])->name('mp');
Route::post('/products', [ProductController::class, 'store'])->name('products.store');
Route::get('/menuandpricing/{product}/edit', [ProductController::class, 'edit'])->name('editMP');
Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');