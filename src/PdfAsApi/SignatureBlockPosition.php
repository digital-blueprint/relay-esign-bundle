<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\PdfAsApi;

class SignatureBlockPosition
{
    public const PROFILE_DEFAULT = null;
    public const AUTO = 'auto';
    public const PAGE_NEW = 'new';
    public const PAGE_LAST = 'last';

    /**
     * @param string|float|null $x            X-coordinate of the top-left corner. Use null for the PDF-AS profile default or 'auto' for automatic positioning.
     * @param string|float|null $y            Y-coordinate of the top-left corner. Use null for the PDF-AS profile default or 'auto' for automatic positioning.
     * @param string|float|null $width        Width of the signature block. Must be > 0 when numeric.
     * @param string|int|null   $page         Page to place the signature on. null, 'auto', 'new', 'last', or a page number.
     * @param float|null        $footerHeight Y-offset for footer in PDF User Space units. Only used when yPosition is 'auto'.
     * @param float|null        $rotation     rotation in degrees (0-360)
     */
    public function __construct(
        public readonly string|float|null $x = self::PROFILE_DEFAULT,
        public readonly string|float|null $y = self::PROFILE_DEFAULT,
        public readonly string|float|null $width = self::PROFILE_DEFAULT,
        public readonly string|int|null $page = self::PROFILE_DEFAULT,
        public readonly ?float $footerHeight = self::PROFILE_DEFAULT,
        public readonly ?float $rotation = self::PROFILE_DEFAULT,
    ) {
        $this->validate();
    }

    /**
     * Serializes to the PDF-AS position string format, or null if all values use the profile default.
     * Example: x:auto;y:100.5;w:auto;p:last;f:20;r:90.
     */
    public function toPdfAsFormat(): ?string
    {
        $parts = [];
        foreach ([
            'x' => $this->x,
            'y' => $this->y,
            'w' => $this->width,
            'p' => $this->page,
            'f' => $this->footerHeight,
            'r' => $this->rotation,
        ] as $key => $value) {
            if ($value !== null) {
                $parts[] = $key.':'.$value;
            }
        }

        return $parts === [] ? null : implode(';', $parts);
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

        if ($this->footerHeight !== null && $this->footerHeight < 0) {
            throw new \InvalidArgumentException('Footer height must be a non-negative number.');
        }

        if ($this->rotation !== null && ($this->rotation < 0 || $this->rotation > 360)) {
            throw new \InvalidArgumentException('Rotation must be between 0 and 360 degrees.');
        }
    }
}
