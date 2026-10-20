<?php

namespace App\Services;

use App\Contracts\ReminderProvider;
use App\Models\Appointment;
use Illuminate\Support\Facades\Log;

class LogReminderProvider implements ReminderProvider
{
    public function send(Appointment $appointment, string $requestId): void
    {
        Log::info('appointment_reminder_sent', [
            'request_id' => $requestId,
            'clinic_id' => $appointment->clinic_id,
            'appointment_id' => $appointment->id,
            'provider' => 'log',
        ]);
    }
}
