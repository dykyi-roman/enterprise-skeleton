<?php

declare(strict_types=1);

namespace App\Shared\Resources;

use App\Shared\Presentation\Responder\JsonResponder;
use Illuminate\Support\ServiceProvider;

final class ResponderServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(JsonResponder::class);
    }

    public function boot(): void
    {
        $this->app['router']->aliasMiddleware('json.response', JsonResponder::class);
        $this->app['router']->pushMiddlewareToGroup('api', 'json.response');
    }
}
