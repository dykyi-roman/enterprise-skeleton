<?php

declare(strict_types=1);

namespace App\Domain1\Tests\Unit\Presentation\Console;

use App\Domain1\Presentation\Console\LogTestCommand;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

final class LogTestCommandTest extends TestCase
{
    private LoggerInterface&MockObject $logger;
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $command = new LogTestCommand($this->logger);
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
                self::callback(function (array $context) {
                    return isset($context['exception'])
                        && 'Test exception' === $context['exception']
                        && isset($context['trace']);
                })
            );

        $exitCode = $this->commandTester->execute([]);

        self::assertEquals(Command::SUCCESS, $exitCode);
        self::assertStringContainsString('This is console output', $this->commandTester->getDisplay());
    }
}
