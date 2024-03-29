<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\DependencyInjection;

use Dbp\Relay\CoreBundle\Extension\ExtensionTrait;
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
            '/esign/advancedly-signed-documents/{identifier}',
            '/esign/advancedly-signed-documents',
            '/esign/qualified-signing-requests/{identifier}',
            '/esign/qualified-signing-requests',
            '/esign/electronic-signature-verification-reports',
            '/esign/electronic-signature-verification-reports/{identifier}',
        ];

        if (($_ENV['ESIGN_PDF_AS_VERIFICATION_ENABLE'] ?? 'false') !== 'true') {
            $pathsToHide = array_merge($pathsToHide, [
                '/esign/electronic-signatures/{identifier}',
            ]);
        }

        foreach ($pathsToHide as $path) {
            $this->addPathToHide($container, $path);
        }

        $this->addRouteResource($container, __DIR__.'/../Resources/config/routes.yaml', 'yaml');

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );
        $loader->load('services.yaml');

        $definition = $container->getDefinition('Dbp\Relay\EsignBundle\Service\PdfAsApi');
        $definition->addMethodCall('setConfig', [$mergedConfig]);
    }
}
