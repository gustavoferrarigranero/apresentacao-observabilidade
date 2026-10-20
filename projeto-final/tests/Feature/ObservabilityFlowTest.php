<?php

namespace Tests\Feature;

use App\Contracts\ReminderProvider;
use App\Jobs\SendClinicReminders;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Patient;
use App\Models\ReminderDelivery;
use App\Providers\TelescopeServiceProvider;
use App\Services\DispatchClinicReminders;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use RuntimeException;
use Tests\TestCase;

class ObservabilityFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_request_id_is_propagated_and_contextual_log_is_written(): void
    {
        Log::spy();
        $clinic = Clinic::factory()->create();
        $patient = Patient::factory()->for($clinic)->create();
        Appointment::factory()->for($clinic)->for($patient)->create();

        $this->withHeader('X-Request-ID', 'lesson-request-123')
            ->getJson('/api/clinics/'.$clinic->id.'/schedule')
            ->assertOk()
            ->assertHeader('X-Request-ID', 'lesson-request-123');

        Log::shouldHaveReceived('info')->with('clinic_schedule_loaded', [
            'request_id' => 'lesson-request-123',
            'clinic_id' => $clinic->id,
            'appointments_count' => 1,
        ]);
    }

    public function test_optimized_schedule_removes_the_n_plus_one_queries(): void
    {
        $clinic = Clinic::factory()->create();
        Patient::factory()->count(10)->for($clinic)->create()->each(
            fn (Patient $patient) => Appointment::factory()->for($clinic)->for($patient)->create(),
        );

        DB::enableQueryLog();
        $this->getJson('/api/clinics/'.$clinic->id.'/schedule/slow')->assertOk();
        $slowQueryCount = count(DB::getQueryLog());

        DB::flushQueryLog();
        $this->getJson('/api/clinics/'.$clinic->id.'/schedule')->assertOk();
        $optimizedQueryCount = count(DB::getQueryLog());

        $this->assertGreaterThan($optimizedQueryCount, $slowQueryCount);
        $this->assertLessThanOrEqual(3, $optimizedQueryCount);
    }

    public function test_webhook_failure_writes_a_structured_log_without_payload_or_credentials(): void
    {
        Log::spy();
        $clinic = Clinic::factory()->create();

        $this->withHeader('X-Request-ID', 'webhook-request-456')
            ->postJson('/api/clinics/'.$clinic->id.'/webhook-failures', [
                'delivery_id' => 'delivery-10',
                'provider' => 'clinic-partner',
                'status_code' => 504,
                'duration_ms' => 4800,
            ])
            ->assertAccepted();

        Log::shouldHaveReceived('warning')->with('webhook_delivery_failed', [
            'request_id' => 'webhook-request-456',
            'clinic_id' => $clinic->id,
            'delivery_id' => 'delivery-10',
            'provider' => 'clinic-partner',
            'status_code' => 504,
            'duration_ms' => 4800,
        ]);
    }

    public function test_dispatcher_creates_one_reminder_job_per_clinic_on_the_expected_queue(): void
    {
        Queue::fake();
        Clinic::factory()->count(205)->create();

        $count = app(DispatchClinicReminders::class)->handle('00000000-0000-4000-8000-000000000001');

        $this->assertSame(205, $count);
        Queue::assertPushed(SendClinicReminders::class, 205);
        Queue::assertPushedOn('reminders', SendClinicReminders::class);
    }

    public function test_reminder_job_is_idempotent_and_keeps_correlation_context(): void
    {
        Log::spy();
        $clinic = Clinic::factory()->create();
        $patient = Patient::factory()->for($clinic)->create();
        $appointment = Appointment::factory()->for($clinic)->for($patient)->create([
            'starts_at' => now()->addHour(),
            'status' => 'scheduled',
        ]);
        $provider = new class implements ReminderProvider
        {
            public int $calls = 0;

            public function send(Appointment $appointment, string $requestId): void
            {
                $this->calls++;
            }
        };
        $job = new SendClinicReminders($clinic->id, '00000000-0000-4000-8000-000000000002');

        $job->handle($provider);
        $job->handle($provider);

        $this->assertSame(1, $provider->calls);
        $this->assertDatabaseCount('reminder_deliveries', 1);
        $this->assertSame('sent', ReminderDelivery::first()->status);
        $this->assertSame(3, $job->tries);
        $this->assertSame(30, $job->timeout);
        $this->assertCount(1, $job->middleware());
        $this->assertSame($appointment->id, ReminderDelivery::first()->appointment_id);
    }

    public function test_failed_provider_call_can_be_retried_without_losing_the_reminder(): void
    {
        $clinic = Clinic::factory()->create();
        $patient = Patient::factory()->for($clinic)->create();
        Appointment::factory()->for($clinic)->for($patient)->create([
            'starts_at' => now()->addHour(),
            'status' => 'scheduled',
        ]);
        $provider = new class implements ReminderProvider
        {
            public int $calls = 0;

            public function send(Appointment $appointment, string $requestId): void
            {
                $this->calls++;

                if ($this->calls === 1) {
                    throw new RuntimeException('Provider temporarily unavailable');
                }
            }
        };
        $job = new SendClinicReminders($clinic->id, 'retry-request');

        try {
            $job->handle($provider);
        } catch (RuntimeException) {
        }

        $this->assertSame('pending', ReminderDelivery::first()->status);

        $job->handle($provider);

        $this->assertSame(2, $provider->calls);
        $this->assertSame('sent', ReminderDelivery::first()->status);
    }

    public function test_telescope_and_pulse_are_configured_for_the_application(): void
    {
        $this->assertSame('telescope', config('telescope.path'));
        $this->assertSame('pulse', config('pulse.path'));
        $this->assertContains(TelescopeServiceProvider::class, require base_path('bootstrap/providers.php'));
    }

    public function test_pulse_values_can_be_restored_from_the_database_cache(): void
    {
        $value = [
            collect([(object) ['count' => 1]]),
            Carbon::now()->toImmutable(),
        ];

        Cache::store('database')->put('pulse-cache-roundtrip', $value, 60);

        $cached = Cache::store('database')->get('pulse-cache-roundtrip');

        $this->assertInstanceOf(Collection::class, $cached[0]);
        $this->assertInstanceOf(CarbonImmutable::class, $cached[1]);
        $this->assertInstanceOf(\stdClass::class, $cached[0]->first());
    }
}
