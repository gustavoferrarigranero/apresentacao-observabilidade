<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use Illuminate\Contracts\View\View;

class ObservabilityDemoController extends Controller
{
    public function __invoke(?Clinic $clinic = null): View
    {
        abort_unless(app()->environment(['local', 'testing']), 404);

        return view('observability-demo', [
            'clinic' => $clinic ?? Clinic::query()->firstOrFail(),
        ]);
    }
}
