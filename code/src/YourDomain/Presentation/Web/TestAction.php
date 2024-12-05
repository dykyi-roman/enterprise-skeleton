<?php

declare(strict_types=1);

namespace App\CoreDomain\Presentation\Web;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class TestAction
{
    #[Route('/test', name: 'web_test', methods: ['GET'])]
    public function __invoke(): Response
    {
        return new Response('Test');
    }
}
