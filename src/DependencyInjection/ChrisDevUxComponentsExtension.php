<?php

declare(strict_types=1);

namespace ChrisDev\UxComponents\DependencyInjection;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

final class ChrisDevUxComponentsExtension extends ConfigurableExtension
{
    /**
     * @param array<string, mixed> $config
     */
    public function getConfiguration(array $config, ContainerBuilder $container): Configuration
    {
        return new Configuration();
    }

    /**
     * @param array{navbar: array{brand: array<string, mixed>, items: list<array<string, mixed>>, user_items?: array<string, mixed>}} $mergedConfig
     */
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../../config')
        );

        $loader->load('services.yaml');

        if (interface_exists(ManagerRegistry::class)) {
            $loader->load('services_doctrine.yaml');
        }

        $container->setParameter('chris_dev_ux_components.navbar.brand', $mergedConfig['navbar']['brand']);
        $container->setParameter('chris_dev_ux_components.navbar.items', $mergedConfig['navbar']['items']);
        $container->setParameter(
            'chris_dev_ux_components.navbar.user_items',
            $mergedConfig['navbar']['user_items'] ?? [
                'logged_in' => ['label' => 'Logout', 'route' => 'app_logout', 'icon' => 'bi:box-arrow-right'],
                'logged_out' => ['label' => 'Login', 'route' => 'app_login', 'icon' => 'bi:box-arrow-in-right'],
            ],
        );
    }
}
