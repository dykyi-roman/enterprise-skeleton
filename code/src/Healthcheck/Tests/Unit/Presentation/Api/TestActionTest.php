<?php

declare(strict_types=1);

namespace App\Healthcheck\Tests\Unit\Presentation\Api;

use App\Healthcheck\Presentation\Api\Resonse\TestJsonResponse;
use App\Healthcheck\Presentation\Api\TestAction;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;

#[CoversClass(TestAction::class)]
final class TestActionTest extends TestCase
{
    private TestAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new TestAction();
    }

    public function testInvoke(): void
    {
        $response = $this->action->__invoke(new TestJsonResponse());

        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertEquals('"Test"', $response->getContent());
        self::assertEquals(200, $response->getStatusCode());
        self::assertEquals('application/json', $response->headers->get('Content-Type'));
    }
}
