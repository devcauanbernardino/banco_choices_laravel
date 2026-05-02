<?php

use App\Http\Controllers\Api\GeoController;
use App\Http\Controllers\Api\PostalController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:public-api')->group(function () {
    Route::post('/checkout-geo', [GeoController::class, 'checkoutGeo']);
    Route::post('/postal-lookup', [PostalController::class, 'lookup']);
    Route::get('/viacep/{cep}', [PostalController::class, 'viacep']);
});
