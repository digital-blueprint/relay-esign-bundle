<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\Tests\PdfAsApi;

use Dbp\Relay\EsignBundle\Configuration\BundleConfig;
use Dbp\Relay\EsignBundle\PdfAsApi\PdfAsApi;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProfileTextConfigurationTest extends TestCase
{
    public function testProfileCheckReportsMissingSystemTextForVisibleProfile(): void
    {
        $config = new BundleConfig(['advanced_signature' => [
            'profiles' => [
                ['name' => 'invisible', 'invisible' => true, 'include_username' => true],
                ['name' => 'visible', 'invisible' => false, 'include_username' => true],
            ],
        ]]);
        $api = new PdfAsApi(new Stopwatch(), $this->createMock(UrlGeneratorInterface::class), $config, $this->createMock(TranslatorInterface::class));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Profile "visible": system_text not available/implemented for this profile');
        $api->checkPdfAsProfiles();
    }

    public function testProfileCheckReportsMissingUserTextForVisibleProfile(): void
    {
        $config = new BundleConfig(['qualified_signature' => [
            'profiles' => [
                ['name' => 'invisible', 'invisible' => true, 'allow_annotations' => true],
                ['name' => 'visible', 'invisible' => false, 'allow_annotations' => true],
            ],
        ]]);
        $api = new PdfAsApi(new Stopwatch(), $this->createMock(UrlGeneratorInterface::class), $config, $this->createMock(TranslatorInterface::class));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Profile "visible": user_text not available/implemented for this profile');
        $api->checkPdfAsProfiles();
    }
}
