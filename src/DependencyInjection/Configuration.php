<?php

declare(strict_types=1);

namespace ChrisDev\UxComponents\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('chris_dev_ux_components');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('navbar')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('brand')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('label')->defaultValue('Symfony Ux Starter Kit')->end()
                                ->scalarNode('route')->defaultValue('app_home')->end()
                                ->scalarNode('logo')->defaultValue('bi:boxes')->end()
                            ->end()
                        ->end()
                        ->arrayNode('items')
                            ->arrayPrototype()
                                ->children()
                                    ->scalarNode('label')->isRequired()->end()
                                    ->scalarNode('route')->isRequired()->end()
                                    ->scalarNode('icon')->isRequired()->end()
                                    ->booleanNode('authenticated')->defaultFalse()->end()
                                ->end()
                            ->end()
                            ->defaultValue([
                                ['label' => 'Accueil', 'route' => 'app_home', 'icon' => 'bi:house', 'authenticated' => false],
                                ['label' => 'Compte', 'route' => 'app_account', 'icon' => 'bi:person', 'authenticated' => true],
                            ])
                        ->end()
                        ->arrayNode('user_items')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->arrayNode('logged_in')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('label')->defaultValue('Logout')->end()
                                        ->scalarNode('route')->defaultValue('app_logout')->end()
                                        ->scalarNode('icon')->defaultValue('bi:box-arrow-right')->end()
                                    ->end()
                                ->end()
                                ->arrayNode('logged_out')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('label')->defaultValue('Login')->end()
                                        ->scalarNode('route')->defaultValue('app_login')->end()
                                        ->scalarNode('icon')->defaultValue('bi:box-arrow-in-right')->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
