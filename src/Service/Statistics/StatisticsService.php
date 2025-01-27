<?php

declare(strict_types=1);

namespace App\Service\Statistics;

use App\Repository\ArtisanRepository;
use App\Repository\ArtisanVolatileDataRepository;
use App\Utils\DateTime\DateTimeException;
use App\Utils\DateTime\UtcClock;
use Doctrine\ORM\UnexpectedResultException;

class StatisticsService
{
    public function __construct(
        private readonly ArtisanRepository $artisanRepository,
        private readonly ArtisanVolatileDataRepository $avdRepository,
    ) {
    }

    public function getMainPageStats(): MainPageStats
    {
        try {
            $lastDataUpdateTimeUtc = $this->avdRepository->getLastCsUpdateTime();
        } catch (UnexpectedResultException|DateTimeException) {
            $lastDataUpdateTimeUtc = null;
        }

        try {
            $activeArtisansCount = $this->artisanRepository->countActive();
        } catch (UnexpectedResultException) {
            $activeArtisansCount = null;
        }

        try {
            $countryCount = $this->artisanRepository->getDistinctCountriesCount();
        } catch (UnexpectedResultException) {
            $countryCount = null;
        }

        try {
            $lastSystemUpdateTimeUtc = UtcClock::at(shell_exec('TZ=UTC git log -n1 --format=%cd --date=local'));
        } catch (DateTimeException) {
            $lastSystemUpdateTimeUtc = null;
        }

        return new MainPageStats(
            $activeArtisansCount,
            $countryCount,
            $lastDataUpdateTimeUtc,
            $lastSystemUpdateTimeUtc,
        );
    }
}
