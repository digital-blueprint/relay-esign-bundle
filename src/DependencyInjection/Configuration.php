<?php

declare(strict_types=1);

namespace DBP\API\ESignBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('dbp_esign');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('qualified_url')->end()
                ->scalarNode('qualified_static_url')->end()
                ->scalarNode('qualified_profile_id')->end()
                ->scalarNode('advanced_url')->end()
                ->arrayNode('advanced_profiles')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('name')->end()
                            ->scalarNode('key_id')->end()
                            ->scalarNode('profile_id')->end()
                            ->scalarNode('role')->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
