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
    private const __default = 'jks'; // @phpstan-ignore-line
    private const jks = 'jks'; // @phpstan-ignore-line
    private const moa = 'moa'; // @phpstan-ignore-line
    private const bku = 'bku'; // @phpstan-ignore-line
    private const mobilebku = 'mobilebku'; // @phpstan-ignore-line
    private const onlinebku = 'onlinebku'; // @phpstan-ignore-line
    private const sl20 = 'sl20'; // @phpstan-ignore-line
}
