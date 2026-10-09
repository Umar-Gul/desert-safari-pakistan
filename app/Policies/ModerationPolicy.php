<?php

declare(strict_types=1);

namespace App\Policies;

/** Reviews, questions, enquiries, subscribers. Support handles these day to day. */
class ModerationPolicy extends BasePolicy
{
    protected array $writeRoles = ['super_admin', 'admin', 'support'];

    protected array $deleteRoles = ['super_admin', 'admin', 'support'];
}