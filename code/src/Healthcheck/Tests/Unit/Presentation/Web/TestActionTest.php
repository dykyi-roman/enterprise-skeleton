<?php

declare(strict_types=1);

namespace Healthcheck\Tests\Unit\Presentation\Web;

use Healthcheck\Presentation\Web\Request\TestRequest;
use Healthcheck\Presentation\Web\Resonse\TestHtmlResponse;
use Healthcheck\Presentation\Web\TestAction;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TestAction::class)]
final class TestActionTest extends TestCase
{
    private TestAction $action;

    protected function setUp(): void
    {
        $this->action = new TestAction();
    }

    public function testInvoke(): void
    {
        $response = $this->action->__invoke(
            new TestRequest(),
            new TestHtmlResponse(),
        );

        self::assertInstanceOf(TestHtmlResponse::class, $response);
        self::assertArrayHasKey('lang', $response->payload());
        self::assertEquals(200, $response->statusCode());
    }
}
