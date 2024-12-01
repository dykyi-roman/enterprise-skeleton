<?php

declare(strict_types=1);

namespace App\YourDomain\Resources;

use App\YourDomain\Resources\Attribute\Route;
use Illuminate\Support\Facades\Route as LaravelRoute;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Console\Attribute\AsCommand;

final class DomainServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerRoutes();
        $this->registerCommands();
    }

    private function registerRoutes(): void
    {
        $presentationPath = dirname(__DIR__).'/Presentation';
        if (!is_dir($presentationPath)) {
            return;
        }

        $this->registerRoutesFromPath($presentationPath);
    }

    private function registerCommands(): void
    {
        $consolePath = dirname(__DIR__).'/Presentation/Console';
        if (!is_dir($consolePath)) {
            return;
        }

        $commands = $this->findCommands($consolePath);
        if (!empty($commands)) {
            $this->commands($commands);
        }
    }

    private function registerRoutesFromPath(string $path): void
    {
        $directory = new \RecursiveDirectoryIterator($path);
        $iterator = new \RecursiveIteratorIterator($directory);
        $files = new \RegexIterator($iterator, '/^.+\.php$/i', \RegexIterator::GET_MATCH);

        foreach ($files as $file) {
            $filePath = $file[0];
            $className = $this->getClassNameFromFile($filePath);

            if (!$className || !class_exists($className)) {
                continue;
            }

            $this->registerClassRoutes($className);
        }
    }

    private function registerClassRoutes(string $className): void
    {
        $reflection = new \ReflectionClass($className);
        $attributes = $reflection->getAttributes(Route::class, \ReflectionAttribute::IS_INSTANCEOF);

        foreach ($attributes as $attribute) {
            $route = $attribute->newInstance();

            LaravelRoute::middleware($route->middleware)
                ->match($route->methods, $route->path, $className)
                ->name($route->name);
        }
    }

    private function findCommands(string $path): array
    {
        $commands = [];

        $directory = new \RecursiveDirectoryIterator($path);
        $iterator = new \RecursiveIteratorIterator($directory);
        $files = new \RegexIterator($iterator, '/^.+Command\.php$/i', \RegexIterator::GET_MATCH);

        foreach ($files as $file) {
            $filePath = $file[0];
            $className = $this->getClassNameFromFile($filePath);

            if ($className && class_exists($className)) {
                $reflection = new \ReflectionClass($className);
                $attributes = $reflection->getAttributes(AsCommand::class);

                if (!empty($attributes)) {
                    $commands[] = $className;
                }
            }
        }

        return $commands;
    }

    private function getClassNameFromFile(string $filePath): ?string
    {
        $content = file_get_contents($filePath);
        if (preg_match('/namespace\s+(.+?);/s', $content, $matches)
            && preg_match('/class\s+(\w+)/', $content, $classMatches)) {
            return $matches[1].'\\'.$classMatches[1];
        }

        return null;
    }
}
