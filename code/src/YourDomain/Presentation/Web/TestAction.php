<?php

declare(strict_types=1);

namespace App\YourDomain\Presentation\Web;

use App\Providers\Attributes\WebRoute;
use Symfony\Component\HttpFoundation\Response;

#[WebRoute('/test', ['GET'], 'web.test')]
final class TestAction
{
    public function __invoke(): Response
    {
        return new Response('Test');
    }
}
