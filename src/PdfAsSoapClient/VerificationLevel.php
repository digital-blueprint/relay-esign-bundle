<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\PdfAsSoapClient;

use MyCLabs\Enum\Enum;

/**
 * @method static VerificationLevel __default()
 * @method static VerificationLevel intOnly()
 * @method static VerificationLevel full()
 *
 * @extends Enum<string>
 */
class VerificationLevel extends Enum
{
    private const __default = 'intOnly'; // @phpstan-ignore-line
    private const intOnly = 'intOnly'; // @phpstan-ignore-line
    private const full = 'full'; // @phpstan-ignore-line
}
