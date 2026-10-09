<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\EventPackage;
use Illuminate\View\View;

class EventController extends Controller
{
    public function index(): View
    {
        return view('frontend.events.index', [
            'packages' => EventPackage::query()->active()->with('media')->ordered()->get(),
        ]);
    }
}