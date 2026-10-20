<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DispatchReminders extends Command
{
    protected $signature = 'reminders:dispatch {--request-id=}';

    protected $description = 'Dispatch reminder jobs grouped by clinic';

    public function handle(): int
    {
        return self::SUCCESS;
    }
}
