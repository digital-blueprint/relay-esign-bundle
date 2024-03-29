<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
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
                                    ->end()
                                    ->scalarNode('role')
                                        ->info('The Symfony role required to use this profile')
                                        ->example('ROLE_FOOBAR')
                                    ->end()
                                    ->scalarNode('profile_id')
                                        ->info('The PDF-AS signature profile ID to use')
                                        ->example('MYPROFILE')
                                    ->end()
                                    ->scalarNode('user_text_table')
                                        ->info('The profile table ID to attach the content to. Leave empty to disable user text.')
                                        ->example('usercontent')
                                    ->end()
                                    ->integerNode('user_text_row')
                                        ->info('The index of the first unset row in the table (starts with 1)')
                                        ->example('1')
                                    ->end()
                                    ->scalarNode('user_text_attach_parent')
                                        ->info('In case there is content "child" will be attached to "parent" at "row" (optional)')
                                        ->example('parent')
                                    ->end()
                                    ->scalarNode('user_text_attach_child')
                                        ->info('In case there is content "child" will be attached to "parent" at "row" (optional)')
                                        ->example('child')
                                    ->end()
                                    ->integerNode('user_text_attach_row')
                                        ->info('In case there is content "child" will be attached to "parent" at "row" (optional)')
                                        ->example('4')
                                    ->end()
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
                        ->end()
                        ->arrayNode('profiles')
                            ->arrayPrototype()
                                ->children()
                                    ->scalarNode('name')
                                        ->info('The name of the profile, this needs to be passed to the API')
                                        ->example('myprofile')
                                    ->end()
                                    ->scalarNode('role')
                                        ->info('The Symfony role required to use this profile')
                                        ->example('ROLE_FOOBAR')
                                    ->end()
                                    ->scalarNode('key_id')
                                        ->info('The PDF-AS signature key ID used for singing')
                                        ->example('MYKEY')
                                    ->end()
                                    ->scalarNode('profile_id')
                                        ->info('The PDF-AS signature profile ID to use')
                                        ->example('MYPROFILE')
                                    ->end()
                                    ->scalarNode('user_text_table')
                                        ->info('The profile table ID to attach the content to. Leave empty to disable user text.')
                                        ->example('usercontent')
                                    ->end()
                                    ->integerNode('user_text_row')
                                        ->info('The index of the first unset row in the table (starts with 1)')
                                        ->example('1')
                                    ->end()
                                    ->scalarNode('user_text_attach_parent')
                                        ->info('In case there is content "child" will be attached to "parent" at "row" (optional)')
                                        ->example('parent')
                                    ->end()
                                    ->scalarNode('user_text_attach_child')
                                        ->info('In case there is content "child" will be attached to "parent" at "row" (optional)')
                                        ->example('child')
                                    ->end()
                                    ->integerNode('user_text_attach_row')
                                        ->info('In case there is content "child" will be attached to "parent" at "row" (optional)')
                                        ->example('4')
                                    ->end()
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
