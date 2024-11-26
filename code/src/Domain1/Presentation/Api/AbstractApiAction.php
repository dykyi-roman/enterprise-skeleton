<?php

declare(strict_types=1);

namespace App\Domain1\Presentation\Api;

use OpenApi\Attributes as OA;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
#[OA\Info(version: "1.0.0", title: "Enterprise Skeleton API")]
abstract class AbstractApiAction
{
}
