<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class DbpRelayEsignExtension extends ConfigurableExtension
{
    public function loadInternal(array $mergedConfig, ContainerBuilder $container)
    {
        $this->extendArrayParameter(
            $container, 'api_platform.resource_class_directories', [__DIR__.'/../Entity']);

        $pathsToHide = [
            '/esign/advancedly_signed_documents/{identifier}',
            '/esign/advancedly_signed_documents',
            '/esign/qualified_signing_requests/{identifier}',
            '/esign/qualified_signing_requests',
            '/esign/electronic_signature_verification_reports',
            '/esign/electronic_signature_verification_reports/{identifier}',
        ];

        if (($_ENV['PDF_AS_VERIFICATION_ENABLE'] ?? 'true') !== 'true') {
            $pathsToHide = array_merge($pathsToHide, [
                '/esign/electronic_signatures/{id}',
            ]);
        }

        $this->extendArrayParameter($container, 'dbp_api.paths_to_hide', $pathsToHide);

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );
        $loader->load('services.yaml');

        $definition = $container->getDefinition('Dbp\Relay\EsignBundle\Service\PdfAsApi');
        $definition->addMethodCall('setConfig', [$mergedConfig]);
    }

    private function extendArrayParameter(ContainerBuilder $container, string $parameter, array $values)
    {
        if (!$container->hasParameter($parameter)) {
            $container->setParameter($parameter, []);
        }
        $oldValues = $container->getParameter($parameter);
        assert(is_array($oldValues));
        $container->setParameter($parameter, array_merge($oldValues, $values));
    }
}
