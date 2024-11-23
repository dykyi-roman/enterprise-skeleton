<?php

declare(strict_types=1);

namespace App\Domain1\Presentation\Api;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/** @psalm-suppress UnusedClass */
final class TestAction
{
    #[Route('/example', name: 'example')]
    public function index(\Sentry\State\HubInterface $hub): Response
    {
        return new Response();
    }
}
