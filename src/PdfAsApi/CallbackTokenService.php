<?php

namespace Dbp\Relay\EsignBundle\PdfAsApi;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class CallbackTokenService
{
    public function __construct(
        #[Autowire(param: 'kernel.secret')]
        private string $secret
    ) {}

    public function generateToken(string $id, int $expires): string
    {
        return hash_hmac('sha256', $id . $expires, $this->secret);
    }

    public function verifyToken(string $token, string $id, int $expires): void
    {
        $expected = $this->generateToken($id, $expires);

        if (!hash_equals($expected, $token)) {
            throw new AccessDeniedHttpException('Invalid token');
        }

        if (time() > $expires) {
            throw new AccessDeniedHttpException('Token expired');
        }
    }

    public function getExpires(string $isoDuration) {
        $interval = new \DateInterval($isoDuration);
        $dateTime = new \DateTimeImmutable();
        return  $dateTime->add($interval)->getTimestamp();
    }
}