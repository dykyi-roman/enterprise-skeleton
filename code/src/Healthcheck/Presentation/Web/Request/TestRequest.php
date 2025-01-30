<?php

declare(strict_types=1);

namespace App\Healthcheck\Presentation\Web\Request;

use Symfony\Component\Validator\Constraints as Assert;

final class TestRequest
{
    /** @psalm-suppress PossiblyUnusedMethod */
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(min: 2, max: 5)]
        public string $lang = 'en',
    ) {
    }
}
