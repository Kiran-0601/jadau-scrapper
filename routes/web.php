<?php

use App\Http\Controllers\Controller;
use App\Http\Controllers\LiveRateController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::view('/live-rate', 'welcome'); // Blade page

Route::get('/api/live-rates', [LiveRateController::class, 'showLiveRates'])->name('live.rates');
