<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\EnquiryStatus;
use App\Http\Controllers\Controller;
use App\Models\Enquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EnquiryController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Enquiry::class);

        $filter = (string) $request->input('status', 'new');
        $status = EnquiryStatus::tryFrom($filter);

        return view('admin.enquiries.index', [
            'enquiries' => Enquiry::query()
                ->when($status, fn ($q) => $q->where('status', $status->value))
                ->latest('id')
                ->paginate(15)
                ->withQueryString(),
            'filter' => $filter,
            'counts' => Enquiry::query()->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status'),
        ]);
    }

    public function status(Request $request, Enquiry $enquiry): RedirectResponse
    {
        Gate::authorize('update', $enquiry);

        $data = $request->validate(['status' => ['required', Rule::enum(EnquiryStatus::class)]]);

        $enquiry->update(['status' => EnquiryStatus::from($data['status'])]);

        return back()->with('success', 'Enquiry marked as '.strtolower($enquiry->status->label()).'.');
    }

    public function destroy(Enquiry $enquiry): RedirectResponse
    {
        Gate::authorize('delete', $enquiry);

        $enquiry->delete();

        return back()->with('success', 'Enquiry deleted.');
    }
}