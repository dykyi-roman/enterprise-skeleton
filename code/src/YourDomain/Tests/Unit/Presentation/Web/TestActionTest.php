<?php

declare(strict_types=1);

namespace App\YourDomain\Tests\Unit\Presentation\Web;

use App\YourDomain\Presentation\Web\TestAction;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

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
        $response = $this->action->__invoke();

        self::assertInstanceOf(Response::class, $response);
        self::assertEquals('Test', $response->getContent());
        self::assertEquals(200, $response->getStatusCode());
    }
}
