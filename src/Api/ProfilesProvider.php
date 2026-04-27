<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\Api;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\PartialPaginatorInterface;
use ApiPlatform\State\ProviderInterface;
use Dbp\Relay\CoreBundle\Exception\ApiError;
use Dbp\Relay\EsignBundle\Authorization\AuthorizationService;
use Dbp\Relay\EsignBundle\Configuration\BundleConfig;
use Symfony\Component\HttpFoundation\Response;

/**
 * For GET endpoints which we don't implement, either return an empty collection
 * or return null which gets translated to 404.
 *
 * @implements ProviderInterface<object>
 */
readonly class ProfilesProvider implements ProviderInterface
{
    public function __construct(private AuthorizationService $authorizationService, private BundleConfig $config)
    {
    }

    /**
     * @return PartialPaginatorInterface<object>|iterable<mixed, object>|object|null
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array|object|null
    {
        if (!array_key_exists('filters', $context)) {
            throw new ApiError(Response::HTTP_BAD_REQUEST, 'No query parameters given!');
        }
        $filters = $context['filters'];

        if (!array_key_exists('type', $filters) || !is_string($filters['type'])) {
            throw new ApiError(Response::HTTP_BAD_REQUEST, "Missing required 'type' query parameter");
        }

        $type = $filters['type'];

        if ($type !== 'advanced' && $type !== 'qualified' && $type !== 'simple') {
            throw new ApiError(Response::HTTP_BAD_REQUEST, "Unsupported signature type: {$filters['type']}");
        }

        if ($type === 'advanced') {
            $profiles = $this->config->getAdvanced()->getProfiles();
            $availableProfiles = $this->getAvailableProfiles($profiles);
        } elseif ($type === 'qualified') {
            $profiles = $this->config->getQualified()->getProfiles();
            $availableProfiles = $this->getAvailableProfiles($profiles);
        } else {
            $availableProfiles = [];
        }

        return $availableProfiles;
    }

    private function getAvailableProfiles($profiles): array
    {
        $availableProfiles = [];
        foreach ($profiles as $profile) {
            if (!$this->authorizationService->getCanSignWithProfile($profile->getName())) {
                continue;
            }
            $newProfile = new Profiles();
            $newProfile->setIdentifier($profile->getName());
            $newProfile->setAllowAnnotations($profile->getAllowAnnotations());
            $newProfile->setAllowManualPositioning($profile->getAllowManualPositioning());
            $newProfile->setDisplayNameDe($profile->getDisplayNameDe());
            $newProfile->setDisplayNameEn($profile->getDisplayNameEn());
            $newProfile->setLanguage($profile->getLanguage());
            $newProfile->setInvisible($profile->getInvisible());
            $availableProfiles[] = $newProfile;
        }

        return $availableProfiles;
    }
}
