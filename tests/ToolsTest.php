<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\Tests;

use Dbp\Relay\EsignBundle\Helpers\Tools;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Stopwatch\Stopwatch;

class ToolsTest extends TestCase
{
    public function testGetUriWithPort()
    {
        $this->assertEquals('http://tugraz.at:80', Tools::getUriWithPort('http://tugraz.at'));
        $this->assertEquals('http://tugraz.at:80/', Tools::getUriWithPort('http://tugraz.at/'));
        $this->assertEquals('https://tugraz.at:443', Tools::getUriWithPort('https://tugraz.at'));
        $this->assertEquals('https://tugraz.at:123', Tools::getUriWithPort('https://tugraz.at:123'));
        $this->assertEquals('https://tugraz.at:123/', Tools::getUriWithPort('https://tugraz.at:123/'));
        $this->assertEquals('http://tugraz.at:80/foo/bar', Tools::getUriWithPort('http://tugraz.at/foo/bar'));
        $this->assertEquals('https://tugraz.at:123/foo/bar', Tools::getUriWithPort('https://tugraz.at:123/foo/bar'));
        $this->assertEquals('https://tugraz.at:443/static/error.html',
            Tools::getUriWithPort('https://tugraz.at/static/error.html'));
    }

    public function testGetDataURI()
    {
        $this->assertSame('data:text/plain;base64,Zm9vYmFy', Tools::getDataURI('foobar', 'text/plain'));
    }

    public function testGenerateRequestId()
    {
        $this->assertNotSame(Tools::generateRequestId(), Tools::generateRequestId());
    }

    public function testGenerateSignedFileName()
    {
        $this->assertSame('foo-sig.pdf', Tools::generateSignedFileName('foo.pdf'));
        $this->assertSame('-sig.pdf', Tools::generateSignedFileName('.pdf'));
        $this->assertSame('-sig.pdf', Tools::generateSignedFileName('-sig.pdf'));
        $this->assertSame('-sig', Tools::generateSignedFileName(''));
        $this->assertSame('foo.tar-sig.gz', Tools::generateSignedFileName('foo.tar.gz'));
        $this->assertSame('foo-sig.sig', Tools::generateSignedFileName('foo.sig'));
    }

    public function testCreateStopwatchMiddleware()
    {
        $stopwatch = new Stopwatch();
        $middleware = Tools::createStopwatchMiddleware($stopwatch, 'foo', 'bar');
        $stack = new HandlerStack(new MockHandler([new Response(200, ['Content-Type' => 'application/json'], '{}')]));
        $stack->push($middleware);
        $client = new Client(['handler' => $stack]);
        $client->get('https://this.does.not.exist');
        $events = array_values($stopwatch->getSections())[0]->getEvents();
        $this->assertCount(1, $events);
        $this->assertArrayHasKey('foo(GET this.does.not.exist)', $events);
    }
}
