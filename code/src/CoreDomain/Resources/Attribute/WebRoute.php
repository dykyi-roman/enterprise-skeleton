<?php

declare(strict_types=1);

namespace App\CoreDomain\Resources\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
class WebRoute extends Route
{
    public function __construct(
        string $path,
        string|array $methods = ['GET'],
        ?string $name = null,
        array $middleware = [],
    ) {
        $middleware = array_merge(['web'], $middleware);
        parent::__construct($path, $methods, $name, $middleware);
    }
}
