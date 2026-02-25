<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\Tests\Authorization;

use Dbp\Relay\CoreBundle\TestUtils\TestAuthorizationService;
use Dbp\Relay\EsignBundle\Authorization\AuthorizationService;
use Dbp\Relay\EsignBundle\Configuration\BundleConfig;
use PHPUnit\Framework\TestCase;

class AuthorizationTest extends TestCase
{
    private AuthorizationService $auth;
    private DummyAuthorizationChecker $checker;

    public function setUp(): void
    {
        $config = [
            'qualified_signature' => [
                'server_url' => 'https://sig.tugraz.at/pdf-as-web',
                'profiles' => [
                    [
                        'name' => 'some-profile',
                        'profile_id' => 'ID',
                    ],
                    [
                        'name' => 'legacy',
                        'role' => 'SYMFONY_ROLE',
                        'profile_id' => 'ID',
                    ],
                ],
            ],
            'advanced_signature' => [
                'server_url' => 'https://sig.tugraz.at/pdf-as-web',
                'profiles' => [
                    [
                        'name' => 'unused',
                        'profile_id' => 'ID',
                        'key_id' => 'key',
                    ],
                ],
            ],
            'authorization' => [
                'roles' => [
                    'ROLE_SIGNER' => 'user.get("HAS_ROLE_SIGNER")',
                    'ROLE_VERIFIER' => 'user.get("HAS_ROLE_VERIFIER")',
                ],
                'resource_permissions' => [
                    'ROLE_PROFILE_SIGNER' => 'resource.getName() in user.get("ALLOWED_PROFILES")',
                ],
            ],
        ];
        $this->checker = new DummyAuthorizationChecker();
        $this->auth = new AuthorizationService(new BundleConfig($config), $this->checker);
        $this->auth->setConfig($config);
    }

    public function testCheckCanSign()
    {
        TestAuthorizationService::setUp($this->auth, currentUserAttributes: [
            'HAS_ROLE_SIGNER' => true,
        ]);
        $this->auth->checkCanSign();

        TestAuthorizationService::setUp($this->auth, currentUserAttributes: [
            'HAS_ROLE_SIGNER' => false,
        ]);
        $this->expectException(\Exception::class);
        $this->auth->checkCanSign();
    }

    public function testCheckCanVerify()
    {
        TestAuthorizationService::setUp($this->auth, currentUserAttributes: [
            'HAS_ROLE_VERIFIER' => true,
        ]);
        $this->auth->checkCanVerify();

        TestAuthorizationService::setUp($this->auth, currentUserAttributes: [
            'HAS_ROLE_VERIFIER' => false,
        ]);
        $this->expectException(\Exception::class);
        $this->auth->checkCanVerify();
    }

    public function testCheckCanSignWithProfile()
    {
        TestAuthorizationService::setUp($this->auth, currentUserAttributes: [
            'HAS_ROLE_SIGNER' => true,
            'ALLOWED_PROFILES' => ['some-profile'],
        ]);
        $this->expectNotToPerformAssertions();
        $this->auth->checkCanSignWithProfile('some-profile');
    }

    public function testCheckCanSignWithProfileNotExisting()
    {
        TestAuthorizationService::setUp($this->auth, currentUserAttributes: [
            'HAS_ROLE_SIGNER' => true,
            'ALLOWED_PROFILES' => ['some-profile'],
        ]);
        $this->expectException(\Exception::class);
        $this->auth->checkCanSignWithProfile('some-other-profile');
    }

    public function testCheckCanSignWithProfileNotSigner()
    {
        TestAuthorizationService::setUp($this->auth, currentUserAttributes: [
            'HAS_ROLE_SIGNER' => false,
            'ALLOWED_PROFILES' => ['some-profile'],
        ]);
        $this->expectException(\Exception::class);
        $this->auth->checkCanSignWithProfile('some-profile');
    }

    public function testCheckCanSignWithProfileNotAllowed()
    {
        TestAuthorizationService::setUp($this->auth, currentUserAttributes: [
            'HAS_ROLE_SIGNER' => true,
            'ALLOWED_PROFILES' => ['some-profile'],
        ]);
        $this->expectException(\Exception::class);
        $this->auth->checkCanSignWithProfile('unused');
    }

    public function testCheckCanSignWithAnyQualifiedProfile()
    {
        TestAuthorizationService::setUp($this->auth, currentUserAttributes: [
            'HAS_ROLE_SIGNER' => true,
            'ALLOWED_PROFILES' => ['some-profile'],
        ]);
        $this->auth->checkCanSignWithAnyQualifiedProfile();
        TestAuthorizationService::setUp($this->auth, currentUserAttributes: [
            'HAS_ROLE_SIGNER' => true,
            'ALLOWED_PROFILES' => [],
        ]);
        $this->expectException(\Exception::class);
        $this->auth->checkCanSignWithAnyQualifiedProfile();
    }

    public function testLegacyRoles()
    {
        TestAuthorizationService::setUp($this->auth, currentUserAttributes: [
            'HAS_ROLE_SIGNER' => true,
        ]);
        $this->checker->roles = ['SYMFONY_ROLE'];
        $this->auth->checkCanSignWithProfile('legacy');

        $this->checker->roles = ['SYMFONY_OTHER_ROLE'];
        $this->expectException(\Exception::class);
        $this->auth->checkCanSignWithProfile('legacy');
    }
}
