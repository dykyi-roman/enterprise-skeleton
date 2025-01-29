<?php

namespace App\Shared\Resources;

use Illuminate\Support\ServiceProvider;

class AppNamespaceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind('app.namespace', function () {
            return 'App\\';
        });
    }
}
