<?php

declare(strict_types=1);

namespace App\Utils\IuSubmissions;

use App\Entity\Artisan;
use App\Utils\Data\Printer;
use App\Utils\DateTime\DateTimeUtils;
use App\Utils\StrUtils;

class Messaging
{
    private Printer $printer;
    private Manager $manager;

    public function __construct(Printer $printer, Manager $manager)
    {
        $this->printer = $printer;
        $this->manager = $manager;
    }

    public function reportIgnoredItem(ImportItem $item): void
    {
        $this->printer->writeln("{$item->getIdStrSafe()} ignored until {$this->manager->getIgnoredUntilDate($item)->format('Y-m-d')}");
    }

    public function reportMoreThanOneMatchedArtisans(Artisan $artisan, array $results): void
    {
        $namesList = implode(', ', array_map(function (Artisan $artisan) {
            return StrUtils::artisanNamesSafeForCli($artisan);
        }, $results));

        $this->printer->warning('Was looking for: '.StrUtils::artisanNamesSafeForCli($artisan).'. Found more than one: '.$namesList);
    }

    public function reportNewMaker(ImportItem $item): void
    {
        $monthLater = DateTimeUtils::getMonthLaterYmd();
        $makerId = $item->getMakerId();

        $this->printer->warning("New maker: {$item->getNamesStrSafe()}");
        $this->printer->writeln([
            Manager::CMD_MATCH_NAME.":$makerId:ABCDEFGHIJ:",
            Manager::CMD_ACK_NEW.":$makerId:",
            Manager::CMD_REJECT.":$makerId:{$item->getId()}:",
            Manager::CMD_IGNORE_UNTIL.":$makerId:{$item->getId()}:$monthLater:",
        ]);
    }

    public function reportChangedMakerId(ImportItem $item): void
    {
        $this->printer->warning($item->getNamesStrSafe().' changed their maker ID from '.$item->getOriginalEntity()->getMakerId()
            .' to '.$item->getFixedEntity()->getMakerId());
    }

    public function reportNewPasscode(ImportItem $item): void
    {
        $hash = $item->getId();
        $makerId = $item->getMakerId();

        $this->printer->warning("{$item->getNamesStrSafe()} set new passcode: {$item->getProvidedPasscode()}");
        $this->printer->writeln([
            Manager::CMD_SET_PIN.":$makerId:$hash:",
            Manager::CMD_REJECT.":$makerId:$hash:",
            '',
        ]);
        $this->emitDiffAndContactDetails($item);
    }

    public function reportInvalidPasscode(ImportItem $item, string $expectedPasscode): void
    {
        $weekLater = DateTimeUtils::getWeekLaterYmd();
        $makerId = $item->getMakerId();
        $hash = $item->getId();

        $this->printer->warning("{$item->getNamesStrSafe()} provided invalid passcode '{$item->getProvidedPasscode()}' (expected: '$expectedPasscode')");
        $this->printer->writeln([
            Manager::CMD_IGNORE_PIN.":$makerId:$hash:",
            Manager::CMD_REJECT.":$makerId:$hash:",
            Manager::CMD_SET_PIN.":$makerId:$hash:",
            Manager::CMD_IGNORE_UNTIL.":$makerId:$hash:$weekLater:",
            '',
        ]);
        $this->emitDiffAndContactDetails($item);
    }

    public function reportUpdates(ImportItem $item): void
    {
        if (!empty($item->getReplaced())) {
            $this->printer->writeln([
                $item->getIdStrSafe().' replaced',
                implode(" replaced\n", $item->getReplaced()),
                '',
            ]);
        }
    }

    private function emitDiffAndContactDetails(ImportItem $item): void
    {
        $this->printer->writeln($item->getDiff()->getDescriptionCliSafe());
        $this->printer->writeln('Contact info: '
            .($item->getOriginalEntity()->getContactAllowed() ?: '-')
            .'/'.$item->getFixedEntity()->getContactAllowed()
            .' '.($item->getOriginalEntity()->getContactInfoOriginal() ?: '?'));
    }

    public function reportValid(ImportItem $item): void
    {
        if ($item->getDiff()->hasAnythingChanged()) {
            $this->printer->success('Accepted for import');
        }
    }
}
