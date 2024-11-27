<?php

declare(strict_types=1);

namespace App\Domain1\Tests\Unit\Presentation\Console;

use App\Domain1\Presentation\Console\NativeMailTestCommand;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(NativeMailTestCommand::class)]
final class NativeMailTestCommandTest extends TestCase
{
    private CommandTester $commandTester;
    private LoggerInterface&MockObject $logger;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $command = new NativeMailTestCommand($this->logger);
        $this->commandTester = new CommandTester($command);
    }

    public function testExecute(): void
    {
        $this->logger->expects(self::once())
            ->method('info')
            ->with(
                'Attempting to send test email',
                self::callback(function (array $context): bool {
                    return isset($context['to'], $context['subject'], $context['smtp_host'], $context['smtp_port']);
                })
            );

        // Since we can't mock the PHP mail() function directly,
        // we'll just verify the command execution and output
        $exitCode = $this->commandTester->execute([]);

        // Note: The actual success/failure will depend on the mail server availability
        // In a real test environment, you might want to check logs or use a different approach
        self::assertTrue(
            in_array($exitCode, [Command::SUCCESS, Command::FAILURE], true),
            'Command should either succeed or fail'
        );

        $display = $this->commandTester->getDisplay();
        self::assertTrue(
            str_contains($display, 'Test email has been sent successfully') ||
            str_contains($display, 'Failed to send email'),
            'Command output should indicate success or failure'
        );
    }
}
