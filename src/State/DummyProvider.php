<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\State;

use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\PartialPaginatorInterface;
use ApiPlatform\State\ProviderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * For GET endpoints which we don't implement, either return an empty collection
 * or return null which gets translated to 404.
 *
 * @implements ProviderInterface<object>
 */
class DummyProvider extends AbstractController implements ProviderInterface
{
    /**
     * @return PartialPaginatorInterface<object>|iterable<mixed, object>|object|null
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array|object|null
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if ($operation instanceof CollectionOperationInterface) {
            return [];
        } else {
            return null;
        }
    }
}
