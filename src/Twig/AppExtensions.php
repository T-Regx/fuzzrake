<?php

declare(strict_types=1);

namespace App\Twig;

use App\Repository\ArtisanCommissionsStatusRepository;
use App\Utils\DateTimeException;
use App\Utils\DateTimeUtils;
use App\Utils\FilterItem;
use App\Utils\Regexp\Utils as Regexp;
use App\Utils\Tracking\Status;
use App\Utils\StrUtils;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\HttpFoundation\Exception\SuspiciousOperationException;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppExtensions extends AbstractExtension
{
    const MONTHS = [1 => 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

    /**
     * @var ArtisanCommissionsStatusRepository
     */
    private $acsRepository;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var array
     */
    private $hosts;

    public function __construct(ArtisanCommissionsStatusRepository $acsRepository, RequestStack $requestStack, array $hosts)
    {
        $this->acsRepository = $acsRepository;
        $this->requestStack = $requestStack;
        $this->hosts = $hosts;
    }

    public function getFilters()
    {
        return [
            new TwigFilter('since', [$this, 'sinceFilter']),
            new TwigFilter('other', [$this, 'otherFilter']),
            new TwigFilter('event_url', [StrUtils::class, 'shortPrintUrl']),
            new TwigFilter('status_text', [Status::class, 'text']),
            new TwigFilter('filterItemsMatching', [$this, 'filterItemsMatchingFilter']),
            new TwigFilter('humanFriendlyRegexp', [$this, 'filterHumanFriendlyRegexp']),
        ];
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('getLastSystemUpdateTimeUtcStr', [$this, 'getLastSystemUpdateTimeUtcStrFunction']),
            new TwigFunction('getLastDataUpdateTimeUtcStr', [$this, 'getLastDataUpdateTimeUtcStrFunction']),
            new TwigFunction('isDevMachine', [$this, 'isDevMachineFunction']),
            new TwigFunction('isProduction', [$this, 'isProductionFunction']),
        ];
    }

    public function isDevMachineFunction(): bool
    {
        return $this->getHostname() === $this->hosts['dev_machine'];
    }

    public function isProductionFunction(): bool
    {
        return $this->getHostname() === $this->hosts['production'];
    }

    public function getLastDataUpdateTimeUtcStrFunction(): string
    {
        try {
            return $this->acsRepository->getLastCstUpdateTime()->format('Y-m-d H:i');
        } catch (DateTimeException | NonUniqueResultException $e) {
            return 'unknown/error';
        }
    }

    public function getLastSystemUpdateTimeUtcStrFunction(): string
    {
        try {
            return DateTimeUtils::getUtcAt(`TZ=UTC git log -n1 --format=%cd --date=local`)->format('Y-m-d H:i');
        } catch (DateTimeException $e) {
            return 'unknown/error';
        }
    }

    public function otherFilter($primaryList, $otherList)
    {
        $primaryList = str_replace("\n", ', ', $primaryList);

        if ('' !== $otherList) {
            if ('' !== $primaryList) {
                return "$primaryList, Other";
            } else {
                return 'Other';
            }
        } else {
            return $primaryList;
        }
    }

    /**
     * @param string $input
     *
     * @return string
     *
     * @throws TplDataException
     */
    public function sinceFilter(string $input): string
    {
        if ('' === $input) {
            return '';
        }

        if (!Regexp::match('#^(?<year>\d{4})-(?<month>\d{2})$#', $input, $matches)) {
            throw new TplDataException("Invalid 'since' data: '$input''");
        }

        return self::MONTHS[(int) $matches['month']].' '.$matches['year'];
    }

    public function filterItemsMatchingFilter(array $items, string $matchWord): array
    {
        return array_filter($items, function (FilterItem $item) use ($matchWord) {
            return Regexp::match("#$matchWord#i", $item->getLabel());
        });
    }

    public function filterHumanFriendlyRegexp(string $input): string
    {
        $input = Regexp::replace('#\(\?<!.+?\)#', '', $input);
        $input = Regexp::replace('#\(\?!.+?\)#', '', $input);
        $input = Regexp::replace('#\([^a-z]+?\)#i', '', $input);
        $input = Regexp::replace('#[()?]#', '', $input);
        $input = Regexp::replace('#\[.+?\]#', '', $input);

        return strtoupper($input);
    }

    private function getHostname(): string
    {
        try {
            return $this->requestStack->getCurrentRequest()->getHost();
        } catch (SuspiciousOperationException $e) {
            return 'unknown/error';
        }
    }
}
