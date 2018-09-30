<?php

declare(strict_types=1);

namespace App\Controller\Frontend;

use App\Repository\ArtisanRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/")
 */
class DefaultController extends AbstractController
{
    /**
     * @Route("/info.html", name="info")
     *
     * @return Response
     */
    public function info(): Response
    {
        return $this->render('frontend/info.html.twig', []);
    }

    /**
     * @Route("/idea.html", name="idea")
     *
     * @return Response
     */
    public function idea(): Response
    {
        return $this->render('frontend/idea.html.twig', []);
    }

    /**
     * @Route("/", name="main")
     * @Route("/index.html")
     *
     * @return Response
     */
    public function main(ArtisanRepository $artisanRepository): Response
    {
        $artisans = $artisanRepository->getAll();
        $countryCount = $artisanRepository->getDistinctCountriesCount();
        $types = $artisanRepository->getDistinctTypes();
        $styles = $artisanRepository->getDistinctStyles();
        $features = $artisanRepository->getDistinctFeatures();
        $countries = $artisanRepository->getDistinctCountries();

        return $this->render('frontend/main/main.html.twig', [
            'artisans' => $artisans,
            'countryCount' => $countryCount,
            'types' => $types,
            'styles' => $styles,
            'features' => $features,
            'countries' => $countries,
        ]);
    }
}
