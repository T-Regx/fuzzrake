<?php

declare(strict_types=1);

namespace App\Command;

use App\Tasks\StatusTracker\TaskIOFactory;
use App\Tracker\TrackerException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand('app:status-tracker:run')]
class StatusTrackerRunCommand extends Command
{
    private const OPT_REFETCH = 'refetch';
    private const OPT_COMMIT = 'commit';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TaskIOFactory $factory,
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->addOption(self::OPT_REFETCH, null, null, 'Refresh cache (re-fetch pages)')
            ->addOption(self::OPT_COMMIT, null, null, 'Save changes in the database')
        ;
    }

    /**
     * @throws TrackerException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $task = $this->factory->get(
            $input->getOption(self::OPT_REFETCH),
            $input->getOption(self::OPT_COMMIT),
            $io,
        );

        $task->performUpdates();

        if ($input->getOption(self::OPT_COMMIT)) {
            $this->entityManager->flush();
            $io->success('Finished and saved');
        } else {
            $io->success('Finished without saving');
        }

        return 0;
    }
}