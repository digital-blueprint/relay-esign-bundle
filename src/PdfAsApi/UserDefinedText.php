<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\PdfAsApi;

class UserDefinedText
{
    private $description;
    private $value;

    private $type;

    public static $TYPE_TEXT = 'text';
    public static $TYPE_IMAGE = 'image';

    public function __construct(string $description, string $value, ?string $type = null)
    {
        $this->description = $description;
        $this->value = $value;

        if (is_null($type)) {
            $this->type = self::$TYPE_TEXT;
        } else {
            $this->type = $type;
        }
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getType(): string
    {
        return $this->type;
    }
}
