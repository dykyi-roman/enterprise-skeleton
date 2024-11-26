<?php

declare(strict_types=1);

namespace App\Domain1\Tests\Unit\Presentation\Web;

use App\Domain1\Presentation\Web\TestAction;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

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

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('Test', $response->getContent());
        $this->assertEquals(200, $response->getStatusCode());
    }
}
