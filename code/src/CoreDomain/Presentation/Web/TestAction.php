<?php

declare(strict_types=1);

namespace App\CoreDomain\Presentation\Web;

use App\CoreDomain\Presentation\Web\Request\TestActionRequest;
use App\CoreDomain\Resources\Attribute\WebRoute;
use Symfony\Component\HttpFoundation\Response;

#[WebRoute('/test', ['GET'], 'web.test')]
final class TestAction
{
    public function __invoke(
        TestActionRequest $request,
    ): Response {
        return new Response('Test');
    }
}
