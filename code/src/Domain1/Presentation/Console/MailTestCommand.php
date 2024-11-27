<?php

declare(strict_types=1);

namespace App\Domain1\Presentation\Console;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:test:native-mail',
    description: 'Send a test email using native PHP mail with SMTP'
)]
final class MailTestCommand extends Command
{
    private const SMTP_HOST = 'es-mailhog';
    private const SMTP_PORT = 1025;

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $to = 'recipient@example.com';
            $subject = 'Test Native Email from Enterprise Skeleton';

            $headers = [
                'MIME-Version: 1.0',
                'Content-Type: text/html; charset=UTF-8',
                'From: test@example.com',
                'Reply-To: test@example.com',
                'X-Mailer: PHP/' . PHP_VERSION
            ];

            $message = '
                <html>
                <head>
                    <title>Test Email</title>
                </head>
                <body>
                    <h1>Test Email</h1>
                    <p>This is a test email sent from the Enterprise Skeleton application using native PHP mail.</p>
                </body>
                </html>
            ';

            // Configure SMTP connection
            ini_set('SMTP', self::SMTP_HOST);
            ini_set('smtp_port', (string) self::SMTP_PORT);

            if (mail($to, $subject, $message, implode("\r\n", $headers))) {
                $output->writeln('<info>Tdest email has been sent successfully using native PHP mail!</info>');

                return Command::SUCCESS;
            }

            $error = error_get_last();
            throw new \RuntimeException($error ? $error['message'] : 'Failed to send email');
        } catch (\Throwable $exception) {
            $output->writeln('<error>Failed to send email: ' . $exception->getMessage() . '</error>');

            return Command::FAILURE;
        }
    }
}
