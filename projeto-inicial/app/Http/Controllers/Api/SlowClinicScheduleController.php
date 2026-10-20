<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SendClinicReminders;
use App\Models\Appointment;
use App\Models\Clinic;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Context;

class SlowClinicScheduleController extends Controller {
    public function __invoke(): JsonResponse
    {
        $requestId = Context::get('request_id');

        $appointments = Appointment::query()
            ->with(['patient'])
            ->limit(100)
            ->get()
            ->map(function (Appointment $appointment) use ($requestId): array {
                SendClinicReminders::dispatch(
                    $appointment->clinic_id, $requestId
                );

                return [
                    'id' => $appointment->id,
                    'clinic_id' => $appointment->clinic_id,
                    'patient_name' => $appointment->patient->name,
                ];
            });

        return response()->json(['data' => $appointments]);
    }
}
