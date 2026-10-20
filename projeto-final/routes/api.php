<?php

use App\Http\Controllers\Api\ClinicScheduleController;
use App\Http\Controllers\Api\DispatchRemindersController;
use App\Http\Controllers\Api\SlowClinicScheduleController;
use App\Http\Controllers\Api\WebhookDeliveryController;
use App\Http\Middleware\CorrelateRequest;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => ['status' => 'ok']);
Route::middleware(CorrelateRequest::class)->group(function () {
    Route::get('/clinics/{clinic}/schedule/slow', SlowClinicScheduleController::class);
    Route::get('/clinics/{clinic}/schedule', ClinicScheduleController::class);
    Route::post('/clinics/{clinic}/webhook-failures', WebhookDeliveryController::class);
    Route::post('/observability/reminders/dispatch', DispatchRemindersController::class);
});
