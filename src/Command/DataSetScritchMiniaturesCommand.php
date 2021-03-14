<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\ArtisanRepository;
use App\Utils\Json;
use App\Utils\StrUtils;
use App\Utils\UnbelievableRuntimeException;
use App\Utils\Web\HttpClient\GentleHttpClient;
use Doctrine\ORM\EntityManagerInterface;
use JsonException;
use LogicException;
use Symfony\Component\BrowserKit\CookieJar;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use TRegx\CleanRegex\Exception\NonexistentGroupException;
use TRegx\CleanRegex\Match\Details\Detail;

class DataSetScritchMiniaturesCommand extends Command
{
    private const PICTURE_URL_REGEXP = '^https://scritch\.es/pictures/(?<picture_id>[a-z0-9-]{36})$';

    protected static $defaultName = 'app:data:set-scritch-miniatures';
    private CookieJar $cookieJar;
    private GentleHttpClient $httpClient;

    public function __construct(
        private ArtisanRepository $artisanRepository,
        private EntityManagerInterface $entityManager,
    ) {
        parent::__construct();

        $this->cookieJar = new CookieJar();
        $this->httpClient = new GentleHttpClient();
    }

    protected function configure()
    {
        $this->addOption('commit', null, null, 'Save changes in the database');
    }

    /**
     * @throws ExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $response = $this->httpClient->get('https://scritch.es/', $this->cookieJar);

        $this->updateCookies($response);
        $csrfToken = $this->cookieJar->get('csrf-token')->getValue();

        foreach ($this->artisanRepository->getAll() as $artisan) {
            $pictureUrls = array_filter(explode("\n", $artisan->getPhotoUrls()));

            if (empty($pictureUrls)) {
                $artisan->setMiniatureUrls('');
                continue;
            }

            if (count($pictureUrls) === count(array_filter(explode("\n", $artisan->getMiniatureUrls())))) {
                continue;
            }

            try {
                $miniatureUrls = $this->retrieveMiniatureUrls($pictureUrls, $csrfToken);
            } catch (ExceptionInterface | JsonException | LogicException $e) {
                $io->error('Failed: '.$artisan->getLastMakerId().', '.$e->getMessage());
                continue;
            }

            $artisan->setMiniatureUrls(implode("\n", $miniatureUrls));
            $io->writeln('Retrieved miniatures for '.StrUtils::artisanNamesSafeForCli($artisan));
        }

        if ($input->getOption('commit')) {
            $this->entityManager->flush();
            $io->success('Finished and saved');
        } else {
            $io->success('Finished without saving');
        }

        return 0;
    }

    /**
     * @param string[] $pictureUrls
     *
     * @return string[]
     *
     * @throws JsonException
     * @throws LogicException
     * @throws ExceptionInterface
     */
    private function retrieveMiniatureUrls(array $pictureUrls, string $csrfToken): array
    {
        $pictureIds = $this->idsFromPictureUrls($pictureUrls);
        $jsonPayloads = array_map([$this, 'getGraphQlJsonPayload'], $pictureIds);
        $result = [];

        foreach ($jsonPayloads as $jsonPayload) {
            $response = $this->httpClient->post('https://scritch.es/graphql', $jsonPayload, $this->cookieJar, [
                'Content-Type'  => 'application/json',
                'X-CSRF-Token'  => $csrfToken,
                'authorization' => "Scritcher $csrfToken",
            ]);

            $this->updateCookies($response);

            $thumbnailUrl = Json::decode($response->getContent(true))['data']['medium']['thumbnail'] ?? '';

            if ('' === $thumbnailUrl) {
                throw new LogicException('No thumbnail URL found in response');
            }

            $result[] = $thumbnailUrl;
        }

        return $result;
    }

    /**
     * @param string[] $pictureUrls
     *
     * @return string[]
     */
    private function idsFromPictureUrls(array $pictureUrls): array
    {
        $result = [];

        foreach ($pictureUrls as $pictureUrl) {
            $result[] = pattern(self::PICTURE_URL_REGEXP)->match($pictureUrl)
                ->findFirst(function (Detail $detail): string {
                    try {
                        return $detail->get('picture_id');
                    } catch (NonexistentGroupException $e) {
                        throw new UnbelievableRuntimeException($e);
                    }
                })->orElse(throw new LogicException("Failed to match Scritch picture URL: $pictureUrl"));
        }

        return $result;
    }

    private function getGraphQlJsonPayload(string $pictureId): string
    {
        return '{"operationName": "Medium", "variables": {"id": "'.$pictureId.'"}, "query": "query Medium($id: ID!, $tagging: Boolean) { medium(id: $id, tagging: $tagging) { thumbnail } }"}';
    }

    /**
     * @throws ExceptionInterface
     */
    private function updateCookies(ResponseInterface $response): void
    {
        $this->cookieJar->updateFromSetCookie($response->getHeaders(true)['set-cookie'] ?? []);
    }
}
