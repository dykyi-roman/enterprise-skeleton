<?php

declare(strict_types=1);

namespace App\Domain1\Tests\Unit\Presentation\Api;

use App\Domain1\Presentation\Api\HelloAction;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;

final class HelloActionTest extends TestCase
{
    private HelloAction $action;

    protected function setUp(): void
    {
        $this->action = new HelloAction();
    }

    public function testInvoke(): void
    {
        $response = $this->action->__invoke();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals('"Test"', $response->getContent());
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
    }
}
