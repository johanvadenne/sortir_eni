<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Service\InscriptionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/sorties')]
class InscriptionController extends AbstractController
{
    public function __construct(
        private InscriptionService $inscriptionService
    ) {
    }

    #[Route('/{id}/inscription', name: 'sortie_inscription', methods: ['POST'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_USER')]
    public function inscription(Sortie $sortie): Response
    {
        $participant = $this->getUser();

        try {
            $this->inscriptionService->inscrire($participant, $sortie);
            $this->addFlash('success', 'Inscription réussie !');
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('sortie_show', ['id' => $sortie->getId()]);
    }

    #[Route('/{id}/desistement', name: 'sortie_desistement', methods: ['POST'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_USER')]
    public function desistement(Sortie $sortie): Response
    {
        $participant = $this->getUser();

        try {
            $this->inscriptionService->desister($participant, $sortie);
            $this->addFlash('success', 'Désistement réussi !');
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('sortie_show', ['id' => $sortie->getId()]);
    }
}
