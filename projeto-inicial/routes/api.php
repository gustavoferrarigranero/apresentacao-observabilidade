<?php

use App\Http\Controllers\Api\SlowClinicScheduleController;
use App\Http\Middleware\CorrelateRequest;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => ['status' => 'ok']);

Route::middleware(CorrelateRequest::class)->group(function () {

    Route::get('/test-request-id', function () {
        return response()->json(['message' => 'Test route is working']);
    });

    Route::get('/clinics/schedule/slow', SlowClinicScheduleController::class);

});