<?php

use App\Providers\AppServiceProvider;

return [
    AppServiceProvider::class,
        App\Providers\AuthorizationServiceProvider::class,
         App\Providers\PublicSiteServiceProvider::class,
        App\Providers\MessagingServiceProvider::class,
];
