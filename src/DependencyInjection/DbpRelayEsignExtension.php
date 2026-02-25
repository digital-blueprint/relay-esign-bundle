<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\DependencyInjection;

use Dbp\Relay\CoreBundle\Extension\ExtensionTrait;
use Dbp\Relay\EsignBundle\Authorization\AuthorizationService;
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
        $this->addResourceClassDirectory($container, __DIR__.'/../Api');

        $pathsToHide = [];
        if (!BundleConfig::hasVerification()) {
            $pathsToHide['GET'] = [
                '/esign/electronic-signature-verification-reports',
                '/esign/electronic-signature-verification-reports/{identifier}',
            ];
            $pathsToHide['POST'] = [
                '/esign/electronic-signature-verification-reports',
            ];
        }

        if (!BundleConfig::hasBatch()) {
            $pathsToHide['GET'] = [
                '/esign/qualified-batch-signing-results/{identifier}',
            ];
            $pathsToHide['POST'] = [
                '/esign/qualified-batch-signing-requests',
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

        $definition = $container->getDefinition(AuthorizationService::class);
        $definition->addMethodCall('setConfig', [$mergedConfig]);
    }
}
