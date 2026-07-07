<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\Tests\Api;

use ApiPlatform\Metadata\GetCollection;
use Dbp\Relay\CoreBundle\Exception\ApiError;
use Dbp\Relay\EsignBundle\Api\Profiles;
use Dbp\Relay\EsignBundle\Api\ProfilesProvider;
use Dbp\Relay\EsignBundle\Authorization\AuthorizationService;
use Dbp\Relay\EsignBundle\Configuration\BundleConfig;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProfilesProviderTest extends TestCase
{
    private BundleConfig $config;

    public function setUp(): void
    {
        $config = [
            'qualified_signature' => [
                'server_url' => 'https://sig.tugraz.at/pdf-as-web',
                'profiles' => [
                    [
                        'name' => 'qualified-profile',
                        'profile_id' => 'ID',
                        'allow_annotations' => true,
                        'allow_manual_positioning' => true,
                        'language' => 'de',
                        'invisible' => false,
                    ],
                ],
            ],
            'advanced_signature' => [
                'server_url' => 'https://sig.tugraz.at/pdf-as-web',
                'profiles' => [
                    [
                        'name' => 'advanced-profile',
                        'profile_id' => 'ID',
                        'key_id' => 'KEY',
                        'allow_annotations' => false,
                        'allow_manual_positioning' => false,
                        'language' => 'en',
                        'invisible' => true,
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
        $this->config = new BundleConfig($config);
    }

    private function createProvider(bool $canSign = true): ProfilesProvider
    {
        $auth = $this->createMock(AuthorizationService::class);
        $auth->method('getCanSignWithProfile')->willReturn($canSign);

        $translator = $this->createMock(TranslatorInterface::class);
        $translator->method('trans')->willReturnCallback(
            fn (string $id, array $parameters = [], ?string $domain = null, ?string $locale = null) => $id.'-'.$locale
        );

        return new ProfilesProvider($auth, $this->config, $translator);
    }

    public function testProvideQualifiedProfiles(): void
    {
        $provider = $this->createProvider();

        $result = $provider->provide(new GetCollection(), [], ['filters' => ['type' => 'qualified']]);

        $this->assertCount(1, $result);
        $profile = $result[0];
        $this->assertInstanceOf(Profiles::class, $profile);
        $this->assertSame('qualified-profile', $profile->getIdentifier());
        $this->assertTrue($profile->getAllowAnnotations());
        $this->assertTrue($profile->getAllowManualPositioning());
        $this->assertSame('de', $profile->getLanguage());
        $this->assertFalse($profile->getInvisible());
        $this->assertSame('profile_display_names.qualified-profile-de', $profile->getDisplayNameDe());
        $this->assertSame('profile_display_names.qualified-profile-en', $profile->getDisplayNameEn());
    }

    public function testProvideAdvancedProfiles(): void
    {
        $provider = $this->createProvider();

        $result = $provider->provide(new GetCollection(), [], ['filters' => ['type' => 'advanced']]);

        $this->assertCount(1, $result);
        $this->assertSame('advanced-profile', $result[0]->getIdentifier());
    }

    public function testProvideFiltersOutProfilesUserCannotSignWith(): void
    {
        $provider = $this->createProvider(canSign: false);

        $result = $provider->provide(new GetCollection(), [], ['filters' => ['type' => 'qualified']]);

        $this->assertSame([], $result);
    }

    public function testProvideWithoutFiltersThrows(): void
    {
        $provider = $this->createProvider();

        try {
            $provider->provide(new GetCollection(), [], []);
            $this->fail('Expected ApiError was not thrown');
        } catch (ApiError $e) {
            $this->assertSame(Response::HTTP_BAD_REQUEST, $e->getStatusCode());
        }
    }

    public function testProvideWithoutTypeThrows(): void
    {
        $provider = $this->createProvider();

        try {
            $provider->provide(new GetCollection(), [], ['filters' => []]);
            $this->fail('Expected ApiError was not thrown');
        } catch (ApiError $e) {
            $this->assertSame(Response::HTTP_BAD_REQUEST, $e->getStatusCode());
        }
    }

    public function testProvideWithUnsupportedTypeThrows(): void
    {
        $provider = $this->createProvider();

        try {
            $provider->provide(new GetCollection(), [], ['filters' => ['type' => 'bogus']]);
            $this->fail('Expected ApiError was not thrown');
        } catch (ApiError $e) {
            $this->assertSame(Response::HTTP_BAD_REQUEST, $e->getStatusCode());
        }
    }
}
