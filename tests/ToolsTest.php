<?php

namespace DBP\API\ESignBundle\Tests;

use DBP\API\ESignBundle\Helpers\Tools;
use PHPUnit\Framework\TestCase;

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
}
