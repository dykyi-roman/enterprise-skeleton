<?php

declare(strict_types=1);

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Symfony\Component\Finder\Finder;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $configDir = $this->getConfigDir();
        $routes->import($configDir.'/routes.yaml');

        $projectDir = $this->getProjectDir();
        $finder = new Finder();
        $finder->directories()
            ->in($projectDir.'/src')
            ->depth(0)
            ->notName('Kernel.php');

        foreach ($finder as $domainDir) {
            $domainPath = $domainDir->getRealPath();
            $domainName = strtolower($domainDir->getBasename());

            // Load API routes
            $apiPath = $domainPath.'/Presentation/Api';
            if (is_dir($apiPath)) {
                $routes->import($apiPath, 'attribute')
                    ->prefix('/'.$domainName);
            }

            // Load Web routes
            $webPath = $domainPath.'/Presentation/Web';
            if (is_dir($webPath)) {
                $routes->import($webPath, 'attribute')
                    ->prefix('/'.$domainName);
            }

            // Load YAML routes if they exist (optional)
            $routesPath = $domainPath.'/Resources/Config/routes.yaml';
            if (file_exists($routesPath)) {
                $routes->import($routesPath);
            }
        }
    }
}
