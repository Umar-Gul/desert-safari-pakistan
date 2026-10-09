<?php

declare(strict_types=1);

namespace App\Policies;

/** Payments, coupons, availability and prices: admins only. */
class FinancePolicy extends BasePolicy
{
    protected array $viewRoles = ['super_admin', 'admin'];

    protected array $writeRoles = ['super_admin', 'admin'];
}