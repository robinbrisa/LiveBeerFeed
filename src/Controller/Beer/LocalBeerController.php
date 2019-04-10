<?php

namespace App\Controller\Beer;

use App\Entity\Beer\LocalBeer;
use App\Form\Beer\LocalBeerType;
use App\Repository\Beer\LocalBeerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/beer/local/beer")
 */
class LocalBeerController extends AbstractController
{
    /**
     * @Route("/", name="beer_local_beer_index", methods={"GET"})
     */
    public function index(LocalBeerRepository $localBeerRepository): Response
    {
        return $this->render('beer/local_beer/index.html.twig', [
            'local_beers' => $localBeerRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="beer_local_beer_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $localBeer = new LocalBeer();
        $form = $this->createForm(LocalBeerType::class, $localBeer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($localBeer);
            $entityManager->flush();

            return $this->redirectToRoute('beer_local_beer_index');
        }

        return $this->render('beer/local_beer/new.html.twig', [
            'local_beer' => $localBeer,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="beer_local_beer_show", methods={"GET"})
     */
    public function show(LocalBeer $localBeer): Response
    {
        return $this->render('beer/local_beer/show.html.twig', [
            'local_beer' => $localBeer,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="beer_local_beer_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, LocalBeer $localBeer): Response
    {
        $form = $this->createForm(LocalBeerType::class, $localBeer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('beer_local_beer_index', [
                'id' => $localBeer->getId(),
            ]);
        }

        return $this->render('beer/local_beer/edit.html.twig', [
            'local_beer' => $localBeer,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="beer_local_beer_delete", methods={"DELETE"})
     */
    public function delete(Request $request, LocalBeer $localBeer): Response
    {
        if ($this->isCsrfTokenValid('delete'.$localBeer->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($localBeer);
            $entityManager->flush();
        }

        return $this->redirectToRoute('beer_local_beer_index');
    }
}
