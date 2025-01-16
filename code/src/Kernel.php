<?php

declare(strict_types=1);

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $configDir = $this->getConfigDir();
        $routes->import($configDir.'/routes.yaml');

        $this->loadDomainConfigurations($routes);
    }

    protected function loadDomainConfigurations(RoutingConfigurator $routes): void
    {
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
            } else {
                // Search in subdirectories for API routes
                $subFinder = new Finder();
                $subFinder->directories()
                    ->in($domainPath)
                    ->depth(0);

                foreach ($subFinder as $subFolder) {
                    $apiPath = $domainPath.'/'.$subFolder->getBasename().'/Presentation/Api';
                    if (is_dir($apiPath)) {
                        $routes->import($apiPath, 'attribute')
                            ->prefix('/'.$domainName);
                    }
                }
            }

            // Load Web routes
            $webPath = $domainPath.'/Presentation/Web';
            if (is_dir($webPath)) {
                $routes->import($webPath, 'attribute')
                    ->prefix('/'.$domainName);
            } else {
                // Search in subdirectories for Web routes
                $subFinder = new Finder();
                $subFinder->directories()
                    ->in($domainPath)
                    ->depth(0);

                foreach ($subFinder as $subFolder) {
                    $webPath = $domainPath.'/'.$subFolder->getBasename().'/Presentation/Web';
                    if (is_dir($webPath)) {
                        $routes->import($webPath, 'attribute')
                            ->prefix('/'.$domainName);
                    }
                }
            }

            // Load YAML routes if they exist (optional)
            $routesPath = $domainPath.'/Resources/Config/routes.yaml';
            if (file_exists($routesPath)) {
                $routes->import($routesPath);
            } else {
                // Search in subdirectories for routes.yaml
                $subFinder = new Finder();
                $subFinder->directories()
                    ->in($domainPath)
                    ->depth(0);

                foreach ($subFinder as $subFolder) {
                    $routesPath = $domainPath.'/'.$subFolder->getBasename().'/Resources/Config/routes.yaml';
                    if (file_exists($routesPath)) {
                        $routes->import($routesPath);
                    }
                }
            }
        }
    }

    protected function buildContainer(): ContainerBuilder
    {
        $container = parent::buildContainer();

        // Load domain services
        $projectDir = $this->getProjectDir();
        $finder = new Finder();
        $finder->directories()
            ->in($projectDir.'/src')
            ->depth(0)
            ->notName('Kernel.php');

        // Load services
        $loader = new YamlFileLoader($container, new FileLocator());
        foreach ($finder as $domainDir) {
            $domainPath = $domainDir->getRealPath();
            $servicesPath = $domainPath.'/Resources/Config/services.yaml';

            if (file_exists($servicesPath)) {
                $loader->load($servicesPath);
            } else {
                // Search in all subdirectories
                $subFinder = new Finder();
                $subFinder->directories()
                    ->in($domainPath)
                    ->depth(0);

                foreach ($subFinder as $subFolder) {
                    $servicesPath = $domainPath.'/'.$subFolder->getBasename().'/Resources/Config/services.yaml';

                    if (file_exists($servicesPath)) {
                        $loader->load($servicesPath);
                    }
                }
            }
        }

        return $container;
    }
}
