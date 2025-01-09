<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\Tests;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class DummyAuthorizationChecker implements AuthorizationCheckerInterface
{
    public function __construct(public array $roles = [])
    {
    }

    public function isGranted(mixed $attribute, mixed $subject = null): bool
    {
        if ($subject !== null) {
            throw new \RuntimeException();
        }

        return in_array($attribute, $this->roles, true);
    }
}
