<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Clinic;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Log;

class ClinicScheduleController extends Controller
{
    public function __invoke(Clinic $clinic): JsonResponse
    {
        $appointments = Appointment::query()
            ->select(['id', 'clinic_id', 'patient_id', 'starts_at', 'status'])
            ->with('patient:id,name')
            ->whereBelongsTo($clinic)
            ->orderBy('starts_at')
            ->limit(100)
            ->get();

        Log::info('clinic_schedule_loaded', [
            'request_id' => Context::get('request_id'),
            'clinic_id' => $clinic->id,
            'appointments_count' => $appointments->count(),
        ]);

        return response()->json(['data' => $appointments]);
    }
}
