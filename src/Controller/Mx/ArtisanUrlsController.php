<?php

declare(strict_types=1);

namespace App\Controller\Mx;

use App\DataDefinitions\Fields\Fields;
use App\Repository\ArtisanUrlRepository;
use App\ValueObject\Routing\RouteName;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/mx/artisan_urls')]
class ArtisanUrlsController extends FuzzrakeAbstractController
{
    #[Route(path: '/', name: RouteName::MX_ARTISAN_URLS)]
    #[Cache(maxage: 0, public: false)]
    public function index(ArtisanUrlRepository $repository): Response
    {
        $this->authorize();

        $urls = $repository->getOrderedBySuccessDate(Fields::nonInspectedUrls());

        return $this->render('mx/artisan_urls/index.html.twig', [
            'urls' => $urls,
        ]);
    }
}
