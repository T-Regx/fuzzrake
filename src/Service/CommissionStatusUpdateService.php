<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\Artisan;
use App\Repository\ArtisanRepository;
use App\Utils\CommissionsStatusParser;
use App\Utils\CommissionsStatusParserException;
use App\Utils\WebsiteInfo;
use Doctrine\Common\Persistence\ObjectManager;
use Exception;
use Symfony\Component\Console\Style\StyleInterface;

class CommissionStatusUpdateService
{
    /**
     * @var ArtisanRepository
     */
    private $artisanRepository;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var UrlFetcher
     */
    private $urlFetcher;

    /**
     * @var StyleInterface
     */
    private $style;

    /**
     * @var CommissionsStatusParser
     */
    private $commissionsStatusParser;

    public function __construct(ArtisanRepository $artisanRepository, ObjectManager $objectManager, UrlFetcher $urlFetcher)
    {
        $this->objectManager = $objectManager;
        $this->artisanRepository = $artisanRepository;
        $this->urlFetcher = $urlFetcher;
        $this->commissionsStatusParser = new CommissionsStatusParser();
    }

    /**
     * @param StyleInterface $style
     * @param bool $refresh
     * @param bool $dryRun
     * @throws UrlFetcherException
     */
    public function updateAll(StyleInterface $style, bool $refresh, bool $dryRun)
    {
        $this->style = $style;

        $artisans = $this->getArtisans();
        $this->prefetchStatusWebpages($artisans, $refresh);
        $this->updateArtisans($artisans);

        if (!$dryRun) {
            $this->objectManager->flush();
        }
    }

    private function updateArtisans(array $artisans): void
    {
        foreach ($artisans as $artisan) {
            if ($this->canAutoUpdate($artisan)) {
                try {
                    $this->updateArtisan($artisan);
                } catch (Exception $exception) {
                    $this->style->error("Failed: {$artisan->getName()} ({$artisan->getCommisionsQuotesCheckUrl()})");
                    $this->style->text($exception);
                }
            }
        }
    }

    private function updateArtisan(Artisan $artisan): void
    {
        try {
            $webpageContents = $this->fetchWebpageContents($artisan->getCommisionsQuotesCheckUrl());
            $status = $this->commissionsStatusParser->areCommissionsOpen($webpageContents);
        } catch (UrlFetcherException|CommissionsStatusParserException $exception) {
            $this->style->note("Failed: {$artisan->getName()} ({$artisan->getCommisionsQuotesCheckUrl()}): {$exception->getMessage()}");
            $status = null;
        }

        $this->reportStatusChange($artisan, $status);
        $artisan->setAreCommissionsOpen($status);
    }

    private function canAutoUpdate(Artisan $artisan): bool
    {
        return !empty($artisan->getCommisionsQuotesCheckUrl());
    }

    private function reportStatusChange(Artisan $artisan, ?bool $newStatus)
    {
        if ($artisan->getAreCommissionsOpen() !== true && $newStatus === true) {
            $this->style->caution($artisan->getName() . ' commissions are now OPEN');
        }

        if ($artisan->getAreCommissionsOpen() !== false && $newStatus === false) {
            $this->style->caution($artisan->getName() . ' commissions are now CLOSED');
        }

        if ($artisan->getAreCommissionsOpen() !== null && $newStatus === null) {
            $this->style->caution($artisan->getName() . ' commissions are now UNKNOWN');
        }
    }

    /**
     * @param array $artisans
     * @param bool $refresh
     * @throws UrlFetcherException
     */
    private function prefetchStatusWebpages(array $artisans, bool $refresh): void
    {
        if ($refresh) {
            $this->urlFetcher->clearCache();
        }

        $this->style->progressStart(count($artisans));

        foreach ($artisans as $artisan) {
            if ($this->canAutoUpdate($artisan)) {
                $this->urlFetcher->fetchWebPage($artisan->getCommisionsQuotesCheckUrl());
            }

            $this->style->progressAdvance();
        }

        $this->style->progressFinish();
    }

    /**
     * @return Artisan[]
     */
    private function getArtisans(): array
    {
        return $this->artisanRepository->findAll();
    }

    /**
     * @param string $url
     * @return string
     * @throws UrlFetcherException
     */
    private function fetchWebpageContents(string $url): string
    {
        $webpageContents = $this->urlFetcher->fetchWebPage($url);

        if (WebsiteInfo::isWixsite($url, $webpageContents)) {
            $webpageContents =  $this->fetchWixsiteContents($webpageContents);
        }

        return $webpageContents;
    }

    /**
     * @param string $webpageContents
     * @return string
     * @throws UrlFetcherException
     */
    private function fetchWixsiteContents(string $webpageContents): string
    {
        preg_match('#"masterPageJsonFileName"\s*:\s*"(?<hash>[a-z0-9_]+).json"#s', $webpageContents, $matches);

        $hash = $matches['hash'];

        preg_match("#<link[^>]* href=\"(?<data_url>https://static.wixstatic.com/sites/(?!$hash)[a-z0-9_]+\.json\.z\?v=\d+)\"[^>]*>#si",
            $webpageContents, $matches);

        return $this->urlFetcher->fetchWebPage($matches['data_url']);
    }
}