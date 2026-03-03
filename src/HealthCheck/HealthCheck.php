<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\HealthCheck;

use Dbp\Relay\CoreBundle\HealthCheck\CheckInterface;
use Dbp\Relay\CoreBundle\HealthCheck\CheckOptions;
use Dbp\Relay\CoreBundle\HealthCheck\CheckResult;
use Dbp\Relay\EsignBundle\PdfAsApi\PdfAsApi;

class HealthCheck implements CheckInterface
{
    private $api;

    public function __construct(PdfAsApi $api)
    {
        $this->api = $api;
    }

    public function getName(): string
    {
        return 'esign';
    }

    private function checkMethod(string $description, callable $func, ...$args): CheckResult
    {
        $result = new CheckResult($description);
        try {
            $func(...$args);
        } catch (\Throwable $e) {
            $result->set(CheckResult::STATUS_FAILURE, $e->getMessage(), ['exception' => $e]);

            return $result;
        }
        $result->set(CheckResult::STATUS_SUCCESS);

        return $result;
    }

    public function check(CheckOptions $options): array
    {
        return [
            $this->checkMethod('Check if we can reach the pdf-as-web SOAP interface', [$this->api, 'checkPdfAsConnection']),
            $this->checkMethod('Check if we can reach the pdf-as-web HTTP interface', [$this->api, 'checkPdfAsHttpConnection']),
            $this->checkMethod('Check if all profiles are configured in pdf-as', [$this->api, 'checkPdfAsProfiles']),
            $this->checkMethod('Check if all advanced profiles can sign successfully', [$this->api, 'checkPdfAsCanSign'], __DIR__.DIRECTORY_SEPARATOR.'test.pdf'),
        ];
    }
}
