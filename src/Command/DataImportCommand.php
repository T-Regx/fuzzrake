<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\DataImporter;
use App\Utils\DateTimeException;
use App\Utils\Import\Corrector;
use App\Utils\Import\ImportException;
use Doctrine\Common\Persistence\ObjectManager;
use InvalidArgumentException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;
use ZipArchive;

class DataImportCommand extends Command
{
    protected static $defaultName = 'app:data:import';

    /**
     * @var SymfonyStyle
     */
    private $io;

    /**
     * @var DataImporter
     */
    private $dataImporter;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    public function __construct(DataImporter $dataImporter, ObjectManager $objectManager)
    {
        $this->dataImporter = $dataImporter;
        $this->objectManager = $objectManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this->addOption('commit', null, null, 'Save changes in the database');
        $this->addOption('fix-mode', null, null, 'Show import command for fixes');
        $this->addArgument('import-file', InputArgument::REQUIRED, 'Import file path');
        $this->addArgument('corrections-file', InputArgument::REQUIRED, 'Corrections file path');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|void|null
     *
     * @throws ImportException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);

        $this->dataImporter->import($this->arrayFromCsvFile($input->getArgument('import-file')),
            $this->getImportCorrector($input->getArgument('corrections-file')),
            $this->io, $input->getOption('fix-mode'));

        if ($input->getOption('commit')) {
            $this->objectManager->flush();
            $this->io->success('Finished and saved');
        } else {
            $this->io->success('Finished without saving');
        }
    }

    private function arrayFromCsvFile(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new InvalidArgumentException("File '$filePath' does not exist");
        }

        if ('application/zip' == MimeTypeGuesser::getInstance()->guess($filePath)) {
            $fileContents = $this->readOnlyFileInZip($filePath);
        } else {
            $fileContents = file_get_contents($filePath);
        }

        return $this->arrayFromCsvString($fileContents);
    }

    private function arrayFromCsvString(string $csvString): array
    {
        $fileHandle = $this->getTmpFileWithContents($csvString);

        $result = $this->arrayFromCsvFileHandle($fileHandle);
        array_shift($result); // Drop header

        fclose($fileHandle);

        return $result;
    }

    private function arrayFromCsvFileHandle($fileHandle): array
    {
        $result = [];
        while (false !== ($cols = fgetcsv($fileHandle, 0, ',', '"', '"'))) {
            $result[] = $cols;
        }

        return $result;
    }

    private function readOnlyFileInZip(string $filePath): string
    {
        $zip = new ZipArchive();
        if (true !== $zip->open($filePath)) {
            throw new InvalidArgumentException("File '$filePath' is a corrupted ZIP archive");
        }

        if (1 !== $zip->numFiles) {
            $zip->close();
            throw new InvalidArgumentException("Unexpected number of files in the '$filePath' ZIP archive: {$zip->numFiles}");
        }

        $result = $zip->getFromIndex(0);
        $zip->close();

        if (false === $result) {
            throw new InvalidArgumentException("Failed reading file from the '$filePath' ZIP archive");
        }

        return $result;
    }

    private function getTmpFileWithContents(string $contents)
    {
        $handle = fopen('php://temp', 'r+');
        fwrite($handle, $contents);
        rewind($handle);

        return $handle;
    }

    /**
     * @param string $correctionsFilePath
     *
     * @return Corrector
     *
     * @throws ImportException
     */
    private function getImportCorrector(string $correctionsFilePath): Corrector
    {
        if (!file_exists($correctionsFilePath)) {
            throw new InvalidArgumentException("File '$correctionsFilePath' does not exist");
        }

        try {
            return new Corrector($correctionsFilePath);
        } catch (DateTimeException $e) {
            throw new ImportException('Failed initializing import corrector due to incorrect date format', 0, $e);
        }
    }
}
