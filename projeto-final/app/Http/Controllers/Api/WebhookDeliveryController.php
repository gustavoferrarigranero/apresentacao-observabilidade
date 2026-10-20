<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Log;

class WebhookDeliveryController extends Controller
{
    public function __invoke(Request $request, Clinic $clinic): JsonResponse
    {
        $delivery = $request->validate([
            'delivery_id' => ['required', 'string', 'max:100'],
            'provider' => ['required', 'string', 'max:100'],
            'status_code' => ['required', 'integer', 'min:400', 'max:599'],
            'duration_ms' => ['required', 'integer', 'min:0'],
        ]);

        Log::warning('webhook_delivery_failed', [
            'request_id' => Context::get('request_id'),
            'clinic_id' => $clinic->id,
            ...$delivery,
        ]);

        return response()->json(['accepted' => true], 202);
    }
}
