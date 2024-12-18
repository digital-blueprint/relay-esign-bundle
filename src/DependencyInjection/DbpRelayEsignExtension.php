<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\DependencyInjection;

use Dbp\Relay\CoreBundle\Extension\ExtensionTrait;
use Dbp\Relay\EsignBundle\Configuration\BundleConfig;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class DbpRelayEsignExtension extends ConfigurableExtension
{
    use ExtensionTrait;

    public function loadInternal(array $mergedConfig, ContainerBuilder $container): void
    {
        $pathsToHide = [
            'GET' => [
                '/esign/advancedly-signed-documents/{identifier}',
                '/esign/advancedly-signed-documents',
                '/esign/qualified-signing-requests/{identifier}',
                '/esign/qualified-signing-requests',
                '/esign/qualifiedly-signed-documents',
            ],
        ];

        if (!BundleConfig::hasVerification()) {
            $pathsToHide['GET'] = array_merge($pathsToHide['GET'], [
                '/esign/electronic-signatures',
                '/esign/electronic-signatures/{identifier}',
                '/esign/electronic-signature-verification-reports',
                '/esign/electronic-signature-verification-reports/{identifier}',
            ]);
            $pathsToHide['POST'] = [
                '/esign/electronic-signature-verification-reports',
            ];
        }

        foreach ($pathsToHide as $method => $paths) {
            foreach ($paths as $path) {
                $this->addPathToHide($container, $path, $method);
            }
        }

        $this->addRouteResource($container, __DIR__.'/../Resources/config/routes.yaml', 'yaml');

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );
        $loader->load('services.yaml');

        $definition = $container->getDefinition(BundleConfig::class);
        $definition->addMethodCall('setConfig', [$mergedConfig]);
    }
}
