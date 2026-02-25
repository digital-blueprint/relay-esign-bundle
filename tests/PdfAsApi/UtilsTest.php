<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\Tests\PdfAsApi;

use Dbp\Relay\EsignBundle\PdfAsApi\Utils;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Stopwatch\Stopwatch;

class UtilsTest extends TestCase
{
    public function testGetUriWithPort()
    {
        $this->assertEquals('http://tugraz.at:80', Utils::getUriWithPort('http://tugraz.at'));
        $this->assertEquals('http://tugraz.at:80/', Utils::getUriWithPort('http://tugraz.at/'));
        $this->assertEquals('https://tugraz.at:443', Utils::getUriWithPort('https://tugraz.at'));
        $this->assertEquals('https://tugraz.at:123', Utils::getUriWithPort('https://tugraz.at:123'));
        $this->assertEquals('https://tugraz.at:123/', Utils::getUriWithPort('https://tugraz.at:123/'));
        $this->assertEquals('http://tugraz.at:80/foo/bar', Utils::getUriWithPort('http://tugraz.at/foo/bar'));
        $this->assertEquals('https://tugraz.at:123/foo/bar', Utils::getUriWithPort('https://tugraz.at:123/foo/bar'));
        $this->assertEquals('https://tugraz.at:443/static/error.html',
            Utils::getUriWithPort('https://tugraz.at/static/error.html'));
    }

    public function testGenerateRequestId()
    {
        $this->assertNotSame(Utils::generateRequestId(), Utils::generateRequestId());
    }

    public function testCreateStopwatchMiddleware()
    {
        $stopwatch = new Stopwatch();
        $middleware = Utils::createStopwatchMiddleware($stopwatch, 'foo', 'bar');
        $stack = new HandlerStack(new MockHandler([new Response(200, ['Content-Type' => 'application/json'], '{}')]));
        $stack->push($middleware);
        $client = new Client(['handler' => $stack]);
        $client->get('https://this.does.not.exist');
        $events = array_values($stopwatch->getSections())[0]->getEvents();
        $this->assertCount(1, $events);
        $this->assertArrayHasKey('foo(GET this.does.not.exist)', $events);
    }
}
