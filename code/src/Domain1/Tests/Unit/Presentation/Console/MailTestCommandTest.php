<?php

declare(strict_types=1);

namespace App\Domain1\Tests\Unit\Presentation\Console;

use App\Domain1\Presentation\Console\MailTestCommand;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[CoversClass(MailTestCommand::class)]
final class MailTestCommandTest extends TestCase
{
    private MailerInterface&MockObject $mailer;
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $this->mailer = $this->createMock(MailerInterface::class);
        $command = new MailTestCommand($this->mailer);
        $this->commandTester = new CommandTester($command);
    }

    public function testExecute(): void
    {
        $this->mailer->expects(self::once())
            ->method('send')
            ->with(self::callback(function (Email $email): bool {
                return $email->getFrom()[0]->getAddress() === 'test@example.com'
                    && $email->getTo()[0]->getAddress() === 'recipient@example.com'
                    && $email->getSubject() === 'Test Email from Enterprise Skeleton'
                    && str_contains($email->getTextBody(), 'This is a test email')
                    && str_contains($email->getHtmlBody(), '<p>This is a test email');
            }));

        $exitCode = $this->commandTester->execute([]);

        self::assertSame(Command::SUCCESS, $exitCode);
        self::assertStringContainsString(
            'Test email has been sent successfully!',
            $this->commandTester->getDisplay()
        );
    }

    public function testExecuteWithMailerError(): void
    {
        $errorMessage = 'Failed to connect to SMTP server';
        
        $this->mailer->expects(self::once())
            ->method('send')
            ->willThrowException(new \Exception($errorMessage));

        $exitCode = $this->commandTester->execute([]);

        self::assertSame(Command::FAILURE, $exitCode);
        self::assertStringContainsString(
            'Failed to send email: ' . $errorMessage,
            $this->commandTester->getDisplay()
        );
    }
}
