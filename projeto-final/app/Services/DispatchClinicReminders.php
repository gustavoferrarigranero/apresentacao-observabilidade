<?php

namespace App\Services;

use App\Jobs\SendClinicReminders;
use App\Models\Clinic;
use Illuminate\Support\Str;

class DispatchClinicReminders
{
    public function handle(?string $requestId = null): int
    {
        $requestId ??= (string) Str::uuid();
        $dispatched = 0;

        Clinic::query()
            ->select('id')
            ->chunkById(100, function ($clinics) use (&$dispatched, $requestId): void {
                foreach ($clinics as $clinic) {
                    SendClinicReminders::dispatch($clinic->id, $requestId);
                    $dispatched++;
                }
            });

        return $dispatched;
    }
}
