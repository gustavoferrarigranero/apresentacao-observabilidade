<?php

namespace App\Contracts;

use App\Models\Appointment;

interface ReminderProvider
{
    public function send(Appointment $appointment, string $requestId): void;
}
