<?php

namespace App\Controller;

use App\Entity\Ville;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/test-map')]
class TestMapController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/', name: 'test_map')]
    public function index(): Response
    {
        // Récupérer les villes pour le test
        $villes = $this->entityManager->getRepository(Ville::class)->findAll();

        return $this->render('test_map/index.html.twig', [
            'villes' => $villes
        ]);
    }
}
