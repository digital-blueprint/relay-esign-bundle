<?php

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
            ->scalarNode('official_url')->end()
            ->scalarNode('qualified_url')->end()
            ->scalarNode('qualified_static_url')->end()
            ->end()
            ->end();

        return $treeBuilder;
    }
}
