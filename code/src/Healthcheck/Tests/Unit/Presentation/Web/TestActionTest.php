<?php

declare(strict_types=1);

namespace App\Healthcheck\Tests\Unit\Presentation\Web;

use App\Healthcheck\Presentation\Web\Request\TestRequest;
use App\Healthcheck\Presentation\Web\Resonse\TestHtmlResponse;
use App\Healthcheck\Presentation\Web\TestAction;
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
        self::assertEquals('Test', $response->payload());
        self::assertEquals(200, $response->statusCode());
    }
}
