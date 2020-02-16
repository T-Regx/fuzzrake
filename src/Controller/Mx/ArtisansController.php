<?php

declare(strict_types=1);

namespace App\Controller\Mx;

use App\Entity\Artisan;
use App\Form\ArtisanType;
use App\Service\HostsService;
use App\Utils\Artisan\Utils;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/mx/artisans")
 */
class ArtisansController extends AbstractController
{
    /**
     * @Route("/{id}/edit", name="mx_artisan_edit", methods={"GET", "POST"})
     * @Route("/new", name="mx_artisan_new", methods={"GET", "POST"})
     * @Cache(maxage=0, public=false)
     */
    public function edit(Request $request, ?Artisan $artisan, HostsService $hostsSrv): Response
    {
        if (!$hostsSrv->isDevMachine()) {
            throw $this->createAccessDeniedException();
        }

        $artisan ??= new Artisan();

        $form = $this->createForm(ArtisanType::class, $artisan);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (null !== $artisan->getId() && $form->get(ArtisanType::BTN_DELETE)->isClicked()) {
                $this->getDoctrine()->getManager()->remove($artisan);
            } else {
                Utils::updateContact($artisan, $artisan->getContactInfoOriginal());

                $this->getDoctrine()->getManager()->persist($artisan);
            }

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('main');
        }

        return $this->render('mx/artisans/edit.html.twig', [
            'artisan' => $artisan,
            'form'    => $form->createView(),
        ]);
    }
}
