<?php

declare(strict_types=1);

namespace App\Healthcheck\Tests\Unit\Healthcheck\Presentation\Console;

use App\Healthcheck\Presentation\Console\LogHealthcheckCommand;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(LogHealthcheckCommand::class)]
final class LogHealthcheckCommandTest extends TestCase
{
    private LoggerInterface&MockObject $logger;
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $command = new LogHealthcheckCommand($this->logger);
        $this->commandTester = new CommandTester($command);
    }

    public function testExecute(): void
    {
        $this->logger->expects(self::once())
            ->method('info')
            ->with('This is info log message');

        $this->logger->expects(self::once())
            ->method('error')
            ->with('This is error message');

        $this->logger->expects(self::once())
            ->method('critical')
            ->with(
                'Critical error occurred',
                self::callback(static function (array $context) {
                    return isset($context['exception'], $context['trace']) && 'Test exception' === $context['exception'];
                })
            );

        $exitCode = $this->commandTester->execute([]);

        self::assertEquals(Command::SUCCESS, $exitCode);
        self::assertStringContainsString('This is console output', $this->commandTester->getDisplay());
    }
}
