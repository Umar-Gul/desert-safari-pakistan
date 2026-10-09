<?php

declare(strict_types=1);

namespace App\Policies;

/** Bookings contain customer data: editors have no access. */
class BookingPolicy extends BasePolicy
{
    protected array $viewRoles = ['super_admin', 'admin', 'support'];

    protected array $writeRoles = ['super_admin', 'admin', 'support'];
}