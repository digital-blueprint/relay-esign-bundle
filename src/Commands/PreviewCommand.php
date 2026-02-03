<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\Commands;

use Dbp\Relay\EsignBundle\Service\PdfAsApi;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class PreviewCommand extends Command implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected $api;

    public function __construct(PdfAsApi $api)
    {
        parent::__construct();
        $this->api = $api;
    }

    protected function configure(): void
    {
        $this->setName('dbp:relay:esign:preview');
        $this->setDescription('Create a preview image of the signature block for a given profile');
        $this->addArgument('profile-id', InputArgument::REQUIRED, 'Signing profile ID');
        $this->addArgument('output-path', InputArgument::REQUIRED, 'Output PNG file path');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $outputPath = $input->getArgument('output-path');
        $profile = $input->getArgument('profile-id');

        $resolution = 72 * 4;
        $data = $this->api->createPreviewImage($profile, $resolution);

        $imagesize = getimagesizefromstring($data);
        if ($imagesize === false) {
            throw new \RuntimeException('Invalid image data');
        }
        $width = (int) $imagesize[0];
        $height = (int) $imagesize[1];
        $type = $imagesize[2];
        if ($type !== \IMAGETYPE_PNG) {
            throw new \RuntimeException('Invalid image data');
        }
        $widthPoints = (int) round(($width * 72) / $resolution);
        $heightPoints = (int) round(($height * 72) / $resolution);

        $filesystem = new Filesystem();
        $filesystem->dumpFile($outputPath, $data);
        $output->writeln("Created preview image for '$profile': '$outputPath' ({$width}x{$height}px, {$widthPoints}x{$heightPoints}pt)");

        return 0;
    }
}
