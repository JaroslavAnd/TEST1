<?php

use App\Http\Controllers\BankAccountController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CreditController;
use App\Http\Controllers\CreditTypeController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TestController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test', [TestController::class, 'test']);

// Lab 2 CRUD routes for Product
Route::get('/products', [ProductController::class, 'getProducts']);
Route::get('/products/{id}', [ProductController::class, 'getProductItem']);
Route::post('/products', [ProductController::class, 'createProduct']);
Route::match(['put', 'patch'], '/products/{id}', [ProductController::class, 'updateProduct']);
Route::delete('/products/{id}', [ProductController::class, 'deleteProduct']);

// Lab 3 Banking Credit System API
Route::prefix('api')->group(function () {
    // 1. Clients
    Route::apiResource('clients', ClientController::class);

    // 2. Bank Accounts
    Route::apiResource('bank-accounts', BankAccountController::class);

    // 3. Credit Types
    Route::apiResource('credit-types', CreditTypeController::class);

    // 4. Credits
    Route::apiResource('credits', CreditController::class);

    // 5. Payments
    Route::apiResource('payments', PaymentController::class);
});
