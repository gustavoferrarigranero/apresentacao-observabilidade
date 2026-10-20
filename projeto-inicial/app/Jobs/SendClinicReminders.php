<?php

namespace App\Jobs;

use App\Contracts\ReminderProvider;
use App\Models\Appointment;
use App\Models\ReminderDelivery;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Log;

class SendClinicReminders implements ShouldQueue {
    use Queueable;

    public int $tries = 3;
    public int $timeout = 30;

    public function __construct(
        public int $clinicId,
        public string $requestId,
    ) {
        $this->onQueue('reminders');
    }

    public function handle(): void
    {
        Context::add('request_id', $this->requestId);

        Log::info('sending_reminders_clinic', [
            'clinic_id' => $this->clinicId, 'request_id' => $this->requestId
        ]);
    }
}
