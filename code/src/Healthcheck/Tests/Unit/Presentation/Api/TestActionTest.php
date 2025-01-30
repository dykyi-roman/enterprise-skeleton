<?php

declare(strict_types=1);

namespace App\Healthcheck\Tests\Unit\Presentation\Api;

use App\Healthcheck\Presentation\Api\Response\TestJsonResponder;
use App\Healthcheck\Presentation\Api\TestAction;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

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
        $response = $this->action->__invoke(new TestJsonResponder());

        self::assertInstanceOf(TestJsonResponder::class, $response);
        self::assertArrayHasKey('success', $response->payload());
        self::assertArrayHasKey('message', $response->payload());
        self::assertEquals(200, $response->statusCode());
        self::assertArrayHasKey('Content-Type', $response->headers());
    }
}
