<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\State;

use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\PartialPaginatorInterface;
use ApiPlatform\State\ProviderInterface;
use Dbp\Relay\EsignBundle\Authorization\AuthorizationService;

/**
 * For GET endpoints which we don't implement, either return an empty collection
 * or return null which gets translated to 404.
 *
 * @implements ProviderInterface<object>
 */
class DummySignProvider implements ProviderInterface
{
    public function __construct(private readonly AuthorizationService $authorizationService)
    {
    }

    /**
     * @return PartialPaginatorInterface<object>|iterable<mixed, object>|object|null
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array|object|null
    {
        $this->authorizationService->checkCanSign();

        if ($operation instanceof CollectionOperationInterface) {
            return [];
        }

        return null;
    }
}
