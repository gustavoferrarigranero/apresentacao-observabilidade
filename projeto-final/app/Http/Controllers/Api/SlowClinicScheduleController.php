<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Clinic;
use Illuminate\Http\JsonResponse;

class SlowClinicScheduleController extends Controller
{
    public function __invoke(Clinic $clinic): JsonResponse
    {
        abort_unless(app()->environment(['local', 'testing']), 404);

        $appointments = Appointment::query()
            ->whereBelongsTo($clinic)
            ->orderBy('starts_at')
            ->limit(100)
            ->get()
            ->map(function (Appointment $appointment): array {
                usleep(40_000);

                return [
                    'id' => $appointment->id,
                    'clinic_id' => $appointment->clinic_id,
                    'patient_id' => $appointment->patient_id,
                    'starts_at' => $appointment->starts_at,
                    'status' => $appointment->status,
                    'patient' => [
                        'id' => $appointment->patient->id,
                        'name' => $appointment->patient->name,
                    ],
                ];
            });

        return response()->json(['data' => $appointments]);
    }
}
