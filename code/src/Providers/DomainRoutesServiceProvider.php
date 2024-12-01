<?php

namespace App\Providers;

use App\Providers\Attributes\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route as LaravelRoute;
use ReflectionClass;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;

class DomainRoutesServiceProvider extends ServiceProvider
{
    private const array DOMAINS = [
        'Healthcheck',
        'YourDomain',
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->registerDomainRoutes();
    }

    /**
     * Register routes from all domain modules
     */
    private function registerDomainRoutes(): void
    {
        foreach (self::DOMAINS as $domain) {
            $presentationPath = base_path("src/{$domain}/Presentation");
            if (!is_dir($presentationPath)) {
                continue;
            }

            $this->registerRoutesFromPath($presentationPath);
        }
    }

    /**
     * Register routes from a specific path
     */
    private function registerRoutesFromPath(string $path): void
    {
        $directory = new RecursiveDirectoryIterator($path);
        $iterator = new RecursiveIteratorIterator($directory);
        $files = new RegexIterator($iterator, '/^.+\.php$/i', RegexIterator::GET_MATCH);

        foreach ($files as $file) {
            $filePath = $file[0];
            $className = $this->getClassNameFromFile($filePath);

            if (!$className || !class_exists($className)) {
                continue;
            }

            $this->registerClassRoutes($className);
        }
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

    /**
     * Register routes for a specific class
     */
    private function registerClassRoutes(string $className): void
    {
        $reflection = new ReflectionClass($className);
        $attributes = $reflection->getAttributes(Route::class, \ReflectionAttribute::IS_INSTANCEOF);

        foreach ($attributes as $attribute) {
            $route = $attribute->newInstance();

            LaravelRoute::middleware($route->middleware)
                ->match($route->methods, $route->path, $className)
                ->name($route->name);
        }
    }
}
