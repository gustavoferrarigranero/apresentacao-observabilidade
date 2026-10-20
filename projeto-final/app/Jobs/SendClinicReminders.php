<?php

namespace App\Jobs;

use App\Contracts\ReminderProvider;
use App\Models\Appointment;
use App\Models\ReminderDelivery;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Support\Facades\Context;

class SendClinicReminders implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 30;

    public function __construct(
        public int $clinicId,
        public string $requestId,
    ) {
        $this->onQueue('reminders');
    }

    public function middleware(): array
    {
        return [new RateLimited('reminder-provider')];
    }

    public function handle(ReminderProvider $provider): void
    {
        Context::add('request_id', $this->requestId);

        Appointment::query()
            ->where('clinic_id', $this->clinicId)
            ->where('status', 'scheduled')
            ->whereBetween('starts_at', [now(), now()->addDay()])
            ->eachById(function (Appointment $appointment) use ($provider): void {
                $delivery = ReminderDelivery::firstOrCreate(
                    ['appointment_id' => $appointment->id, 'provider' => 'log'],
                    ['request_id' => $this->requestId, 'status' => 'pending'],
                );

                if ($delivery->status === 'sent') {
                    return;
                }

                $provider->send($appointment, $this->requestId);
                $delivery->update(['status' => 'sent', 'sent_at' => now()]);
            });
    }
}
