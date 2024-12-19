<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\Authorization;

use Dbp\Relay\CoreBundle\Authorization\AbstractAuthorizationService;
use Dbp\Relay\EsignBundle\Configuration\BundleConfig;
use Dbp\Relay\EsignBundle\DependencyInjection\Configuration;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class AuthorizationService extends AbstractAuthorizationService
{
    public function __construct(private readonly BundleConfig $config, private readonly AuthorizationCheckerInterface $authorizationChecker)
    {
        parent::__construct();
    }

    /**
     * Throws if the user is not allowed to sign things.
     */
    public function checkCanSign(): void
    {
        $this->denyAccessUnlessIsGrantedRole(Configuration::ROLE_SIGNER);
    }

    /**
     * Returns if the current user has permissions to sign with the given profile.
     */
    private function getCanSignWithProfile(string $profileName): bool
    {
        $profile = $this->config->getProfile($profileName);
        if ($profile === null) {
            // We don't want to leak which profiles exist
            return false;
        }

        // In case the legacy symfony role is specified it takes precedence over the new system
        $legacySymfonyRole = $profile->getRole();
        if ($legacySymfonyRole !== null) {
            return $this->authorizationChecker->isGranted($legacySymfonyRole);
        }

        $resource = new ProfileData($profile->getName());

        return $this->isGrantedResourcePermission(Configuration::ROLE_PROFILE_SIGNER, $resource);
    }

    /**
     * Throws if the current user doesn't have permissions to sign with the given profile.
     */
    public function checkCanSignWithProfile(string $profileName): void
    {
        $this->checkCanSign();
        if (!$this->getCanSignWithProfile($profileName)) {
            throw new AccessDeniedException();
        }
    }

    /**
     * Throws if the current user doesn't have permissions to sign with any qualified profile.
     */
    public function checkCanSignWithAnyQualifiedProfile(): void
    {
        $this->checkCanSign();
        $qualified = $this->config->getQualified();
        if ($qualified !== null) {
            foreach ($qualified->getProfiles() as $profile) {
                if ($this->getCanSignWithProfile($profile->getName())) {
                    return;
                }
            }
        }
        throw new AccessDeniedException();
    }

    /**
     * Throws if the user is not allowed to verify signatures.
     */
    public function checkCanVerify(): void
    {
        $this->denyAccessUnlessIsGrantedRole(Configuration::ROLE_VERIFIER);
    }
}
