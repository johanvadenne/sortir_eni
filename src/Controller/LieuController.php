<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Form\LieuType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/lieux')]
#[IsGranted('ROLE_ADMIN')]
class LieuController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/', name: 'lieu_index')]
    public function index(): Response
    {
        $lieux = $this->entityManager->getRepository(Lieu::class)->findAll();

        return $this->render('lieu/index.html.twig', [
            'lieux' => $lieux
        ]);
    }

    #[Route('/nouveau', name: 'lieu_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $lieu = new Lieu();
        $form = $this->createForm(LieuType::class, $lieu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($lieu);
            $this->entityManager->flush();

            $this->addFlash('success', 'Lieu créé avec succès.');

            return $this->redirectToRoute('lieu_index');
        }

        return $this->render('lieu/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/{id}', name: 'lieu_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Lieu $lieu): Response
    {
        return $this->render('lieu/show.html.twig', [
            'lieu' => $lieu
        ]);
    }

    #[Route('/{id}/editer', name: 'lieu_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(Request $request, Lieu $lieu): Response
    {
        $form = $this->createForm(LieuType::class, $lieu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', 'Lieu modifié avec succès.');

            return $this->redirectToRoute('lieu_index');
        }

        return $this->render('lieu/edit.html.twig', [
            'form' => $form->createView(),
            'lieu' => $lieu
        ]);
    }

    #[Route('/{id}/supprimer', name: 'lieu_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Lieu $lieu): Response
    {
        $this->entityManager->remove($lieu);
        $this->entityManager->flush();

        $this->addFlash('success', 'Lieu supprimé avec succès.');

        return $this->redirectToRoute('lieu_index');
    }
}
