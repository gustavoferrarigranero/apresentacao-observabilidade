<?php

use App\Http\Controllers\ObservabilityDemoController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/demo/observability/{clinic?}', ObservabilityDemoController::class);
