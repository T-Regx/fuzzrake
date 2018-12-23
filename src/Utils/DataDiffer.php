<?php

declare(strict_types=1);

namespace App\Utils;

use App\Entity\Artisan;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Style\SymfonyStyle;

class DataDiffer
{
    /**
     * @var SymfonyStyle
     */
    private $io;

    public function __construct(SymfonyStyle $io)
    {
        $this->io = $io;

        $this->io->getFormatter()->setStyle('a', new OutputFormatterStyle('green'));
        $this->io->getFormatter()->setStyle('d', new OutputFormatterStyle('red'));
    }

    public function showDiff(Artisan $old, Artisan $new): void
    {
        $nameShown = false;

        foreach (ArtisanMetadata::getModelFieldNames() as $fieldName) {
            $this->showSingleFieldDiff($nameShown, $fieldName, $old, $new);
        }
    }

    private function showSingleFieldDiff(bool &$nameShown, string $fieldName, Artisan $old, Artisan $new): void
    {
        $newVal = $new->get($fieldName);
        $oldVal = $old->get($fieldName);

        if ($oldVal !== $newVal) {
            $this->showNameFirstTime($nameShown, $old, $new);

            if (strpos($oldVal, "\n") !== false || strpos($newVal, "\n") !== false) {
                $this->showListDiff($fieldName, $oldVal, $newVal);
            } else {
                $this->showSingleValueDiff($fieldName, $oldVal, $newVal);
            }

            $this->io->writeln('');
        }
    }

    private function showNameFirstTime(bool &$nameShown, Artisan $old, Artisan $new): void
    {
        if (!$nameShown) {
            $names = array_unique(array_filter([
                $old->getName(),
                $old->getFormerly(),
                $new->getName(),
                $new->getFormerly(),
            ]));

            $this->io->section(implode(' / ', $names));

            $nameShown = true;
        }
    }

    private function showListDiff(string $fieldName, $oldVal, $newVal): void
    {
        $oldValItems = explode("\n", $oldVal);
        $newValItems = explode("\n", $newVal);
        $allItems = array_unique(array_filter(array_merge($oldValItems, $newValItems)));
        sort($allItems);

        foreach ($allItems as &$item) {
            if (in_array($item, $oldValItems)) {
                if (!in_array($item, $newValItems)) {
                    $item = "<d>$item</>";
                }
            } else {
                $item = "<a>$item</>";
            }
        }

        $this->io->writeln("$fieldName: " . join('|', $allItems));
    }

    private function showSingleValueDiff(string $fieldName, $oldVal, $newVal): void
    {
        if ($oldVal) {
            $this->io->writeln("$fieldName: <d>{$oldVal}</>");
        }

        if ($newVal) {
            $this->io->writeln("$fieldName: <a>{$newVal}</>");
        }
    }
}
