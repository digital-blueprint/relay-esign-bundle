<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\Commands;

use Dbp\Relay\EsignBundle\Controller\BaseSigningController;
use Dbp\Relay\EsignBundle\Helpers\Tools;
use Dbp\Relay\EsignBundle\Service\SignatureProviderInterface;
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

    public function __construct(SignatureProviderInterface $api)
    {
        parent::__construct();
        $this->api = $api;
    }

    protected function configure(): void
    {
        $this->setName('dbp:relay:esign:sign:qualified');
        $this->setDescription('Sign a PDF file');
        $this->addArgument('profile-id', InputArgument::REQUIRED, 'Signing profile ID');
        $this->addArgument('input-path', InputArgument::REQUIRED, 'Input PDF file path');
        $this->addArgument('output-path', InputArgument::REQUIRED, 'Output PDF file path');
        $this->addOption('user-image-path', null, InputOption::VALUE_REQUIRED, 'Signature image path (PNG)');
        $this->addOption('user-text', null, InputOption::VALUE_REQUIRED, 'User text JSON');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $requestId = Tools::generateRequestId();
        $inputPath = $input->getArgument('input-path');
        $outputPath = $input->getArgument('output-path');
        $profile = $input->getArgument('profile-id');
        $userImagePath = $input->getOption('user-image-path');
        $userText = $input->getOption('user-text');

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
            $userText = BaseSigningController::parseUserText($userText);
        } else {
            $userText = [];
        }

        $url = $this->api->createQualifiedSigningRequestRedirectUrl($inputData, $profile, $requestId, userText: $userText, userImageData: $userImageData);
        $output->writeln("Open the following URL in your browser:\n    ".$url);
        $question = new Question('After confirming your identity please enter the session ID: ');
        $helper = $this->getHelper('question');
        assert($helper instanceof QuestionHelper);
        $sessionId = $helper->ask($input, $output, $question);

        $signedData = $this->api->fetchQualifiedlySignedDocument($sessionId);
        $filesystem = new Filesystem();
        $filesystem->dumpFile($outputPath, $signedData);
        $output->writeln("Created signed file '$outputPath'");

        return 0;
    }
}
