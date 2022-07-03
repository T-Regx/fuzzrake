<?php

declare(strict_types=1);

namespace App\Tasks;

use App\Utils\Data\FdvFactory;
use App\Utils\Data\Manager;
use App\Utils\Data\Printer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DataImportFactory
{
    public function __construct(
        private readonly EntityManagerInterface $objectManager,
        private readonly FdvFactory $fdvFactory,
    ) {
    }

    public function get(Manager $importManager, SymfonyStyle $io, bool $showAllFixCmds): DataImport
    {
        $printer = new Printer($io);

        return new DataImport($this->objectManager, $importManager, $printer, $this->fdvFactory->create($printer),
            $showAllFixCmds);
    }
}
