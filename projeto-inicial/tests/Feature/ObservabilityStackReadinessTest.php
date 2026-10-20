<?php

namespace Tests\Feature;

use App\Console\Commands\DispatchReminders;
use App\Contracts\ReminderProvider;
use App\Http\Controllers\Api\ClinicScheduleController;
use App\Http\Controllers\Api\DispatchRemindersController;
use App\Http\Controllers\Api\SlowClinicScheduleController;
use App\Http\Controllers\Api\WebhookDeliveryController;
use App\Http\Controllers\ObservabilityDemoController;
use App\Http\Middleware\CorrelateRequest;
use App\Jobs\SendClinicReminders;
use App\Models\ReminderDelivery;
use App\Services\DispatchClinicReminders;
use App\Services\LogReminderProvider;
use Carbon\CarbonImmutable;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Laravel\Pulse\Pulse;
use Laravel\Telescope\Telescope;
use Tests\TestCase;

class ObservabilityStackReadinessTest extends TestCase
{
    use RefreshDatabase;

    public function test_observability_stack_and_domain_are_ready(): void
    {
        $this->assertTrue(class_exists(Telescope::class));
        $this->assertTrue(class_exists(Pulse::class));
        $this->assertTrue(Schema::hasTable('clinics'));
        $this->assertTrue(Schema::hasTable('patients'));
        $this->assertTrue(Schema::hasTable('appointments'));
        $this->assertTrue(Schema::hasTable('jobs'));
        $this->assertFalse(Schema::hasTable('reminder_deliveries'));
    }

    public function test_lesson_artifacts_exist_without_the_final_implementation(): void
    {
        $classes = [
            CorrelateRequest::class,
            ClinicScheduleController::class,
            DispatchRemindersController::class,
            SlowClinicScheduleController::class,
            WebhookDeliveryController::class,
            ObservabilityDemoController::class,
            SendClinicReminders::class,
            ReminderDelivery::class,
            DispatchClinicReminders::class,
            LogReminderProvider::class,
            DispatchReminders::class,
        ];

        foreach ($classes as $class) {
            $this->assertTrue(class_exists($class));
        }

        $this->assertTrue(interface_exists(ReminderProvider::class));
        $this->assertFileExists(database_path('migrations/2026_10_19_000001_create_reminder_deliveries_table.php'));
        $this->assertFileExists(resource_path('js/observability-demo.js'));
        $this->assertFileExists(resource_path('views/observability-demo.blade.php'));
    }

    public function test_the_laboratory_shell_and_pulse_collection_are_ready(): void
    {
        $this->get('/demo/observability')
            ->assertOk()
            ->assertSee('Correlação de requests')
            ->assertSee('N+1 e eager loading')
            ->assertSee('Log estruturado')
            ->assertSee('Jobs por clínica');

        $commands = collect(app(Schedule::class)->events())->pluck('command');

        $this->assertTrue($commands->contains(fn (?string $command): bool => str_contains($command ?? '', 'pulse:check')));
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
