<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\PdfAsApi;

class SignatureBlockPosition
{
    public const AUTO = 'auto';
    public const PAGE_NEW = 'new';
    public const PAGE_LAST = 'last';

    /**
     * @param string|float $x            X-coordinate of the top-left corner. Use 'auto' for automatic positioning.
     * @param string|float $y            Y-coordinate of the top-left corner. Use 'auto' for automatic positioning.
     * @param string|float $width        Width of the signature block. Must be > 0 when numeric.
     * @param string|int   $page         Page to place the signature on. 'auto', 'new', 'last', or a page number.
     * @param float        $footerHeight Y-offset for footer in PDF User Space units. Only used when yPosition is 'auto'.
     * @param float        $rotation     rotation in degrees (0-360)
     */
    public function __construct(
        public readonly string|float $x = self::AUTO,
        public readonly string|float $y = self::AUTO,
        public readonly string|float $width = self::AUTO,
        public readonly string|int $page = self::AUTO,
        public readonly float $footerHeight = 0.0,
        public readonly float $rotation = 0.0,
    ) {
        $this->validate();
    }

    /**
     * Serializes to the PDF-AS position string format.
     * Example: x:auto;y:100.5;w:auto;p:last;f:20;r:90.
     */
    public function toPdfAsFormat(): string
    {
        return implode(';', [
            'x:'.$this->x,
            'y:'.$this->y,
            'w:'.$this->width,
            'p:'.$this->page,
            'f:'.$this->footerHeight,
            'r:'.$this->rotation,
        ]);
    }

    private function validate(): void
    {
        if (is_string($this->x) && $this->x !== self::AUTO) {
            throw new \InvalidArgumentException('x position must be "auto" or a numeric value.');
        }

        if (is_string($this->y) && $this->y !== self::AUTO) {
            throw new \InvalidArgumentException('y position must be "auto" or a numeric value.');
        }

        if (is_string($this->width) && $this->width !== self::AUTO) {
            throw new \InvalidArgumentException('Width must be "auto" or a numeric value.');
        }

        if (is_float($this->width) && $this->width <= 0) {
            throw new \InvalidArgumentException('Numeric width must be greater than 0.');
        }

        $allowedPageValues = [self::AUTO, self::PAGE_NEW, self::PAGE_LAST];
        if (is_string($this->page) && !in_array($this->page, $allowedPageValues, true)) {
            throw new \InvalidArgumentException(
                sprintf('Page must be one of "%s" or a page number.', implode('", "', $allowedPageValues))
            );
        }

        if (is_int($this->page) && $this->page < 1) {
            throw new \InvalidArgumentException('Page number must be greater than 0.');
        }

        if ($this->footerHeight < 0) {
            throw new \InvalidArgumentException('Footer height must be a non-negative number.');
        }

        if ($this->rotation < 0 || $this->rotation > 360) {
            throw new \InvalidArgumentException('Rotation must be between 0 and 360 degrees.');
        }
    }
}
