<?php

namespace DBP\API\ESignBundle\PdfAsSoapClient;

use MyCLabs\Enum\Enum;

/**
 * @method static VerificationLevel __default()
 * @method static VerificationLevel intOnly()
 * @method static VerificationLevel full()
 *
 * @psalm-immutable
 */
class VerificationLevel extends Enum
{
    private const __default = 'intOnly';
    private const intOnly = 'intOnly';
    private const full = 'full';
}
