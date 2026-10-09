<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\PaymentMethod;
use App\Enums\PaymentTransactionStatus;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Payment::class);

        $method = PaymentMethod::tryFrom((string) $request->input('method'));
        $status = PaymentTransactionStatus::tryFrom((string) $request->input('status'));
        $term = trim((string) $request->input('q'));

        $payments = Payment::query()
            ->with('booking:id,reference,customer_name')
            ->when($method, fn ($q) => $q->where('method', $method->value))
            ->when($status, fn ($q) => $q->where('status', $status->value))
            ->when($term !== '', function ($q) use ($term) {
                $like = '%'.addcslashes($term, '%_\\').'%';

                $q->where(fn ($w) => $w
                    ->where('transaction_id', 'like', $like)
                    ->orWhereHas('booking', fn ($b) => $b->where('reference', 'like', $like)->orWhere('customer_name', 'like', $like)));
            })
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        $monthTotal = (float) Payment::query()
            ->where('status', PaymentTransactionStatus::Completed->value)
            ->whereBetween('paid_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('amount');

        return view('admin.payments.index', compact('payments', 'monthTotal'));
    }
}