<?php

declare(strict_types=1);

namespace App\View\Composers;

use App\Models\Setting;
use Illuminate\Support\Fluent;
use Illuminate\View\View;

class SettingsComposer
{
    public function compose(View $view): void
    {
        // $site->site_name, $site->phone, $site->get('key', 'default') ...
        $view->with('site', new Fluent(Setting::map()));
    }
}