<?php

declare(strict_types=1);

namespace App\Domain2\Presentation\Api;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/** @psalm-suppress UnusedClass */
final class TestAction
{
    #[Route('/example2', name: 'example2')]
    public function index(): Response
    {
        return new Response();
    }
}
