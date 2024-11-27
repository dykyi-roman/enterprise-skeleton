<?php

declare(strict_types=1);

namespace App\Domain1\Presentation\Console\Healthcheck;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:healthcheck:mail',
    description: 'Send a test email using native PHP mail with SMTP'
)]
final class MailHealthcheckCommand extends Command
{
    private const SMTP_SERVICES = [
        'mailhog' => [
            'host' => 'es-mailhog',
            'port' => 1025,
        ],
        'papercut' => [
            'host' => 'es-papercut',
            'port' => 25,
        ],
    ];

    protected function configure(): void
    {
        $this->addOption(
            'service',
            's',
            InputOption::VALUE_REQUIRED,
            'Mail service to use (mailhog or papercut)',
            'mailhog'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $service = strtolower((string) $input->getOption('service'));
            if (!isset(self::SMTP_SERVICES[$service])) {
                throw new \InvalidArgumentException(sprintf('Invalid service "%s". Available services: %s', $service, implode(', ', array_keys(self::SMTP_SERVICES))));
            }

            $smtp = self::SMTP_SERVICES[$service];
            $to = 'recipient@example.com';
            $subject = sprintf('Test Email from Enterprise Skeleton (%s)', $service);

            $headers = [
                'MIME-Version: 1.0',
                'Content-Type: text/html; charset=UTF-8',
                'From: test@example.com',
                'Reply-To: test@example.com',
                'X-Mailer: PHP/'.PHP_VERSION,
            ];

            $message = '
                <html>
                <head>
                    <title>Test Email</title>
                </head>
                <body>
                    <h1>Test Email</h1>
                    <p>This is a test email sent from the Enterprise Skeleton application using '.$service.'.</p>
                    <p>SMTP Host: '.$smtp['host'].'</p>
                    <p>SMTP Port: '.$smtp['port'].'</p>
                    <p>Time: '.date('Y-m-d H:i:s').'</p>
                </body>
                </html> 
            ';

            // Configure SMTP connection
            ini_set('SMTP', $smtp['host']);
            ini_set('smtp_port', (string) $smtp['port']);

            $output->writeln(sprintf('<info>Attempting to send email via %s (%s:%d)...</info>',
                $service,
                $smtp['host'],
                $smtp['port']
            ));

            if (mail($to, $subject, $message, implode("\r\n", $headers))) {
                $output->writeln(sprintf(
                    '<info>Test email has been sent successfully using %s (SMTP: %s:%d)!</info>',
                    $service,
                    $smtp['host'],
                    $smtp['port']
                ));

                // Display last few lines of msmtp log
                if (file_exists('/var/log/msmtp.log')) {
                    $logContent = shell_exec('tail -n 5 /var/log/msmtp.log');
                    if (null !== $logContent) {
                        $output->writeln('<info>Last msmtp log entries:</info>');
                        $output->writeln((string) $logContent);
                    }
                }

                return Command::SUCCESS;
            }

            $error = error_get_last();
            throw new \RuntimeException($error ? $error['message'] : 'Failed to send email');
        } catch (\Throwable $exception) {
            $output->writeln('<error>Failed to send email: '.$exception->getMessage().'</error>');

            // Display PHP mail configuration
            $output->writeln('<info>Current PHP mail configuration:</info>');
            $output->writeln('SMTP: '.ini_get('SMTP'));
            $output->writeln('smtp_port: '.ini_get('smtp_port'));

            // Display last few lines of msmtp log if available
            if (file_exists('/var/log/msmtp.log')) {
                $logContent = shell_exec('tail -n 10 /var/log/msmtp.log');
                if (null !== $logContent) {
                    $output->writeln('<info>Last msmtp log entries:</info>');
                    $output->writeln((string) $logContent);
                }
            }

            return Command::FAILURE;
        }
    }
}
