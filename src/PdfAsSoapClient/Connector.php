<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\PdfAsSoapClient;

enum Connector: string
{
    case jks = 'jks';
    case moa = 'moa';
    case bku = 'bku';
    case mobilebku = 'mobilebku';
    case onlinebku = 'onlinebku';
    case sl20 = 'sl20';
}
