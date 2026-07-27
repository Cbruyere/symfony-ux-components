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
                            ->children()
                                ->arrayNode('logged_in')
                                    ->children()
                                        ->scalarNode('label')->isRequired()->end()
                                        ->scalarNode('route')->isRequired()->end()
                                        ->scalarNode('icon')->isRequired()->end()
                                    ->end()
                                ->end()
                                ->arrayNode('logged_out')
                                    ->children()
                                        ->scalarNode('label')->isRequired()->end()
                                        ->scalarNode('route')->isRequired()->end()
                                        ->scalarNode('icon')->isRequired()->end()
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
