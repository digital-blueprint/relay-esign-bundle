<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\Tests\PdfAsApi;

use Dbp\Relay\EsignBundle\PdfAsApi\SignatureBlockPosition;
use PHPUnit\Framework\TestCase;

class SignatureBlockPositionTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $pos = new SignatureBlockPosition();

        $this->assertSame(SignatureBlockPosition::AUTO, $pos->x);
        $this->assertSame(SignatureBlockPosition::AUTO, $pos->y);
        $this->assertSame(SignatureBlockPosition::AUTO, $pos->width);
        $this->assertSame(SignatureBlockPosition::AUTO, $pos->page);
        $this->assertSame(0.0, $pos->footerHeight);
        $this->assertSame(0.0, $pos->rotation);
    }

    public function testToPdfAsStringWithDefaults(): void
    {
        $pos = new SignatureBlockPosition();
        $this->assertSame('x:auto;y:auto;w:auto;p:auto;f:0;r:0', $pos->toPdfAsFormat());
    }

    public function testToPdfAsStringWithNumericValues(): void
    {
        $pos = new SignatureBlockPosition(
            x: 10.5,
            y: 20.0,
            width: 100.0,
            page: 2,
            footerHeight: 15.0,
            rotation: 90.0,
        );
        $this->assertSame('x:10.5;y:20;w:100;p:2;f:15;r:90', $pos->toPdfAsFormat());
    }

    public function testToPdfAsStringWithPageConstants(): void
    {
        $pos = new SignatureBlockPosition(page: SignatureBlockPosition::PAGE_NEW);
        $this->assertStringContainsString('p:new', $pos->toPdfAsFormat());

        $pos = new SignatureBlockPosition(page: SignatureBlockPosition::PAGE_LAST);
        $this->assertStringContainsString('p:last', $pos->toPdfAsFormat());
    }

    public function testValidNumericPositions(): void
    {
        $pos = new SignatureBlockPosition(x: 0.0, y: 0.0);
        $this->assertSame(0.0, $pos->x);
        $this->assertSame(0.0, $pos->y);
    }

    public function testValidNumericPageNumber(): void
    {
        $pos = new SignatureBlockPosition(page: 1);
        $this->assertSame(1, $pos->page);
    }

    public function testInvalidXPosition(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('x position');
        new SignatureBlockPosition(x: 'invalid');
    }

    public function testInvalidYPosition(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('y position');
        new SignatureBlockPosition(y: 'invalid');
    }

    public function testInvalidWidthString(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Width');
        new SignatureBlockPosition(width: 'invalid');
    }

    public function testInvalidWidthZero(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Numeric width must be greater than 0');
        new SignatureBlockPosition(width: 0.0);
    }

    public function testInvalidWidthNegative(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Numeric width must be greater than 0');
        new SignatureBlockPosition(width: -1.0);
    }

    public function testInvalidPageString(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Page must be one of');
        new SignatureBlockPosition(page: 'invalid');
    }

    public function testInvalidPageNumberZero(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Page number must be greater than 0');
        new SignatureBlockPosition(page: 0);
    }

    public function testInvalidPageNumberNegative(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Page number must be greater than 0');
        new SignatureBlockPosition(page: -1);
    }

    public function testInvalidFooterHeightNegative(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Footer height must be a non-negative number');
        new SignatureBlockPosition(footerHeight: -0.1);
    }

    public function testInvalidRotationNegative(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Rotation must be between 0 and 360');
        new SignatureBlockPosition(rotation: -1.0);
    }

    public function testInvalidRotationAbove360(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Rotation must be between 0 and 360');
        new SignatureBlockPosition(rotation: 360.1);
    }

    public function testValidBoundaryRotation(): void
    {
        $posZero = new SignatureBlockPosition(rotation: 0.0);
        $this->assertSame(0.0, $posZero->rotation);

        $pos360 = new SignatureBlockPosition(rotation: 360.0);
        $this->assertSame(360.0, $pos360->rotation);
    }

    public function testValidBoundaryFooterHeight(): void
    {
        $pos = new SignatureBlockPosition(footerHeight: 0.0);
        $this->assertSame(0.0, $pos->footerHeight);
    }
}
