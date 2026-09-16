<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\Tests\Configuration;

use Dbp\Relay\EsignBundle\Configuration\QualifiedProfile;
use PHPUnit\Framework\TestCase;

class ProfileTest extends TestCase
{
    public function testQualifiedProfileReturnsFalse(): void
    {
        $profile = new QualifiedProfile();
        $this->assertFalse($profile->getIncludeUsername());
        $this->assertFalse($profile->getTitleInline());
    }
}
