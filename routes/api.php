<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\AccountController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('generate_wallets', [WalletController::class, 'generateWallets']);

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::get('currencies', [CurrencyController::class, 'index']);

Route::middleware('auth:api')->group(function () {
    Route::get('profile', [AccountController::class, 'index']);
    Route::get('deposit_address/{accountId}', [
        AccountController::class,
        'getDepositAddress',
    ]);
    Route::get('generate_private_key/{accountId}', [
        AccountController::class,
        'createPrivateKey',
    ]);
});
