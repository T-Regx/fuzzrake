<?php

declare(strict_types=1);

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\GoneHttpException;
use Symfony\Component\Routing\Annotation\Route;

class PagesController extends AbstractController
{
    #[Route(path: '/data_updates.html', name: 'data_updates')]
    #[Cache(maxage: 21600, public: true)]
    public function dataUpdates(): Response
    {
        return $this->render('pages/data_updates.html.twig', []);
    }

    #[Route(path: '/info.html', name: 'info')]
    #[Cache(maxage: 21600, public: true)]
    public function info(): Response
    {
        return $this->render('pages/info.html.twig', []);
    }

    #[Route(path: '/tracking.html', name: 'tracking')]
    #[Cache(maxage: 21600, public: true)]
    public function tracking(): Response
    {
        return $this->render('pages/tracking.html.twig', []);
    }

    #[Route(path: '/whoopsies.html', name: 'whoopsies')]
    #[Cache(maxage: 21600, public: true)]
    public function whoopsies(): Response
    {
        throw new GoneHttpException();
    }

    #[Route(path: '/maker_ids.html', name: 'maker_ids')]
    #[Cache(maxage: 21600, public: true)]
    public function makerIds(): Response
    {
        return $this->render('pages/maker_ids.html.twig', []);
    }

    #[Route(path: '/donate.html', name: 'donate')]
    #[Cache(maxage: 21600, public: true)]
    public function donate(): Response
    {
        return $this->render('pages/donate.html.twig', []);
    }
}
