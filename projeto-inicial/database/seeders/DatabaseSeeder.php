<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Patient;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Clinic::factory()->count(2)->create()->each(function (Clinic $clinic) {
            $patients = Patient::factory()->count(8)->for($clinic)->create();

            $patients->each(function (Patient $patient) use ($clinic) {
                Appointment::factory()->count(3)->for($clinic)->for($patient)->create();
            });

            Appointment::factory()->for($clinic)->for($patients->first())->create([
                'starts_at' => now()->addHours(2),
                'status' => 'scheduled',
                'notes' => 'Observability lesson reminder',
            ]);
        });
    }
}
