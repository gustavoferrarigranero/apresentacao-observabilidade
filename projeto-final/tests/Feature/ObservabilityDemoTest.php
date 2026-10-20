<?php

namespace Tests\Feature;

use App\Jobs\SendClinicReminders;
use App\Models\Clinic;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\TestCase;

class ObservabilityDemoTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_page_exposes_every_manual_flow(): void
    {
        Clinic::factory()->create();

        $this->get('/demo/observability')
            ->assertOk()
            ->assertSee('Executar agenda lenta')
            ->assertSee('Executar agenda otimizada')
            ->assertSee('Registrar falha de webhook')
            ->assertSee('Despachar jobs por clínica')
            ->assertSee('/telescope', false)
            ->assertSee('/pulse', false);
    }

    public function test_dispatch_endpoint_correlates_and_enqueues_one_job_per_clinic(): void
    {
        Queue::fake();
        Clinic::factory()->count(2)->create();

        $response = $this->postJson('/api/observability/reminders/dispatch');

        $response->assertAccepted()->assertJson(['dispatched_jobs' => 2]);
        $this->assertTrue(Str::isUuid($response->headers->get('X-Request-ID')));
        Queue::assertPushed(SendClinicReminders::class, 2);
        Queue::assertPushedOn('reminders', SendClinicReminders::class);
    }

    public function test_scheduler_collects_pulse_and_dispatches_reminders(): void
    {
        $commands = collect(app(Schedule::class)->events())->pluck('command');

        $this->assertTrue($commands->contains(fn (?string $command): bool => str_contains($command ?? '', 'pulse:check')));
        $this->assertTrue($commands->contains(fn (?string $command): bool => str_contains($command ?? '', 'reminders:dispatch')));
    }
}
