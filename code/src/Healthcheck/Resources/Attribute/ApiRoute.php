<?php

declare(strict_types=1);

namespace App\Healthcheck\Resources\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
class ApiRoute extends Route
{
    public function __construct(
        string $path,
        string|array $methods = ['GET'],
        ?string $name = null,
        array $middleware = [],
    ) {
        $middleware = array_merge(['api'], $middleware);
        parent::__construct($path, $methods, $name, $middleware);
    }
}
