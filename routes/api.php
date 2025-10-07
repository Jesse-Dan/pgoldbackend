<?php

use App\Module\Rates\Controllers\RateController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Module\Authentication\Controllers\AuthController;

Route::group(['middleware' => ['api'], 'prefix' => 'auth'], function () {

    Route::post('register', [AuthController::class, 'register']);

    Route::post('login', [AuthController::class, 'login']);

    Route::post('send-otp', [AuthController::class, 'sendOtp']);

    Route::post('verify-otp', [AuthController::class, 'verifyOtp']);
});

Route::middleware('auth:sanctum')->group(function () {

    Route::post('logout', [AuthController::class, 'logout']);

    Route::get('user', function (Request $request) {
        return $request->user();
    });
});


Route::get('crypto-rates', [RateController::class, 'cryptoRates']);
Route::get('giftcard-rates', [RateController::class, 'giftcardRates']);