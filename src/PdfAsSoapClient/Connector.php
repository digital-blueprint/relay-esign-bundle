<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\PdfAsSoapClient;

use MyCLabs\Enum\Enum;

/**
 * @method static Connector __default()
 * @method static Connector jks()
 * @method static Connector moa()
 * @method static Connector bku()
 * @method static Connector mobilebku()
 * @method static Connector onlinebku()
 * @method static Connector sl20()
 *
 * @psalm-immutable
 */
class Connector extends Enum
{
    private const __default = 'jks';
    private const jks = 'jks';
    private const moa = 'moa';
    private const bku = 'bku';
    private const mobilebku = 'mobilebku';
    private const onlinebku = 'onlinebku';
    private const sl20 = 'sl20';
}
