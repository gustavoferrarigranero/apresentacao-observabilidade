<?php

namespace App\Console\Commands;

use App\Services\DispatchClinicReminders as Dispatcher;
use Illuminate\Console\Command;

class DispatchReminders extends Command
{
    protected $signature = 'reminders:dispatch {--request-id=}';

    protected $description = 'Dispatch reminder jobs grouped by clinic';

    public function handle(Dispatcher $dispatcher): int
    {
        $count = $dispatcher->handle($this->option('request-id'));
        $this->components->info("{$count} clinic reminder jobs dispatched.");

        return self::SUCCESS;
    }
}
