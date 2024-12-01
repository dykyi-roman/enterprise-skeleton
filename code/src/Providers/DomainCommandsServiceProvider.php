<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use ReflectionClass;
use Symfony\Component\Console\Attribute\AsCommand;

class DomainCommandsServiceProvider extends ServiceProvider
{
    private const array DOMAINS = [
        'Healthcheck',
        'YourDomain',
    ];

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->registerDomainCommands();
    }

    /**
     * Register commands from all domain modules
     */
    private function registerDomainCommands(): void
    {
        $commands = [];

        foreach (self::DOMAINS as $domain) {
            $consolePath = base_path("src/{$domain}/Presentation/Console");
            if (!is_dir($consolePath)) {
                continue;
            }

            $commands = array_merge($commands, $this->findCommands($consolePath));
        }

        if (!empty($commands)) {
            $this->commands($commands);
        }
    }

    /**
     * Find all command classes in the specified path
     */
    private function findCommands(string $path): array
    {
        $commands = [];

        $directory = new RecursiveDirectoryIterator($path);
        $iterator = new RecursiveIteratorIterator($directory);
        $files = new RegexIterator($iterator, '/^.+Command\.php$/i', RegexIterator::GET_MATCH);

        foreach ($files as $file) {
            $filePath = $file[0];
            $className = $this->getClassNameFromFile($filePath);

            if ($className && class_exists($className)) {
                $reflection = new ReflectionClass($className);
                $attributes = $reflection->getAttributes(AsCommand::class);

                if (!empty($attributes)) {
                    $commands[] = $className;
                }
            }
        }

        return $commands;
    }

    /**
     * Get fully qualified class name from file path
     */
    private function getClassNameFromFile(string $filePath): ?string
    {
        $content = file_get_contents($filePath);
        if (preg_match('/namespace\s+(.+?);/s', $content, $matches) &&
            preg_match('/class\s+(\w+)/', $content, $classMatches)) {
            return $matches[1] . '\\' . $classMatches[1];
        }
        return null;
    }
}
