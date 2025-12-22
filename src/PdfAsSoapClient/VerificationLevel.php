<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\PdfAsSoapClient;

enum VerificationLevel: string
{
    case intOnly = 'intOnly';
    case full = 'full';
}
