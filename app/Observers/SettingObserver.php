<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Setting;

class SettingObserver
{
    public function saved(Setting $setting): void
    {
        Setting::flushCache();
    }

    public function deleted(Setting $setting): void
    {
        Setting::flushCache();
    }
}