<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DispatchClinicReminders;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Context;

class DispatchRemindersController extends Controller
{
    public function __invoke(DispatchClinicReminders $dispatcher): JsonResponse
    {
        abort_unless(app()->environment(['local', 'testing']), 404);

        $count = $dispatcher->handle(Context::get('request_id'));

        return response()->json(['dispatched_jobs' => $count], 202);
    }
}
