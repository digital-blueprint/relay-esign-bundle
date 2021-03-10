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
                ->scalarNode('advanced_url')->end()
                ->arrayNode('qualified_profile')
                    ->children()
                        ->scalarNode('profile_id')->end()
                        ->scalarNode('profile_user_text_table')->end()
                        ->scalarNode('profile_user_text_parent_table')->end()
                        ->integerNode('profile_user_text_parent_table_row')->end()
                    ->end()
                ->end()
                ->arrayNode('advanced_profiles')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('name')->end()
                            ->scalarNode('role')->end()
                            ->scalarNode('key_id')->end()
                            ->scalarNode('profile_id')->end()
                            ->scalarNode('profile_user_text_table')->end()
                            ->scalarNode('profile_user_text_parent_table')->end()
                            ->integerNode('profile_user_text_parent_table_row')->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
