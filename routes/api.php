<?php

use App\Http\Controllers\Api\TransactionController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;


Route::controller(AuthController::class)->group(function () {
    Route::post('/register', 'register');
    Route::post('/login', 'login');
});

Route::controller(TransactionController::class)
    ->middleware('auth:sanctum')->group(function () {
    Route::post('/balance', 'createAndUpdateBalance');
    Route::get('/balance', 'showBalance');
    Route::get('/transactions', 'showTransactions');
});

