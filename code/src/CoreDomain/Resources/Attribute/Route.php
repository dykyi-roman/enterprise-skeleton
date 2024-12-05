<?php

declare(strict_types=1);

namespace App\CoreDomain\Resources\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
class Route
{
    public function __construct(
        public string $path,
        public string|array $methods = ['GET'],
        public ?string $name = null,
        public array $middleware = [],
    ) {
    }
}
