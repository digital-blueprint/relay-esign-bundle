<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('dbp_esign');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('qualified_url')
                    ->info('The URL to the PDF-AS server for qualified signatures')
                    ->example('https://pdfas.example.com/pdf-as-web')
                ->end()
                ->scalarNode('qualified_callback_url')->end()
                ->scalarNode('qualified_error_callback_url')->end()
                ->arrayNode('qualified_profiles')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('name')->end()
                            ->scalarNode('role')->end()
                            ->scalarNode('profile_id')->end()
                            ->scalarNode('user_text_table')->end()
                            ->integerNode('user_text_row')->end()
                            ->scalarNode('user_text_attach_parent')->end()
                            ->scalarNode('user_text_attach_child')->end()
                            ->integerNode('user_text_attach_row')->end()
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('advanced_url')
                    ->info('The URL to the PDF-AS server for advanced signatures')
                    ->example('https://pdfas.example.com/pdf-as-web')
                ->end()
                ->arrayNode('advanced_profiles')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('name')->end()
                            ->scalarNode('role')->end()
                            ->scalarNode('key_id')->end()
                            ->scalarNode('profile_id')->end()
                            ->scalarNode('user_text_table')->end()
                            ->integerNode('user_text_row')->end()
                            ->scalarNode('user_text_attach_parent')->end()
                            ->scalarNode('user_text_attach_child')->end()
                            ->integerNode('user_text_attach_row')->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
