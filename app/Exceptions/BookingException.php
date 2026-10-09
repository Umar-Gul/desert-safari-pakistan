<?php

declare(strict_types=1);

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;

/** Expected, user-facing booking failure (full date, bad coupon, ...). */
class BookingException extends RuntimeException
{
    /** Not a bug: keep it out of the error log. */
    public function report(): bool
    {
        return false;
    }

    public function render(Request $request): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $this->getMessage()], 422);
        }

        return back()->withInput()->withErrors(['booking' => $this->getMessage()]);
    }
}