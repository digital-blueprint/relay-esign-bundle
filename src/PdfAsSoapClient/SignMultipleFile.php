<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\PdfAsSoapClient;

class SignMultipleFile
{
    public function __construct(
        protected string $inputData,
        protected string $fileName,
        protected ?string $position = null,
        protected ?string $qrCodeContent = null,
        protected ?string $profile = null,
    ) {
    }

    public function getInputData(): string
    {
        return $this->inputData;
    }

    public function setInputData(string $inputData): void
    {
        $this->inputData = $inputData;
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function setFileName(string $fileName): void
    {
        $this->fileName = $fileName;
    }

    public function getPosition(): ?string
    {
        return $this->position;
    }

    public function setPosition(?string $position): void
    {
        $this->position = $position;
    }

    public function getQrCodeContent(): ?string
    {
        return $this->qrCodeContent;
    }

    public function setQrCodeContent(?string $qrCodeContent): void
    {
        $this->qrCodeContent = $qrCodeContent;
    }

    public function getProfile(): ?string
    {
        return $this->profile;
    }

    public function setProfile(?string $profile): void
    {
        $this->profile = $profile;
    }
}
