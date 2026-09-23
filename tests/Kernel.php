<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\Tests;

use Dbp\Relay\CoreBundle\TestUtils\CoreTestKernelTrait;
use Dbp\Relay\EsignBundle\DbpRelayEsignBundle;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use CoreTestKernelTrait;

    protected function registerAdditionalBundles(): iterable
    {
        yield new DbpRelayEsignBundle();
    }
}
