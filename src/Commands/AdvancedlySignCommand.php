<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\Commands;

use Dbp\Relay\EsignBundle\Api\BaseSigningController;
use Dbp\Relay\EsignBundle\Helpers\Tools;
use Dbp\Relay\EsignBundle\Service\PdfAsApi;
use Dbp\Relay\EsignBundle\Service\SigningRequest;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class AdvancedlySignCommand extends Command implements LoggerAwareInterface
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
        $this->setName('dbp:relay:esign:sign:advanced');
        $this->setDescription('Sign one or more PDF files');
        $this->addArgument('profile-id', InputArgument::REQUIRED, 'Signing profile ID');
        $this->addArgument('input-paths', InputArgument::IS_ARRAY | InputArgument::REQUIRED, 'Input PDF file paths');
        $this->addOption('output-paths', 'o', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Output PDF file paths (matched by order)');
        $this->addOption('user-image-path', null, InputOption::VALUE_REQUIRED, 'Signature image path (PNG)');
        $this->addOption('user-text', null, InputOption::VALUE_REQUIRED, 'User text JSON');
        $this->addOption('invisible', null, InputOption::VALUE_NONE, 'Create an invisible signature');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $profile = $input->getArgument('profile-id');
        $inputPaths = $input->getArgument('input-paths');
        $outputPaths = $input->getOption('output-paths');
        $userImagePath = $input->getOption('user-image-path');
        $userText = $input->getOption('user-text');
        $invisible = $input->getOption('invisible');

        // Validate that we have the same number of inputs and outputs
        if (count($inputPaths) !== count($outputPaths)) {
            throw new \RuntimeException(sprintf(
                'Number of input files (%d) must match number of output files (%d)',
                count($inputPaths),
                count($outputPaths)
            ));
        }

        if ($userImagePath !== null) {
            $userImageData = @file_get_contents($userImagePath);
            if ($userImageData === false) {
                throw new \RuntimeException("Failed to read '$userImagePath'");
            }
        } else {
            $userImageData = null;
        }

        if ($userText !== null) {
            $userText = BaseSigningController::parseUserText($userText);
        } else {
            $userText = [];
        }

        $requests = [];
        $requestId = Tools::generateRequestId();
        foreach ($inputPaths as $index => $inputPath) {
            $subRequestId = $requestId.'-'.$index;

            $inputData = @file_get_contents($inputPath);
            if ($inputData === false) {
                throw new \RuntimeException("Failed to read '$inputPath'");
            }

            $request = new SigningRequest($inputData,
                $profile,
                $subRequestId,
                userText: $userText,
                userImageData: $userImageData,
                invisible: $invisible);
            $requests[] = $request;
        }

        $results = $this->api->advancedlySignPdfMultiple($requests);

        $filesystem = new Filesystem();
        foreach ($inputPaths as $index => $inputPath) {
            $outputPath = $outputPaths[$index];
            $result = $results[$index];

            $filesystem->dumpFile($outputPath, $result->getSignedPDF());
            $output->writeln("Created signed file '$outputPath' from '$inputPath'");
        }

        return 0;
    }
}
