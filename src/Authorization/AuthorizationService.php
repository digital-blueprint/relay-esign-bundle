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
     * Throws if the current user doesn't have permissions to sign with the given profile.
     */
    public function checkCanSignWithProfile(string $profileName): void
    {
        $profile = $this->config->getProfile($profileName);
        if ($profile === null) {
            // We don't want to leak which profiles exist
            throw new AccessDeniedException();
        }

        // In case the legacy symfony role is specified it takes precedence over the new system
        $legacyRole = $profile->getRole();
        if ($legacyRole !== null) {
            if ($this->authorizationChecker->isGranted($legacyRole)) {
                return;
            }
            throw new AccessDeniedException();
        }

        $resource = new ProfileData($profile->getName());
        $this->denyAccessUnlessIsGrantedResourcePermission(Configuration::ROLE_PROFILE_SIGNER, $resource);
    }

    /**
     * Throws if the user is not allowed to verify signatures.
     */
    public function checkCanVerify(): void
    {
        $this->denyAccessUnlessIsGrantedRole(Configuration::ROLE_VERIFIER);
    }
}
