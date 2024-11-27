<?php

declare(strict_types=1);

namespace App\Domain1\Tests\Unit\Presentation\Api;

use App\Domain1\Presentation\Api\HelloAction;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @internal
 */
#[CoversClass(HelloAction::class)]
final class HelloActionTest extends TestCase
{
    private HelloAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new HelloAction();
    }

    public function testInvoke(): void
    {
        $response = $this->action->__invoke();

        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertEquals('"Test"', $response->getContent());
        self::assertEquals(200, $response->getStatusCode());
        self::assertEquals('application/json', $response->headers->get('Content-Type'));
    }
}
