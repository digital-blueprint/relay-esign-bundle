<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\Commands;

use Dbp\Relay\EsignBundle\Api\Utils as UtilsAlias;
use Dbp\Relay\EsignBundle\PdfAsApi\PdfAsApi;
use Dbp\Relay\EsignBundle\PdfAsApi\SigningRequest;
use Dbp\Relay\EsignBundle\PdfAsApi\Utils;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Filesystem\Filesystem;

class QualifiedlySignCommand extends Command implements LoggerAwareInterface
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
        $this->setName('dbp:relay:esign:sign:qualified');
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
        $inputPaths = $input->getArgument('input-paths');
        $outputPaths = $input->getOption('output-paths');
        $profile = $input->getArgument('profile-id');
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

        $requests = [];
        $requestId = Utils::generateRequestId();
        foreach ($inputPaths as $index => $inputPath) {
            $outputPath = $outputPaths[$index];

            $inputData = @file_get_contents($inputPath);
            if ($inputData === false) {
                throw new \RuntimeException("Failed to read '$inputPath'");
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
                $parsedUserText = UtilsAlias::parseUserText($userText);
            } else {
                $parsedUserText = [];
            }

            $request = new SigningRequest($inputData, $profile, $requestId.'-'.$index, userText: $parsedUserText, userImageData: $userImageData, invisible: $invisible);
            $requests[] = $request;
        }

        if (count($requests) === 1) {
            $request = $requests[0];
            $outputPath = $outputPaths[0];
            $url = $this->api->createQualifiedSigningRequestRedirectUrl($request);
            $output->writeln("Open the following URL in your browser:\n    ".$url);
            $question = new Question('After confirming your identity please enter the code: ');
            $helper = $this->getHelper('question');
            assert($helper instanceof QuestionHelper);
            $sessionId = $helper->ask($input, $output, $question);

            $result = $this->api->fetchQualifiedlySignedDocument($sessionId);
            $filesystem = new Filesystem();
            $filesystem->dumpFile($outputPath, $result->getSignedPDF());
            $output->writeln("Created signed file '$outputPath'");
        } else {
            $url = $this->api->createQualifiedSigningRequestsRedirectUrl($requestId, $requests);
            $output->writeln("Open the following URL in your browser:\n    ".$url);
            $question = new Question('After confirming your identity please enter the code: ');
            $helper = $this->getHelper('question');
            assert($helper instanceof QuestionHelper);
            $token = $helper->ask($input, $output, $question);

            $responses = $this->api->fetchQualifiedlySignedDocuments($token);
            foreach ($responses as $index => $response) {
                $outputPath = $outputPaths[$index];

                $filesystem = new Filesystem();
                $filesystem->dumpFile($outputPath, $response->getSignedPDF());
                $output->writeln("Created signed file '$outputPath'");
            }
        }

        return 0;
    }
}
