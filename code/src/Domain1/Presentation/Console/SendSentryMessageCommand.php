<?php

declare(strict_types=1);

namespace App\Domain1\Presentation\Console;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Sentry\State\Scope;

#[AsCommand(
    name: 'app:sentry:test',
    description: 'Send test message to Sentry',
)]
class SendSentryMessageCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addOption(
                'message',
                'm',
                InputOption::VALUE_OPTIONAL,
                'Message to send',
                'Test message from console command'
            );
    }
//http://6b0da15ce55e4096ac78bc1dc06f89db@127.0.0.1:9001/1
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $message = $input->getOption('message');

        try {
            \Sentry\init([
                'dsn' => 'http://6b0da15ce55e4096ac78bc1dc06f89db@es-sentry-web:9000/1',
            ]);
            throw new \Exception($message);
        } catch (\Exception $exception) {
             \Sentry\configureScope(function (Scope $scope) use ($message): void {
                $scope->setExtra('custom_message', $message);
            });

            $id = \Sentry\captureException($exception);
            $output->writeln((string) $id);
            
            // Ensure the event is sent before the script ends
            if ($client = \Sentry\SentrySdk::getCurrentHub()->getClient()) {
                $client->flush(2000); // wait up to 2 seconds
            }
        }

        return Command::SUCCESS;
    }
}
