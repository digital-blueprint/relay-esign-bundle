<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    private function getUserTextNode(): ArrayNodeDefinition
    {
        $builder = new ArrayNodeDefinition('user_text');
        $builder
            ->info('For extending the PDF-AS signature layout with user provided text (optional)')
            ->children()
                ->scalarNode('target_table')
                    ->info('The profile table ID to attach the content to.')
                    ->example('usercontent')
                    ->isRequired()
                ->end()
                ->integerNode('target_row')
                    ->info('The index of the first unset row in the table (starts with 1)')
                    ->example('1')
                    ->isRequired()
                ->end()
                ->arrayNode('attach')
                    ->info('In case there is content "child_table" will be attached to "parent_table" at "parent_row" (optional)')
                    ->children()
                        ->scalarNode('parent_table')
                            ->info('The name of the parent table')
                            ->example('parent')
                            ->isRequired()
                        ->end()
                        ->scalarNode('child_table')
                            ->info('Child table name')
                            ->example('child')
                            ->isRequired()
                        ->end()
                        ->integerNode('parent_row')
                            ->info('The index of the row where the child table will be attached to')
                            ->example('4')
                            ->isRequired()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $builder;
    }

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('dbp_relay_esign');

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('qualified_signature')
                    ->children()
                        ->scalarNode('server_url')
                            ->info('The URL to the PDF-AS server for qualified signatures')
                            ->example('https://pdfas.example.com/pdf-as-web')
                            ->isRequired()
                        ->end()
                        ->scalarNode('callback_url')
                            ->info('The URL pdf-as will redirect to when the signature is done (optional)')
                            ->example('https://pdfas.example.com/static/callback.html')
                            ->setDeprecated('dbp/relay-esign-bundle', '???', 'The "%node%" option is deprecated. The API server now provides the callback URL itself.')
                        ->end()
                        ->scalarNode('error_callback_url')
                            ->info('The URL pdf-as will redirect to when the signature failed (optional)')
                            ->example('https://pdfas.example.com/static/error.html')
                            ->setDeprecated('dbp/relay-esign-bundle', '???', 'The "%node%" option is deprecated. The API server now provides the callback URL itself.')
                        ->end()
                        ->arrayNode('profiles')
                            ->arrayPrototype()
                                ->children()
                                    ->scalarNode('name')
                                        ->info('The name of the profile, this needs to be passed to the API')
                                        ->example('myprofile')
                                        ->isRequired()
                                    ->end()
                                    ->scalarNode('role')
                                        ->info('The Symfony role required to use this profile')
                                        ->example('ROLE_FOOBAR')
                                    ->end()
                                    ->scalarNode('profile_id')
                                        ->info('The PDF-AS signature profile ID to use')
                                        ->example('MYPROFILE')
                                        ->isRequired()
                                    ->end()
                                    ->append($this->getUserTextNode())
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('advanced_signature')
                    ->children()
                        ->scalarNode('server_url')
                            ->info('The URL to the PDF-AS server for advanced signatures')
                            ->example('https://pdfas.example.com/pdf-as-web')
                            ->isRequired()
                        ->end()
                        ->arrayNode('profiles')
                            ->arrayPrototype()
                                ->children()
                                    ->scalarNode('name')
                                        ->info('The name of the profile, this needs to be passed to the API')
                                        ->example('myprofile')
                                        ->isRequired()
                                    ->end()
                                    ->scalarNode('role')
                                        ->info('The Symfony role required to use this profile')
                                        ->example('ROLE_FOOBAR')
                                    ->end()
                                    ->scalarNode('key_id')
                                        ->info('The PDF-AS signature key ID used for singing')
                                        ->example('MYKEY')
                                        ->isRequired()
                                    ->end()
                                    ->scalarNode('profile_id')
                                        ->info('The PDF-AS signature profile ID to use')
                                        ->example('MYPROFILE')
                                        ->isRequired()
                                    ->end()
                                    ->append($this->getUserTextNode())
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
