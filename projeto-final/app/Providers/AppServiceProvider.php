<?php

namespace App\Providers;

use App\Contracts\ReminderProvider;
use App\Services\LogReminderProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ReminderProvider::class, LogReminderProvider::class);
    }

    public function boot(): void
    {
        RateLimiter::for(
            'reminder-provider',
            fn (object $job) => Limit::perMinute(60)->by((string) $job->clinicId),
        );
    }
}
