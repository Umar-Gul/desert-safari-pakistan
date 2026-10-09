<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Enums\EnquiryStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\StoreEnquiryRequest;
use App\Models\Enquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function show(): View
    {
        return view('frontend.pages.static.contact');
    }

    public function store(StoreEnquiryRequest $request): RedirectResponse
    {
        Enquiry::create([
            ...Arr::only($request->validated(), ['name', 'email', 'phone', 'subject', 'message']),
            'status' => EnquiryStatus::New,
            'ip' => $request->ip(),
        ]);

        return redirect()->route('contact')->with('status', 'Thank you! We have received your message and will reply soon.');
    }
}